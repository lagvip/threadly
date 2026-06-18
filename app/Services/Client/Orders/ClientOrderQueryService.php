<?php

namespace App\Services\Client\Orders;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\RefundRequestStatus;
use App\Support\Pagination;

class ClientOrderQueryService
{
    public function __construct(protected OrderRepositoryInterface $orders) {}

    public function indexData(int $userId, array $filters): array
    {
        return [
            'orders' => Pagination::withQueryString($this->orders->paginateForUser($userId, $filters, 10)),
            'paymentStatusOptions' => $this->paymentStatusOptions(),
            'orderStatusOptions' => $this->orderStatusOptions(),
            'approvedRefundRequestStatus' => RefundRequestStatus::Approved->value,
            'rejectedRefundRequestStatus' => RefundRequestStatus::Rejected->value,
        ];
    }

    public function showData(int $id, int $userId): array
    {
        $order = $this->orders->findForUserWithDetail($id, $userId);

        return [
            'order' => $order,
            'reviewItems' => $order->details
                ->filter(fn ($item) => ! empty($item->product_id) && ! is_null($item->product))
                ->values()
                ->map(function ($item) use ($order) {
                    $item->existing_review = $order->reviews->firstWhere('order_detail_id', $item->id);

                    return $item;
                }),
        ];
    }

    protected function paymentStatusOptions(): array
    {
        return collect(OrderPaymentStatus::cases())
            ->mapWithKeys(fn (OrderPaymentStatus $status) => [$status->value => $status->label()])
            ->all();
    }

    protected function orderStatusOptions(): array
    {
        return collect(OrderStatus::cases())
            ->mapWithKeys(fn (OrderStatus $status) => [$status->value => $status->label()])
            ->all();
    }
}
