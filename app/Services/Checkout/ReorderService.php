<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class ReorderService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected CartRepositoryInterface $carts,
        protected ProductVariantRepositoryInterface $variants,
    ) {
    }

    public function execute(User $user, int $orderId): array
    {
        $order = $this->orders->findForUserWithDetails($orderId, (int) $user->id);

        $cart = $this->carts->firstOrCreateForUser((int) $user->id);
        $addedQty = 0;
        $skipped = 0;

        try {
            DB::transaction(function () use ($order, $cart, &$addedQty, &$skipped) {
                foreach ($order->details as $detail) {
                    if (!$detail->variant_id) {
                        $skipped++;
                        continue;
                    }

                    $variant = $this->variants->query()->with('product')->find($detail->variant_id);

                    if (!$variant || !$variant->product || $variant->status !== 'active') {
                        $skipped++;
                        continue;
                    }

                    $stock = (int) $variant->quantity;
                    $wantedQty = (int) $detail->quantity;

                    if ($stock <= 0 || $wantedQty <= 0) {
                        $skipped++;
                        continue;
                    }

                    $cartItem = $this->carts->lockDetailByVariant((int) $cart->id, (int) $variant->id);

                    $currentCartQty = $cartItem ? (int) $cartItem->quantity : 0;
                    $canAdd = min($wantedQty, max($stock - $currentCartQty, 0));

                    if ($canAdd <= 0) {
                        $skipped++;
                        continue;
                    }

                    if ($cartItem) {
                        $cartItem->update(['quantity' => $currentCartQty + $canAdd]);
                    } else {
                        $this->carts->createDetail([
                            'id_cart' => $cart->id,
                            'id_variant' => $variant->id,
                            'quantity' => $canAdd,
                        ]);
                    }

                    $addedQty += $canAdd;
                }
            });
        } catch (\Throwable $e) {
            Log::error('Reorder error: ' . $e->getMessage());
            throw new RuntimeException('Có lỗi xảy ra khi mua lại đơn hàng.');
        }

        if ($addedQty === 0) {
            throw new RuntimeException('Không có sản phẩm hợp lệ để mua lại.');
        }

        $message = "Đã thêm {$addedQty} sản phẩm từ đơn cũ vào giỏ hàng.";

        if ($skipped > 0) {
            $message .= " Có {$skipped} sản phẩm không thêm được vì hết hàng, ngừng bán hoặc giỏ đã đủ số lượng.";
        }

        return [
            'added_qty' => $addedQty,
            'skipped' => $skipped,
            'message' => $message,
        ];
    }
}
