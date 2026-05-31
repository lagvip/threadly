<?php

namespace App\Actions\Checkout;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Models\CartDetail;
use RuntimeException;

class SelectCheckoutItemsAction
{
    public function __construct(
        protected CartRepositoryInterface $carts,
    ) {
    }

    public function execute(int $userId, array $selectedItems): array
    {
        $cart = $this->carts->findForUser($userId);

        if (!$cart) {
            throw new RuntimeException('Không tìm thấy giỏ hàng.');
        }

        $selectedIds = CartDetail::where('id_cart', $cart->id)
            ->whereIn('id', $selectedItems)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();

        if (empty($selectedIds)) {
            throw new RuntimeException('Không có sản phẩm hợp lệ để thanh toán.');
        }

        session([config('threadly.checkout.cart_session_key') => $selectedIds]);

        return $selectedIds;
    }
}
