<?php

namespace App\Contracts\Repositories;

use App\Models\WalletTransaction;

interface WalletTransactionRepositoryInterface
{
    public function refundCreditExists(int $refundRequestId): bool;

    public function create(array $data): WalletTransaction;

    public function paginatedForWallet(int $walletId, int $perPage = 15);
}
