<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\WalletRepositoryInterface;
use App\Models\Wallet;

class WalletRepository implements WalletRepositoryInterface
{
    public function firstOrCreateForUser(int $userId): Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => $userId],
            ['balance' => 0]
        );
    }

    public function lockById(int $id): Wallet
    {
        return Wallet::whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function update(Wallet $wallet, array $data): bool
    {
        return $wallet->update($data);
    }
}
