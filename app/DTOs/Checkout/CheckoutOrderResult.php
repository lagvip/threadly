<?php

namespace App\DTOs\Checkout;

use App\Models\Order;

class CheckoutOrderResult
{
    public function __construct(
        public readonly Order $order,
        public readonly string $paymentMethod,
        public readonly ?string $paymentUrl = null,
    ) {
    }

    public function isVnpay(): bool
    {
        return $this->paymentMethod === 'vnpay';
    }
}
