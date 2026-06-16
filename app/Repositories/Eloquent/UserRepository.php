<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class UserRepository implements UserRepositoryInterface
{
    public function adminIndexQuery(): Builder
    {
        return User::with('roles')->withCount(['allOrders as orders_count']);
    }

    public function findWithRoles(int $id): User
    {
        return User::with('roles')->findOrFail($id);
    }

    public function findTrashedWithRoles(int $id): User
    {
        return User::onlyTrashed()->with('roles')->findOrFail($id);
    }

    public function trashedForAdmin(): Builder
    {
        return User::onlyTrashed()
            ->with('roles')
            ->withCount(['allOrders as orders_count']);
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
