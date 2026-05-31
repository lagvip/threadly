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

    public function findByGoogleIdOrEmail(string $googleId, string $email): ?User;

    public function updatePasswordByEmail(string $email, string $passwordHash): void;
}
