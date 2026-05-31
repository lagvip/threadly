<?php

namespace App\DTOs\Checkout;

class BuyNowData
{
    public function __construct(
        public readonly int $variantId,
        public readonly int $quantity,
    ) {
    }
}
