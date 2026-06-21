<?php

namespace App\Services\Client\Cart;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClientCartService
{
    public function __construct(
        protected CartRepositoryInterface $carts,
        protected ProductVariantRepositoryInterface $variants,
    ) {}

    public function indexData(int $userId): array
    {
        $cart = $this->carts->findForUser($userId);
        $cartItems = collect();
        $selectedCartItemIds = [];

        if ($cart) {
            $cartItems = $this->carts->detailsForCart($cart->id);

            $selectedCartItemIds = $this->selectedCartItemIds($cartItems);
        }

        return compact('cartItems', 'cart', 'selectedCartItemIds');
    }

    public function add(int $userId, int $variantId, int $quantity): void
    {
        if ($quantity < 1) {
            throw new RuntimeException('Số lượng phải lớn hơn 0.');
        }

        DB::transaction(function () use ($userId, $variantId, $quantity) {
            $variant = $this->variants->lockAvailableForCart($variantId);

            if (! $variant) {
                throw new RuntimeException('Sản phẩm hoặc biến thể hiện không còn được bán.');
            }

            if ((int) $variant->quantity < 1) {
                throw new RuntimeException('Biến thể này đã hết hàng.');
            }

            $cart = $this->carts->firstOrCreateForUser($userId);
            $cartDetail = $this->carts->lockDetailByVariant((int) $cart->id, (int) $variant->id);
            $newQty = ($cartDetail ? (int) $cartDetail->quantity : 0) + $quantity;

            if ($newQty > (int) $variant->quantity) {
                throw new RuntimeException('Số lượng vượt quá tồn kho.');
            }

            if ($cartDetail) {
                $this->carts->updateDetail($cartDetail, ['quantity' => $newQty]);

                return;
            }

            $this->carts->createDetail([
                'id_cart' => $cart->id,
                'id_variant' => $variant->id,
                'quantity' => $quantity,
            ]);
        });
    }

    public function update(int $userId, array $quantities): void
    {
        $cart = $this->carts->findForUser($userId);

        if (! $cart) {
            throw new RuntimeException('Không tìm thấy giỏ hàng.');
        }

        DB::transaction(function () use ($cart, $quantities) {
            $cartItems = $this->carts->detailsForUpdate($cart->id, array_keys($quantities));

            foreach ($cartItems as $item) {
                $newQty = (int) ($quantities[$item->id] ?? 1);

                if ($newQty < 1) {
                    throw new RuntimeException('Số lượng phải lớn hơn 0.');
                }

                $variant = $this->variants->lockAvailableForCart((int) $item->id_variant);

                if (! $variant || (int) $variant->quantity < 1) {
                    $this->carts->deleteDetail($item);

                    continue;
                }

                if ($newQty > (int) $variant->quantity) {
                    throw new RuntimeException('Sản phẩm "'.($variant->product->name ?? 'N/A').'" vượt quá tồn kho.');
                }

                $this->carts->updateDetail($item, ['quantity' => $newQty]);
            }
        });

        $this->syncSelectedCheckoutItems($cart);
    }

    public function remove(int $userId, int $id): void
    {
        $cart = $this->carts->findForUser($userId);

        if (! $cart) {
            throw new RuntimeException('Không tìm thấy giỏ hàng.');
        }

        $this->carts->deleteDetail($this->carts->findDetailForCart($cart->id, $id));

        $this->removeSelectedCheckoutItem($id);
    }

    protected function selectedCartItemIds($cartItems): array
    {
        $selectedCartItemIds = collect(session($this->checkoutCartSessionKey(), []))
            ->map(fn ($id) => (int) $id)
            ->filter(fn ($id) => $cartItems->contains('id', $id))
            ->values()
            ->toArray();

        if (empty($selectedCartItemIds) && $cartItems->isNotEmpty()) {
            return $cartItems->pluck('id')->map(fn ($id) => (int) $id)->toArray();
        }

        return $selectedCartItemIds;
    }

    protected function syncSelectedCheckoutItems(Cart $cart): void
    {
        $selectedIds = collect(session($this->checkoutCartSessionKey(), []))
            ->map(fn ($id) => (int) $id)
            ->values();

        if ($selectedIds->isEmpty()) {
            return;
        }

        $validIds = $this->carts->validDetailIds($cart->id, $selectedIds->all());

        empty($validIds)
            ? session()->forget($this->checkoutCartSessionKey())
            : session([$this->checkoutCartSessionKey() => $validIds]);
    }

    protected function removeSelectedCheckoutItem(int $id): void
    {
        $selectedIds = collect(session($this->checkoutCartSessionKey(), []))
            ->map(fn ($itemId) => (int) $itemId)
            ->reject(fn ($itemId) => $itemId === $id)
            ->values()
            ->toArray();

        empty($selectedIds)
            ? session()->forget($this->checkoutCartSessionKey())
            : session([$this->checkoutCartSessionKey() => $selectedIds]);
    }

    protected function checkoutCartSessionKey(): string
    {
        return config('threadly.checkout.cart_session_key');
    }
}
