<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\PasswordResetTokenRepositoryInterface;
use Illuminate\Support\Facades\DB;

class PasswordResetTokenRepository implements PasswordResetTokenRepositoryInterface
{
    public function updateOrCreate(string $email, string $tokenHash): void
    {
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            [
                'token' => $tokenHash,
                'created_at' => now(),
            ]
        );
    }

    public function findByEmail(string $email): ?object
    {
        return DB::table('password_reset_tokens')->where('email', $email)->first();
    }

    public function deleteByEmail(string $email): int
    {
        return DB::table('password_reset_tokens')->where('email', $email)->delete();
    }
}
