<?php

namespace App\Actions\Checkout;

use App\DTOs\Checkout\BuyNowData;
use App\Models\ProductVariant;
use RuntimeException;

class BuyNowCheckoutAction
{
    public function execute(BuyNowData $data): void
    {
        $variant = ProductVariant::with(['product', 'color', 'size'])->findOrFail($data->variantId);

        if ($variant->status !== 'active') {
            throw new RuntimeException('Biến thể sản phẩm hiện không khả dụng.');
        }

        if ((int) $variant->quantity < $data->quantity) {
            throw new RuntimeException('Số lượng vượt quá tồn kho.');
        }

        session([
            config('threadly.checkout.buy_now_session_key') => [
                'variant_id' => $variant->id,
                'quantity' => $data->quantity,
            ],
        ]);
    }
}
