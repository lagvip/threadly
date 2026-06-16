<?php

namespace App\Services\Client\Layout;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use Illuminate\Support\Collection;

class ClientLayoutService
{
    public function __construct(
        protected CategoryRepositoryInterface $categories,
        protected CartRepositoryInterface $carts,
    ) {}

    public function viewData(?int $userId): array
    {
        $headerCategories = $this->categories->rootTree();
        $cartSummary = $this->cartSummary($userId);

        return [
            'headerCategories' => $headerCategories,
            'footerCategories' => $headerCategories->take(6),
            'headerCartItems' => $cartSummary['items'],
            'headerCartCount' => $cartSummary['count'],
            'headerCartTotal' => $cartSummary['total'],
        ];
    }

    protected function cartSummary(?int $userId): array
    {
        if (! $userId) {
            return $this->emptyCartSummary();
        }

        $cart = $this->carts->findForUser($userId);

        if (! $cart) {
            return $this->emptyCartSummary();
        }

        $items = $this->carts->detailsForCart((int) $cart->id);
        $count = 0;
        $total = 0.0;

        foreach ($items as $item) {
            $quantity = (int) ($item->quantity ?? 1);
            $variant = $item->variant;
            $price = (float) ($variant->price_sale ?? ($variant->price ?? 0));

            $count += $quantity;
            $total += $price * $quantity;
        }

        return [
            'items' => $items,
            'count' => $count,
            'total' => $total,
        ];
    }

    protected function emptyCartSummary(): array
    {
        return [
            'items' => Collection::make(),
            'count' => 0,
            'total' => 0.0,
        ];
    }
}
