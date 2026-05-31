<?php

namespace App\DTOs\Checkout;

class CheckoutOrderData
{
    public function __construct(
        public readonly string $name,
        public readonly string $phone,
        public readonly int $addressId,
        public readonly ?string $customerNote,
        public readonly string $paymentMethod,
    ) {
    }
}
