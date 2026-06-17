<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\CartRepositoryInterface;
use RuntimeException;

class SelectCheckoutItemsService
{
    public function __construct(
        protected CartRepositoryInterface $carts,
    ) {}

    public function execute(int $userId, array $selectedItems): array
    {
        $cart = $this->carts->findForUser($userId);

        if (! $cart) {
            throw new RuntimeException('Không tìm thấy giỏ hàng.');
        }

        $selectedIds = $this->carts->selectedDetailIds($cart->id, $selectedItems);

        if (empty($selectedIds)) {
            throw new RuntimeException('Không có sản phẩm hợp lệ để thanh toán.');
        }

        session([config('threadly.checkout.cart_session_key') => $selectedIds]);

        return $selectedIds;
    }
}
