<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Models\Order;
use App\Models\StockMovement;
use App\Services\Inventory\StockMovementService;

class CheckoutInventoryService
{
    public function __construct(
        protected ProductVariantRepositoryInterface $variants,
        protected StockMovementService $stockMovements
    ) {
    }

    public function decreaseStockFromOrder(Order $order): void
    {
        $order->load('details');

        foreach ($order->details as $detail) {
            $variant = $this->variants->lockById($detail->variant_id);

            if (!$variant) {
                throw new \Exception('Không tìm thấy biến thể sản phẩm.');
            }

            if ($variant->quantity < $detail->quantity) {
                throw new \Exception('Tồn kho không đủ để xử lý đơn hàng.');
            }

            $stockBefore = (int) $variant->quantity;
            $quantity = (int) $detail->quantity;
            $stockAfter = $stockBefore - $quantity;

            $variant->update(['quantity' => $stockAfter]);

            $this->stockMovements->record(
                $variant,
                StockMovement::TYPE_SALE,
                -$quantity,
                $stockBefore,
                $stockAfter,
                Order::class,
                $order->id,
                $order->user_id ? (int) $order->user_id : null,
                'Xuất kho từ đơn ' . ($order->order_code ?? ('#' . $order->id))
            );
        }
    }
}
