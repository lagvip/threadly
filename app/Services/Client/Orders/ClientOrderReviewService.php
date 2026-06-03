<?php

namespace App\Services\Client\Orders;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use RuntimeException;

class ClientOrderReviewService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected ReviewRepositoryInterface $reviews,
    ) {
    }

    public function submit(int $orderId, int $detailId, int $userId, array $data): int
    {
        $order = $this->orders->findForUserWithReviewDetails($orderId, $userId);

        if (!$order->can_review) {
            throw new RuntimeException('Chỉ có thể bình luận sau khi đơn đã giao, đã thanh toán và bạn đã xác nhận nhận hàng.');
        }

        $detail = $order->details->firstWhere('id', $detailId);

        if (!$detail || !$detail->product) {
            throw new RuntimeException('Sản phẩm không thuộc đơn hàng này hoặc đã không còn tồn tại.');
        }

        $this->reviews->updateOrCreate(
            [
                'user_id' => $userId,
                'order_id' => $order->id,
                'order_detail_id' => $detail->id,
            ],
            [
                'product_id' => $detail->product_id,
                'product_variant_id' => $detail->variant_id,
                'rating' => (int) $data['rating'],
                'comment' => trim((string) $data['comment']),
                'product_name_snapshot' => $detail->product_name,
                'color_snapshot' => optional($detail->variant?->color)->name,
                'size_snapshot' => optional($detail->variant?->size)->name,
            ]
        );

        return $order->id;
    }
}
