<?php

namespace App\Actions\Checkout;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\VoucherRepositoryInterface;
use App\Services\Checkout\CheckoutCartService;
use App\Services\Checkout\CheckoutPricingService;
use App\Services\Checkout\CheckoutVoucherService;
use RuntimeException;

class ApplyCheckoutVoucherAction
{
    public function __construct(
        protected CartRepositoryInterface $carts,
        protected VoucherRepositoryInterface $voucherRepository,
        protected CheckoutCartService $checkoutCart,
        protected CheckoutPricingService $pricing,
        protected CheckoutVoucherService $vouchers,
    ) {}

    public function execute(int $userId, string $voucherCode): void
    {
        $cart = $this->carts->firstOrCreateForUser($userId);
        $checkoutData = $this->checkoutCart->resolveCheckoutItems($cart);
        $cartItems = $checkoutData['items'];

        if ($cartItems->isEmpty()) {
            throw new RuntimeException('Không có sản phẩm để áp dụng voucher.');
        }

        $subtotal = $this->pricing->calculateSubtotal($cartItems);
        $voucher = $this->voucherRepository->findByCode($voucherCode);

        if (! $voucher) {
            throw new RuntimeException('Mã voucher không tồn tại.');
        }

        $currentUses = $this->vouchers->getUserVoucherUsage($voucher, $userId);

        if (! $voucher->isValid($subtotal, $currentUses, 1)) {
            throw new RuntimeException('Voucher không hợp lệ hoặc đã vượt giới hạn sử dụng.');
        }

        session([
            config('threadly.checkout.voucher_session_key') => [
                'voucher_id' => $voucher->id,
                'voucher_code' => $voucher->code,
            ],
        ]);
    }
}
