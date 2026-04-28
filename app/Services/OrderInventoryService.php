<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Voucher;

class OrderInventoryService
{
    /**
     * Hoàn lại tồn kho/voucher cho đơn bị hủy trước khi giao thành công.
     *
     * Quy ước hiện tại của project:
     * - COD: đã trừ kho ngay khi tạo đơn.
     * - VNPay: chỉ trừ kho sau khi thanh toán thành công.
     * - Voucher: đã trừ lượt dùng khi tạo đơn.
     *
     * Hàm này KHÔNG dùng cho đơn đã giao rồi hoàn tiền.
     * Đơn đã giao muốn cộng kho phải dùng nút "Xác nhận nhập lại kho" ở yêu cầu hoàn tiền.
     */
    public function releaseCancelledOrder(Order $order): void
    {
        $order->loadMissing('details');

        $this->releaseStockIfDeducted($order);
        $this->releaseVoucherIfReserved($order);
    }

    protected function releaseStockIfDeducted(Order $order): void
    {
        if ($order->stock_released_at) {
            return;
        }

        if (!$this->wasStockDeducted($order)) {
            return;
        }

        foreach ($order->details as $detail) {
            if (!$detail->variant_id || (int) $detail->quantity <= 0) {
                continue;
            }

            $variant = ProductVariant::whereKey($detail->variant_id)
                ->lockForUpdate()
                ->first();

            if (!$variant) {
                continue;
            }

            $variant->increment('quantity', (int) $detail->quantity);
        }

        $order->forceFill([
            'stock_released_at' => now(),
        ])->save();
    }

    protected function releaseVoucherIfReserved(Order $order): void
    {
        if (!$order->voucher_id || $order->voucher_released_at) {
            return;
        }

        $voucher = Voucher::whereKey($order->voucher_id)
            ->lockForUpdate()
            ->first();

        if ($voucher) {
            $voucher->increment('quantity');
        }

        $order->forceFill([
            'voucher_released_at' => now(),
        ])->save();
    }

    protected function wasStockDeducted(Order $order): bool
    {
        if ($order->payment_method === Order::PAYMENT_METHOD_COD) {
            return true;
        }

        return $order->payment_method === Order::PAYMENT_METHOD_VNPAY
            && $order->payment_status === Order::PAYMENT_PAID;
    }
}
