<?php

namespace App\Services\Client\Refunds;

use App\Contracts\Repositories\WalletRepositoryInterface;
use App\Contracts\Repositories\WalletTransactionRepositoryInterface;
use App\Models\Order;
use RuntimeException;

class ClientRefundPageService
{
    public function __construct(
        protected ClientRefundRequestService $refunds,
        protected WalletRepositoryInterface $wallets,
        protected WalletTransactionRepositoryInterface $walletTransactions,
    ) {
    }

    public function createData(Order $order, int $userId): array
    {
        $order->load([
            'details.variant.color',
            'details.variant.size',
            'details.product',
            'refundRequests.evidences',
            'refundRequests.items',
        ]);

        $this->assertOrderOwner($order, $userId);

        if (!$order->can_request_refund) {
            throw new RuntimeException('Đơn hàng này chưa đủ điều kiện hoặc không còn số tiền để yêu cầu hoàn.');
        }

        return [
            'order' => $order,
            'refundableItems' => $this->refunds->buildRefundableItems($order),
        ];
    }

    public function walletData(int $userId): array
    {
        $wallet = $this->wallets->firstOrCreateForUser($userId);

        $wallet->load(['transactions.order', 'transactions.refundRequest']);

        return [
            'wallet' => $wallet,
            'transactions' => $this->walletTransactions->paginatedForWallet($wallet->id),
        ];
    }

    protected function assertOrderOwner(Order $order, int $userId): void
    {
        if ((int) $order->user_id !== $userId) {
            abort(403);
        }
    }
}
