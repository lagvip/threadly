<?php

namespace App\Services\Admin\Users;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\UserStatus;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class AdminUserService
{
    public function __construct(
        protected RoleRepositoryInterface $roles,
        protected UserRepositoryInterface $users,
    ) {}

    public function create(array $data, ?UploadedFile $avatar = null): void
    {
        $newAvatarPath = $this->storeAvatar($avatar);
        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ];

        if ($newAvatarPath) {
            $payload['avatar'] = $newAvatarPath;
        }

        try {
            DB::transaction(function () use ($data, $payload) {
                $adminRole = $this->roles->lockBySlug('admin');
                $this->assertRoleTransition(null, (int) $data['role_id'], $adminRole);

                $user = $this->users->create($payload);
                $this->users->syncRoles($user, [(int) $data['role_id']]);
            });
        } catch (\Throwable $e) {
            $this->deleteAvatarPath($newAvatarPath);

            throw $e;
        }
    }

    public function update(int $id, array $data, ?UploadedFile $avatar = null): void
    {
        $newAvatarPath = $this->storeAvatar($avatar);

        try {
            DB::transaction(function () use ($id, $data, $newAvatarPath) {
                $adminRole = $this->roles->lockBySlug('admin');
                $user = $this->users->lockWithRoles($id);
                $this->assertRoleTransition($user, (int) $data['role_id'], $adminRole);

                $payload = [
                    'name' => $data['name'],
                    'email' => $data['email'],
                ];

                if (! empty($data['password'])) {
                    $payload['password'] = bcrypt($data['password']);
                }

                if ($newAvatarPath) {
                    $payload['avatar'] = $newAvatarPath;
                }

                $oldAvatarPath = $user->avatar;

                if (! $this->users->update($user, $payload)) {
                    throw new RuntimeException('Cập nhật tài khoản không thành công.');
                }

                $this->users->syncRoles($user, [(int) $data['role_id']]);

                if ($newAvatarPath) {
                    DB::afterCommit(fn () => $this->deleteAvatarPath($oldAvatarPath));
                }
            });
        } catch (\Throwable $e) {
            $this->deleteAvatarPath($newAvatarPath);

            throw $e;
        }
    }

    public function assignRole(int $id, int $roleId): void
    {
        DB::transaction(function () use ($id, $roleId) {
            $adminRole = $this->roles->lockBySlug('admin');
            $user = $this->users->lockWithRoles($id);
            $this->assertRoleTransition($user, $roleId, $adminRole);
            $this->users->syncRoles($user, [$roleId]);
        });
    }

    public function softDelete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $this->roles->lockBySlug('admin');
            $user = $this->users->lockWithRoles($id);
            $this->assertAdminCanBeRemoved($user);

            if ($this->users->hasOrders($user)) {
                throw new RuntimeException('User này vẫn còn đơn hàng, không thể xóa.');
            }

            $this->users->delete($user);
        });
    }

    public function restore(int $id): void
    {
        DB::transaction(function () use ($id) {
            $adminRole = $this->roles->lockBySlug('admin');
            $user = $this->users->lockTrashedWithRoles($id);

            if ($user->hasRole('admin') && $adminRole && $this->users->countAdmins() >= 1) {
                throw new RuntimeException('Không thể khôi phục vì hệ thống đã có tài khoản Admin.');
            }

            $this->users->restore($user);
        });
    }

    public function forceDelete(int $id): void
    {
        DB::transaction(function () use ($id) {
            $this->roles->lockBySlug('admin');
            $user = $this->users->lockTrashedWithRoles($id);

            if ($this->users->hasOrders($user)) {
                throw new RuntimeException('User này vẫn còn đơn hàng, không thể xóa vĩnh viễn.');
            }

            $avatarPath = $user->avatar;
            $this->users->detachRoles($user);
            $this->users->forceDelete($user);
            DB::afterCommit(fn () => $this->deleteAvatarPath($avatarPath));
        });
    }

    public function ban(int $id, string $reasonOption, ?string $customReason, int $adminId): void
    {
        $user = $this->users->findWithRoles($id);

        if ($user->hasRole('admin')) {
            throw new RuntimeException('Không thể ban tài khoản Admin.');
        }

        $reason = $reasonOption;

        if ($reason === 'custom') {
            $reason = trim((string) $customReason);

            if ($reason === '') {
                throw new RuntimeException('Vui lòng nhập lý do ban.');
            }
        }

        $this->users->update($user, [
            'status' => UserStatus::Banned->value,
            'ban_reason' => $reason,
            'banned_at' => now(),
            'banned_by' => $adminId,
        ]);
    }

    public function unban(int $id): void
    {
        $this->users->update($this->users->findWithRoles($id), [
            'status' => UserStatus::Active->value,
            'ban_reason' => null,
            'banned_at' => null,
            'banned_by' => null,
        ]);
    }

    protected function assertRoleTransition(?User $user, int $newRoleId, ?Role $adminRole): void
    {
        $isCurrentAdmin = $user?->hasRole('admin') ?? false;
        $isNewAdmin = $adminRole && (int) $adminRole->id === $newRoleId;
        $adminCount = $this->users->countAdmins();

        if (! $isCurrentAdmin && $isNewAdmin && $adminCount >= 1) {
            throw new RuntimeException('Hệ thống chỉ cho phép tồn tại 1 tài khoản Admin duy nhất.');
        }

        if ($isCurrentAdmin && ! $isNewAdmin && $adminCount <= 1) {
            throw new RuntimeException('Không thể đổi role của Admin duy nhất sang quyền khác.');
        }
    }

    protected function assertAdminCanBeRemoved(User $user): void
    {
        if ($user->hasRole('admin') && $this->users->countAdmins() <= 1) {
            throw new RuntimeException('Không thể xóa tài khoản Admin duy nhất.');
        }
    }

    protected function storeAvatar(?UploadedFile $avatar): ?string
    {
        if (! $avatar) {
            return null;
        }

        $path = $avatar->store('users', 'public');

        if (! $path) {
            throw new RuntimeException('Không thể lưu ảnh đại diện.');
        }

        return $path;
    }

    protected function deleteAvatarPath(?string $path): void
    {
        if ($path && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
