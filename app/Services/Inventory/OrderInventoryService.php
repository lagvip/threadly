<?php

namespace App\Services\Inventory;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Contracts\Repositories\VoucherRepositoryInterface;
use App\Enums\OrderPaymentStatus;
use App\Enums\PaymentMethod;
use App\Enums\StockMovementType;
use App\Events\Inventory\StockMovementRecorded;
use App\Models\Order;

class OrderInventoryService
{
    public function __construct(
        protected ProductVariantRepositoryInterface $variants,
        protected OrderRepositoryInterface $orders,
        protected VoucherRepositoryInterface $vouchers,
    ) {}

    public function releaseCancelledOrder(Order $order): void
    {
        // Load chi tiết đơn để biết cần hoàn kho cho biến thể nào, số lượng bao nhiêu.
        $order->loadMissing('details');

        // Hoàn lại tồn kho nếu đơn này đã từng bị trừ kho.
        $this->releaseStockIfDeducted($order);

        // Hoàn lại lượt voucher nếu đơn có dùng voucher.
        $this->releaseVoucherIfReserved($order);
    }

    protected function releaseStockIfDeducted(Order $order): void
    {
        // Nếu đã hoàn kho rồi thì dừng, tránh cộng kho nhiều lần.
        if ($order->stock_released_at) {
            return;
        }

        // Nếu đơn chưa từng trừ kho thì không được cộng lại kho.
        if (! $this->wasStockDeducted($order)) {
            return;
        }

        // Duyệt từng sản phẩm trong đơn để cộng lại đúng biến thể đã mua.
        foreach ($order->details as $detail) {
            // Bỏ qua nếu không có variant_id hoặc số lượng không hợp lệ.
            if (! $detail->variant_id || (int) $detail->quantity <= 0) {
                continue;
            }

            // Tìm biến thể và khóa dòng khi cập nhật để tránh lệch tồn kho nếu có nhiều request cùng lúc.
            $variant = $this->variants->lockById((int) $detail->variant_id);

            // Nếu biến thể không còn tồn tại thì bỏ qua.
            if (! $variant) {
                continue;
            }

            // Cộng lại số lượng đã mua vào tồn kho của biến thể.
            $stockBefore = (int) $variant->quantity;
            $quantity = (int) $detail->quantity;
            $stockAfter = $stockBefore + $quantity;

            $this->variants->update($variant, ['quantity' => $stockAfter]);

            StockMovementRecorded::dispatch(
                (int) $variant->id,
                StockMovementType::CancelRelease->value,
                $quantity,
                $stockBefore,
                $stockAfter,
                Order::class,
                $order->id,
                null,
                'Hoàn tồn do hủy đơn '.($order->order_code ?? ('#'.$order->id))
            );
        }

        // Đánh dấu đơn đã hoàn kho để lần sau không cộng lại nữa.
        $this->orders->update($order, [
            'stock_released_at' => now(),
        ]);
    }

    protected function releaseVoucherIfReserved(Order $order): void
    {
        // Nếu đơn không dùng voucher hoặc đã hoàn voucher rồi thì dừng.
        if (! $order->voucher_id || $order->voucher_released_at) {
            return;
        }

        // Tìm voucher và khóa dòng để tránh cộng lượt dùng bị trùng.
        $voucher = $this->vouchers->lockById((int) $order->voucher_id);

        // Nếu voucher còn tồn tại thì cộng lại 1 lượt dùng.
        if ($voucher) {
            $voucher->increment('quantity');
        }

        // Đánh dấu đã hoàn voucher để tránh hoàn nhiều lần.
        $this->orders->update($order, [
            'voucher_released_at' => now(),
        ]);
    }

    protected function wasStockDeducted(Order $order): bool
    {
        // COD đã trừ kho ngay khi tạo đơn nên nếu hủy thì cần hoàn kho.
        if ($order->payment_method === PaymentMethod::Cod->value) {
            return true;
        }

        // VNPay chỉ trừ kho sau khi thanh toán thành công.
        return $order->payment_method === PaymentMethod::Vnpay->value
            && $order->payment_status === OrderPaymentStatus::Paid->value;
    }
}
