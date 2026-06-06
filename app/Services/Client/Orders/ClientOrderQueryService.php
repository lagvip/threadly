<?php

namespace App\Services\Client\Orders;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Support\Pagination;

class ClientOrderQueryService
{
    public function __construct(protected OrderRepositoryInterface $orders)
    {
    }

    public function indexData(int $userId, array $filters): array
    {
        $query = $this->orders->clientIndexQuery($userId);

        if (!empty($filters['order_code'])) {
            $query->where('order_code', 'like', '%' . $filters['order_code'] . '%');
        }

        if (!empty($filters['customer'])) {
            $keyword = $filters['customer'];

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%')
                    ->orWhere('address', 'like', '%' . $keyword . '%');
            });
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['order_status'])) {
            $query->where('order_status', $filters['order_status']);
        }

        return [
            'orders' => Pagination::withQueryString($query->latest('id')->paginate(10)),
        ];
    }

    public function showData(int $id, int $userId): array
    {
        $order = $this->orders->findForUserWithDetail($id, $userId);

        return [
            'order' => $order,
            'reviewItems' => $order->details
                ->filter(fn ($item) => !empty($item->product_id) && !is_null($item->product))
                ->values()
                ->map(function ($item) use ($order) {
                    $item->existing_review = $order->reviews->firstWhere('order_detail_id', $item->id);

                    return $item;
                }),
        ];
    }
}
