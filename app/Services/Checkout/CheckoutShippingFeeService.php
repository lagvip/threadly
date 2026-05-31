<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\AddressRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class CheckoutShippingFeeService
{
    public function __construct(
        protected CartRepositoryInterface $carts,
        protected AddressRepositoryInterface $addresses,
        protected CheckoutCartService $checkoutCart,
        protected CheckoutPricingService $pricing,
    ) {
    }

    public function calculate(User $user, int $addressId): int
    {
        $address = $this->addresses->findForUser($addressId, $user->id);

        Log::info('Shipping fee selected address', [
            'address_id' => $address->id,
            'ghn_province_id' => $address->ghn_province_id,
            'ghn_district_id' => $address->ghn_district_id,
            'ghn_ward_code' => $address->ghn_ward_code,
        ]);

        $cart = $this->carts->findForUser($user->id);
        $checkoutData = $this->checkoutCart->resolveCheckoutItems($cart);
        $cartItems = $checkoutData['items'];

        Log::info('Shipping fee checkout items', [
            'source' => $checkoutData['source'],
            'count' => $cartItems->count(),
        ]);

        if ($cartItems->isEmpty()) {
            throw new RuntimeException('Không có sản phẩm để tính phí vận chuyển.');
        }

        $shippingFee = $this->pricing->calculateShippingFromCart($cartItems, $address);

        Log::info('Shipping fee result', [
            'shipping_fee' => $shippingFee,
        ]);

        return $shippingFee;
    }
}
