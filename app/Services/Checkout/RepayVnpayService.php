<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RepayVnpayService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected CheckoutVoucherService $vouchers,
        protected VnpayPaymentService $vnpay,
        protected CheckoutInventoryService $inventory,
    ) {}

    public function execute(User $user, int $orderId, ?string $clientIp = null): string
    {
        $order = $this->orders->findForUserWithDetails($orderId, (int) $user->id);

        if ($order->payment_method !== PaymentMethod::Vnpay->value) {
            throw new RuntimeException('Đơn này không phải thanh toán VNPay.');
        }

        if (! $order->can_repay) {
            throw new RuntimeException('Đơn này không thuộc trạng thái cho phép thanh toán lại.');
        }

        if ($order->details->isEmpty()) {
            throw new RuntimeException('Đơn hàng không có sản phẩm để thanh toán lại.');
        }

        try {
            return DB::transaction(function () use ($order, $clientIp) {
                $lockedOrder = $this->orders->lockById((int) $order->id);

                if ((int) $lockedOrder->user_id !== (int) $order->user_id || ! $lockedOrder->can_repay) {
                    throw new RuntimeException('Đơn này không còn ở trạng thái cho phép thanh toán lại.');
                }

                $lockedOrder->loadMissing('details');

                if ($lockedOrder->details->isEmpty()) {
                    throw new RuntimeException('Đơn hàng không có sản phẩm để thanh toán lại.');
                }

                $this->vouchers->reserveVoucherForRepay($lockedOrder);
                $this->inventory->decreaseStockFromOrder($lockedOrder);

                $this->orders->update($lockedOrder, [
                    'previous_status' => $lockedOrder->order_status,
                    'order_status' => OrderStatus::Pending->value,
                    'payment_status' => OrderPaymentStatus::Pending->value,
                    'cancel_reason' => null,
                    'voucher_released_at' => null,
                ]);

                return $this->vnpay->createPaymentUrl($lockedOrder, $clientIp);
            });
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Repay VNPay error: '.$e->getMessage());
            throw new RuntimeException('Có lỗi xảy ra khi tạo thanh toán lại.');
        }
    }
}
