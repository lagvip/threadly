<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\Sales\OrderStatusChanged;
use App\Models\Order;
use App\Services\Inventory\OrderInventoryService;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class ExpirePendingVnpayOrdersService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected OrderInventoryService $inventory,
    ) {}

    public function execute(int $batchSize = 100): int
    {
        $batchSize = max(1, $batchSize);
        $expiredCount = 0;

        do {
            $now = now();
            $ids = $this->orders->pendingVnpayExpirationCandidateIds(
                $now->format('YmdHis'),
                $now->copy()->subMinutes(15)->format('Y-m-d H:i:s'),
                $batchSize,
            );

            foreach ($ids as $id) {
                $expiredCount += DB::transaction(function () use ($id, $now): int {
                    $order = $this->orders->lockById($id);

                    if (! $this->canExpire($order, $now->format('YmdHis'), $now->copy()->subMinutes(15))) {
                        return 0;
                    }

                    $this->inventory->releaseCancelledOrder($order);

                    $this->orders->update($order, [
                        'previous_status' => $order->order_status,
                        'order_status' => OrderStatus::Cancelled->value,
                        'payment_status' => OrderPaymentStatus::Expired->value,
                        'cancel_reason' => 'Quá hạn thanh toán VNPay',
                    ]);

                    OrderStatusChanged::dispatch(
                        (int) $order->id,
                        OrderStatus::Cancelled->value,
                        'Tự động hủy do quá hạn thanh toán VNPay.',
                    );

                    return 1;
                });
            }
        } while (count($ids) === $batchSize);

        return $expiredCount;
    }

    protected function canExpire(Order $order, string $now, CarbonInterface $legacyCutoff): bool
    {
        if (
            $order->payment_method !== PaymentMethod::Vnpay->value
            || $order->payment_status !== OrderPaymentStatus::Pending->value
            || $order->order_status !== OrderStatus::Pending->value
        ) {
            return false;
        }

        $paymentExpireDate = trim((string) $order->payment_expire_date);

        if ($paymentExpireDate !== '') {
            return $paymentExpireDate <= $now;
        }

        return $order->created_at && $order->created_at->lessThanOrEqualTo($legacyCutoff);
    }
}
