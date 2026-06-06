<?php

namespace App\Services\Admin\Orders;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Support\Pagination;

class AdminOrderQueryService
{
    public function __construct(protected OrderRepositoryInterface $orders)
    {
    }

    public function indexData(array $filters): array
    {
        $query = $this->orders->adminIndexQuery();

        if (!empty($filters['order_code'])) {
            $query->where('order_code', 'like', '%' . $filters['order_code'] . '%');
        }

        if (!empty($filters['customer'])) {
            $customer = $filters['customer'];

            $query->where(function ($q) use ($customer) {
                $q->where('email', 'like', '%' . $customer . '%')
                    ->orWhere('name', 'like', '%' . $customer . '%')
                    ->orWhereHas('user', function ($subQuery) use ($customer) {
                        $subQuery->where('email', 'like', '%' . $customer . '%')
                            ->orWhere('name', 'like', '%' . $customer . '%');
                    });
            });
        }

        if (!empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (!empty($filters['order_status'])) {
            $query->where('order_status', $filters['order_status']);
        }

        return [
            'orders' => Pagination::withQueryString($query->latest()->paginate(10)),
            'orderCancel' => $this->orders->countByStatus(OrderStatus::Cancelled->value),
            'orderDelivering' => $this->orders->countByStatus(OrderStatus::Shipped->value),
            'pendingPayment' => $this->orders->countPendingPayment(),
            'orderDelivered' => $this->orders->countByStatus(OrderStatus::Delivered->value),
        ];
    }

    public function loadForShow(Order $order): Order
    {
        return $order->load([
            'user',
            'voucher',
            'details.variant.product',
            'details.variant.size',
            'details.variant.color',
        ]);
    }

    public function findForStatusUpdate(int $id): Order
    {
        return $this->orders->findOrFail($id);
    }

    public function trashData(): array
    {
        return [
            'orders' => $this->orders->trashedForAdmin()
                ->latest()
                ->paginate(10),
        ];
    }
}
