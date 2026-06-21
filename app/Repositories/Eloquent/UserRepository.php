<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class UserRepository implements UserRepositoryInterface
{
    protected function adminIndexQuery(): Builder
    {
        return User::with('roles')->withCount(['allOrders as orders_count']);
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        return $this->adminIndexQuery()
            ->when($filters['role'] ?? null, function ($q, $role) {
                $q->whereHas('roles', fn ($r) => $r->where('slug', $role));
            })
            ->when($filters['keyword'] ?? null, function ($q, $keyword) {
                $q->where(function ($qq) use ($keyword) {
                    $qq->where('name', 'LIKE', "%{$keyword}%")
                        ->orWhere('email', 'LIKE', "%{$keyword}%");
                });
            })
            ->orderByDesc('id')
            ->paginate($perPage);
    }

    public function findWithRoles(int $id): User
    {
        return User::with('roles')->findOrFail($id);
    }

    public function lockWithRoles(int $id): User
    {
        return User::with('roles')->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function findTrashedWithRoles(int $id): User
    {
        return User::onlyTrashed()->with('roles')->findOrFail($id);
    }

    public function lockTrashedWithRoles(int $id): User
    {
        return User::onlyTrashed()->with('roles')->whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function paginateTrashedForAdmin(int $perPage = 10): LengthAwarePaginator
    {
        return User::onlyTrashed()
            ->with('roles')
            ->withCount(['allOrders as orders_count'])
            ->orderByDesc('deleted_at')
            ->paginate($perPage);
    }

    public function countAdmins(): int
    {
        return User::whereHas('roles', fn ($query) => $query->where('slug', 'admin'))->count();
    }

    public function adminExistsExcept(int $userId): bool
    {
        return User::whereHas('roles', fn ($query) => $query->where('slug', 'admin'))
            ->where('id', '!=', $userId)
            ->exists();
    }

    public function create(array $data): User
    {
        return User::create($data);
    }

    public function update(User $user, array $data): bool
    {
        return $user->update($data);
    }

    public function delete(User $user): bool
    {
        return (bool) $user->delete();
    }

    public function restore(User $user): bool
    {
        return (bool) $user->restore();
    }

    public function forceDelete(User $user): bool
    {
        return (bool) $user->forceDelete();
    }

    public function syncRoles(User $user, array $roleIds): array
    {
        return $user->roles()->sync($roleIds);
    }

    public function syncRolesWithoutDetaching(User $user, array $roleIds): array
    {
        return $user->roles()->syncWithoutDetaching($roleIds);
    }

    public function attachRole(User $user, int $roleId): void
    {
        $user->roles()->attach($roleId);
    }

    public function detachRoles(User $user): int
    {
        return $user->roles()->detach();
    }

    public function hasOrders(User $user): bool
    {
        return $user->allOrders()->exists();
    }

    public function findByGoogleIdOrEmail(string $googleId, string $email): ?User
    {
        return User::where('google_id', $googleId)
            ->orWhere('email', $email)
            ->first();
    }

    public function updatePasswordByEmail(string $email, string $passwordHash): void
    {
        User::where('email', $email)->firstOrFail()->update([
            'password' => $passwordHash,
        ]);
    }
}
