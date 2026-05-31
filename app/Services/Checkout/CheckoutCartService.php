<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Models\Cart;
use App\Models\Order;

class CheckoutCartService
{
    public function __construct(
        protected CartRepositoryInterface $carts,
        protected ProductVariantRepositoryInterface $variants,
    ) {
    }

    public function getSelectedCheckoutItemIds(Cart $cart): array
    {
        $selectedIds = collect(session(config('threadly.checkout.cart_session_key'), []))
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values();

        if ($selectedIds->isEmpty()) {
            return [];
        }

        return $this->carts->selectedDetailIds($cart->id, $selectedIds->all());
    }

    public function getCheckoutCartItems(Cart $cart)
    {
        $selectedIds = $this->getSelectedCheckoutItemIds($cart);

        if (empty($selectedIds)) {
            return collect();
        }

        return $this->carts->selectedDetails($cart->id, $selectedIds);
    }

    public function clearSelectedCartItems(Cart $cart, array $cartDetailIds): void
    {
        $cartDetailIds = collect($cartDetailIds)
            ->map(fn ($id) => (int) $id)
            ->filter()
            ->values()
            ->toArray();

        if (empty($cartDetailIds)) {
            return;
        }

        $this->carts->deleteDetails($cart->id, $cartDetailIds);
    }

    public function clearCart(Cart $cart): void
    {
        $this->carts->deleteAllDetails($cart->id);
    }

    public function clearUserCartByOrder(Order $order): void
    {
        $cart = $this->carts->findForUser($order->user_id);

        if (!$cart) {
            return;
        }

        $variantIds = $order->details()
            ->whereNotNull('variant_id')
            ->pluck('variant_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        if (empty($variantIds)) {
            return;
        }

        $this->carts->deleteDetailsByVariants($cart->id, $variantIds);
    }

    public function getBuyNowItems()
    {
        $buyNow = session(config('threadly.checkout.buy_now_session_key'));

        if (!$buyNow || empty($buyNow['variant_id']) || empty($buyNow['quantity'])) {
            return collect();
        }

        $variant = $this->variants->findWithRelationsOrNull((int) $buyNow['variant_id']);

        if (!$variant) {
            return collect();
        }

        $qty = (int) $buyNow['quantity'];

        if ($qty < 1) {
            return collect();
        }

        return collect([
            (object) [
                'id' => null,
                'quantity' => $qty,
                'variant' => $variant,
            ],
        ]);
    }

    public function resolveCheckoutItems(?Cart $cart = null): array
    {
        $buyNowItems = $this->getBuyNowItems();

        if ($buyNowItems->isNotEmpty()) {
            return [
                'source' => 'buy_now',
                'items' => $buyNowItems,
            ];
        }

        if (!$cart) {
            return [
                'source' => 'cart',
                'items' => collect(),
            ];
        }

        return [
            'source' => 'cart',
            'items' => $this->getCheckoutCartItems($cart),
        ];
    }
}
