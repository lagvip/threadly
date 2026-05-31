<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Models\Order;

class CheckoutInventoryService
{
    public function __construct(protected ProductVariantRepositoryInterface $variants)
    {
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

            $variant->decrement('quantity', $detail->quantity);
        }
    }
}
