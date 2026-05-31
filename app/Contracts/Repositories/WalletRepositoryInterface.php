<?php

namespace App\Contracts\Repositories;

use App\Models\Wallet;

interface WalletRepositoryInterface
{
    public function firstOrCreateForUser(int $userId): Wallet;

    public function lockById(int $id): Wallet;
}
