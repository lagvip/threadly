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
