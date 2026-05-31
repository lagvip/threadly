<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\AddressRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Models\User;
use RuntimeException;

class CheckoutPageService
{
    public function __construct(
        protected CartRepositoryInterface $carts,
        protected AddressRepositoryInterface $addresses,
        protected CheckoutCartService $checkoutCart,
        protected CheckoutPricingService $pricing,
        protected CheckoutVoucherService $vouchers,
    ) {
    }

    public function dataFor(User $user): array
    {
        $cart = $this->carts->firstOrCreateForUser($user->id);
        $checkoutData = $this->checkoutCart->resolveCheckoutItems($cart);
        $cartItems = $checkoutData['items'];

        if ($cartItems->isEmpty()) {
            throw new RuntimeException('Vui lòng chọn sản phẩm cần thanh toán.');
        }

        $addresses = $this->addresses->forUser($user->id);
        $defaultAddress = $addresses->firstWhere('is_default', 1) ?? $addresses->first();
        $shippingFee = 0;

        if ($defaultAddress && $defaultAddress->ghn_district_id && $defaultAddress->ghn_ward_code) {
            $shippingFee = $this->pricing->calculateShippingFromCart($cartItems, $defaultAddress);
        }

        $subtotal = $this->pricing->calculateSubtotal($cartItems);
        $appliedVoucher = $this->vouchers->getAppliedVoucherPreview($subtotal, $user->id);
        $discount = $appliedVoucher['discount'] ?? 0;

        return [
            'cartItems' => $cartItems,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
            'shippingFee' => $shippingFee,
            'subtotal' => $subtotal,
            'appliedVoucher' => $appliedVoucher,
            'discount' => $discount,
            'grandTotal' => max(0, $subtotal + $shippingFee - $discount),
            'availableVouchers' => $this->vouchers->getAvailableVouchersForCheckout($subtotal, $user->id),
        ];
    }
}
