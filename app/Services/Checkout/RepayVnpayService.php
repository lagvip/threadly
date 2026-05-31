<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class RepayVnpayService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected ProductVariantRepositoryInterface $variants,
        protected CheckoutVoucherService $vouchers,
        protected VnpayPaymentService $vnpay,
    ) {
    }

    public function execute(User $user, int $orderId): string
    {
        $order = $this->orders->findForUserWithDetails($orderId, (int) $user->id);

        if ($order->payment_method !== 'vnpay') {
            throw new RuntimeException('Đơn này không phải thanh toán VNPay.');
        }

        if (!$order->can_repay) {
            throw new RuntimeException('Đơn này không thuộc trạng thái cho phép thanh toán lại.');
        }

        if ($order->details->isEmpty()) {
            throw new RuntimeException('Đơn hàng không có sản phẩm để thanh toán lại.');
        }

        try {
            DB::transaction(function () use ($order) {
                foreach ($order->details as $detail) {
                    if (!$detail->variant_id) {
                        throw new RuntimeException('Có sản phẩm trong đơn không còn biến thể hợp lệ.');
                    }

                    $variant = $this->variants->lockById((int) $detail->variant_id);

                    if (!$variant) {
                        throw new RuntimeException('Có sản phẩm trong đơn không còn tồn tại.');
                    }

                    if ((int) $variant->quantity < (int) $detail->quantity) {
                        throw new RuntimeException('Sản phẩm "' . ($detail->product_name ?? 'N/A') . '" không đủ tồn kho để thanh toán lại.');
                    }
                }

                $this->vouchers->reserveVoucherForRepay($order);

                $order->update([
                    'previous_status' => $order->order_status,
                    'order_status' => 'pending',
                    'payment_status' => 'pending',
                    'cancel_reason' => null,
                ]);
            });

            return $this->vnpay->createPaymentUrl($order);
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Repay VNPay error: ' . $e->getMessage());
            throw new RuntimeException('Có lỗi xảy ra khi tạo thanh toán lại.');
        }
    }
}
