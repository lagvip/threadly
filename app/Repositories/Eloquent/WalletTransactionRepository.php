<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\WalletTransactionRepositoryInterface;
use App\Enums\WalletTransactionType;
use App\Models\WalletTransaction;

class WalletTransactionRepository implements WalletTransactionRepositoryInterface
{
    public function refundCreditExists(int $refundRequestId): bool
    {
        return WalletTransaction::where('refund_request_id', $refundRequestId)
            ->where('type', WalletTransactionType::RefundCredit->value)
            ->exists();
    }

    public function create(array $data): WalletTransaction
    {
        return WalletTransaction::create($data);
    }

    public function paginatedForWallet(int $walletId, int $perPage = 15)
    {
        return WalletTransaction::with(['order', 'refundRequest'])
            ->where('wallet_id', $walletId)
            ->latest('id')
            ->paginate($perPage);
    }
}
