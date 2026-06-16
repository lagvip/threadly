<?php

namespace App\Actions\Checkout;

use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\DTOs\Checkout\BuyNowData;
use RuntimeException;

class BuyNowCheckoutAction
{
    public function __construct(
        protected ProductVariantRepositoryInterface $variants,
    ) {}

    public function execute(BuyNowData $data): void
    {
        $variant = $this->variants->findWithRelations($data->variantId);

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
