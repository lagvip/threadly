<?php

namespace App\Contracts\Repositories;

interface PasswordResetTokenRepositoryInterface
{
    public function updateOrCreate(string $email, string $tokenHash): void;

    public function findByEmail(string $email): ?object;

    public function deleteByEmail(string $email): int;
}
