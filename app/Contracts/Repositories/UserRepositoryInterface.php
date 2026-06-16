<?php

namespace App\Contracts\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

interface UserRepositoryInterface
{
    public function adminIndexQuery(): Builder;

    public function findWithRoles(int $id): User;

    public function findTrashedWithRoles(int $id): User;

    public function trashedForAdmin(): Builder;

    public function countAdmins(): int;

    public function adminExistsExcept(int $userId): bool;

    public function create(array $data): User;

    public function update(User $user, array $data): bool;

    public function delete(User $user): bool;

    public function restore(User $user): bool;

    public function forceDelete(User $user): bool;

    public function syncRoles(User $user, array $roleIds): array;

    public function syncRolesWithoutDetaching(User $user, array $roleIds): array;

    public function attachRole(User $user, int $roleId): void;

    public function detachRoles(User $user): int;

    public function hasOrders(User $user): bool;

    public function findByGoogleIdOrEmail(string $googleId, string $email): ?User;

    public function updatePasswordByEmail(string $email, string $passwordHash): void;
}
