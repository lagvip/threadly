<?php

namespace App\Services\Admin\Users;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\UserStatus;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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
        $this->assertAdminRoleCanBeAssigned((int) $data['role_id']);

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ];

        if ($avatar) {
            $payload['avatar'] = $avatar->store('users', 'public');
        }

        $user = $this->users->create($payload);
        $this->users->syncRoles($user, [(int) $data['role_id']]);
    }

    public function update(int $id, array $data, ?UploadedFile $avatar = null): void
    {
        $user = $this->users->findWithRoles($id);

        $isCurrentAdmin = $user->hasRole('admin');
        $isNewAdminRole = $this->isAdminRoleId((int) $data['role_id']);
        $adminCount = $this->countAdminUsers();

        if (! $isCurrentAdmin && $isNewAdminRole && $adminCount >= 1) {
            throw new RuntimeException('Hệ thống chỉ cho phép tồn tại 1 tài khoản Admin duy nhất.');
        }

        if ($isCurrentAdmin && ! $isNewAdminRole && $adminCount <= 1) {
            throw new RuntimeException('Không thể đổi role của Admin duy nhất sang quyền khác.');
        }

        $payload = [
            'name' => $data['name'],
            'email' => $data['email'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = bcrypt($data['password']);
        }

        if ($avatar) {
            $this->deleteAvatar($user);
            $payload['avatar'] = $avatar->store('users', 'public');
        }

        $this->users->update($user, $payload);
        $this->users->syncRoles($user, [(int) $data['role_id']]);
    }

    public function assignRole(int $id, int $roleId): void
    {
        $user = $this->users->findWithRoles($id);
        $isCurrentAdmin = $user->hasRole('admin');
        $isNewAdminRole = $this->isAdminRoleId($roleId);
        $adminCount = $this->countAdminUsers();

        if (! $isCurrentAdmin && $isNewAdminRole && $adminCount >= 1) {
            throw new RuntimeException('Hệ thống chỉ cho phép tồn tại 1 tài khoản Admin duy nhất.');
        }

        if ($isCurrentAdmin && ! $isNewAdminRole && $adminCount <= 1) {
            throw new RuntimeException('Không thể đổi role của Admin duy nhất sang quyền khác.');
        }

        $this->users->syncRoles($user, [$roleId]);
    }

    public function softDelete(int $id): void
    {
        $user = $this->users->findWithRoles($id);

        if ($this->users->hasOrders($user)) {
            throw new RuntimeException('User này vẫn còn đơn hàng, không thể xóa.');
        }

        $this->users->delete($user);
    }

    public function restore(int $id): void
    {
        $this->users->restore($this->users->findTrashedWithRoles($id));
    }

    public function forceDelete(int $id): void
    {
        $user = $this->users->findTrashedWithRoles($id);

        if ($this->users->hasOrders($user)) {
            throw new RuntimeException('User này vẫn còn đơn hàng, không thể xóa vĩnh viễn.');
        }

        $this->deleteAvatar($user);
        $this->users->detachRoles($user);
        $this->users->forceDelete($user);
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

    protected function assertAdminRoleCanBeAssigned(int $roleId): void
    {
        if ($this->isAdminRoleId($roleId) && $this->countAdminUsers() >= 1) {
            throw new RuntimeException('Hệ thống chỉ cho phép tồn tại 1 tài khoản Admin duy nhất.');
        }
    }

    protected function isAdminRoleId(int $roleId): bool
    {
        $adminRole = $this->roles->findBySlug('admin');

        return $adminRole && (int) $adminRole->id === $roleId;
    }

    protected function countAdminUsers(): int
    {
        return $this->users->countAdmins();
    }

    protected function deleteAvatar(User $user): void
    {
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }
    }
}
