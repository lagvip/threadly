<?php

namespace App\Services\Checkout;

use App\Models\Address;
use App\Services\Integrations\Ghn\GhnService;

class CheckoutPricingService
{
    public function __construct(
        protected GhnService $ghn,
    ) {}

    public function calculateSubtotal($cartItems): float
    {
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $variant = $item->variant;

            if (! $variant) {
                continue;
            }

            $subtotal += ((float) $variant->price * (int) $item->quantity);
        }

        return (float) $subtotal;
    }

    public function calculateShippingFromCart($cartItems, Address $address): int
    {
        if (! $address->ghn_district_id || ! $address->ghn_ward_code) {
            return 0;
        }

        $totalWeight = 0;

        foreach ($cartItems as $item) {
            $weight = (int) ($item->variant->product->weight ?? 500);
            $qty = (int) $item->quantity;
            $totalWeight += ($weight * $qty);
        }

        return $this->ghn->calculateFee(
            (int) $address->ghn_district_id,
            (string) $address->ghn_ward_code,
            max($totalWeight, 100)
        );
    }

    public function buildFullAddress(Address $address): string
    {
        return trim(implode(', ', array_filter([
            $address->detailed_address,
            $address->ward,
            $address->district,
            $address->province,
        ])));
    }
}
