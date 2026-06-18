<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderPaymentStatus;
use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class OrderRepository implements OrderRepositoryInterface
{
    public function paginateForAdmin(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Order::with('user');

        if (! empty($filters['order_code'])) {
            $query->where('order_code', 'like', '%'.$filters['order_code'].'%');
        }

        if (! empty($filters['customer'])) {
            $customer = $filters['customer'];

            $query->where(function ($q) use ($customer) {
                $q->where('email', 'like', '%'.$customer.'%')
                    ->orWhere('name', 'like', '%'.$customer.'%')
                    ->orWhereHas('user', function ($subQuery) use ($customer) {
                        $subQuery->where('email', 'like', '%'.$customer.'%')
                            ->orWhere('name', 'like', '%'.$customer.'%');
                    });
            });
        }

        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (! empty($filters['order_status'])) {
            $query->where('order_status', $filters['order_status']);
        }

        return $query->latest()->paginate($perPage);
    }

    public function create(array $data): Order
    {
        return Order::create($data);
    }

    public function update(Order $order, array $data): bool
    {
        return $order->update($data);
    }

    public function delete(Order $order): bool
    {
        return (bool) $order->delete();
    }

    public function restore(Order $order): bool
    {
        return (bool) $order->restore();
    }

    public function forceDelete(Order $order): bool
    {
        return (bool) $order->forceDelete();
    }

    public function findOrFail(int $id): Order
    {
        return Order::findOrFail($id);
    }

    public function findByCode(string $orderCode): ?Order
    {
        return Order::where('order_code', $orderCode)->first();
    }

    public function orderCodeExists(string $orderCode): bool
    {
        return Order::where('order_code', $orderCode)->exists();
    }

    public function findByGhnCodes(?string $orderCode, ?string $clientOrderCode): ?Order
    {
        if (empty($orderCode) && empty($clientOrderCode)) {
            return null;
        }

        return Order::query()
            ->when($orderCode, fn ($q) => $q->orWhere('ghn_order_code', $orderCode))
            ->when($clientOrderCode, function ($q) use ($clientOrderCode) {
                $q->orWhere('ghn_client_order_code', $clientOrderCode)
                    ->orWhere('order_code', $clientOrderCode);
            })
            ->first();
    }

    public function lockById(int $id): Order
    {
        return Order::whereKey($id)->lockForUpdate()->firstOrFail();
    }

    public function paginateTrashedForAdmin(int $perPage = 10): LengthAwarePaginator
    {
        return Order::onlyTrashed()
            ->with('user')
            ->latest()
            ->paginate($perPage);
    }

    public function countByStatus(string $status): int
    {
        return Order::where('order_status', $status)->count();
    }

    public function countPendingPayment(): int
    {
        return Order::whereIn('payment_status', [
            OrderPaymentStatus::Unpaid->value,
            OrderPaymentStatus::Pending->value,
        ])->count();
    }

    public function recentForUser(int $userId, int $limit = 5)
    {
        return Order::where('user_id', $userId)->latest('id')->take($limit)->get();
    }

    public function recentForUserWithDetails(int $userId, int $limit = 3): Collection
    {
        return Order::with([
            'details.product',
            'details.variant.color',
            'details.variant.size',
        ])
            ->where('user_id', $userId)
            ->latest('id')
            ->take($limit)
            ->get();
    }

    public function countForUser(int $userId): int
    {
        return Order::where('user_id', $userId)->count();
    }

    public function countForUserByStatuses(int $userId, array $statuses): int
    {
        return Order::where('user_id', $userId)->whereIn('order_status', $statuses)->count();
    }

    public function countForUserByStatus(int $userId, string $status): int
    {
        return Order::where('user_id', $userId)->where('order_status', $status)->count();
    }

    public function paginateForUser(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = Order::with([
            'details.variant',
            'details.product',
            'reviews.variant.color',
            'reviews.variant.size',
            'refundRequests.admin',
        ])
            ->where('user_id', $userId);

        if (! empty($filters['order_code'])) {
            $query->where('order_code', 'like', '%'.$filters['order_code'].'%');
        }

        if (! empty($filters['customer'])) {
            $keyword = $filters['customer'];

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%'.$keyword.'%')
                    ->orWhere('email', 'like', '%'.$keyword.'%')
                    ->orWhere('phone', 'like', '%'.$keyword.'%')
                    ->orWhere('address', 'like', '%'.$keyword.'%');
            });
        }

        if (! empty($filters['payment_status'])) {
            $query->where('payment_status', $filters['payment_status']);
        }

        if (! empty($filters['order_status'])) {
            $query->where('order_status', $filters['order_status']);
        }

        return $query->latest('id')->paginate($perPage);
    }

    public function findForUserWithDetail(int $id, int $userId): Order
    {
        return Order::with([
            'details.variant.color',
            'details.variant.size',
            'details.product',
            'reviews.variant.color',
            'reviews.variant.size',
            'refundRequests.admin',
            'refundRequests.items',
        ])
            ->where('user_id', $userId)
            ->findOrFail($id);
    }

    public function findForUserWithDetails(int $id, int $userId): Order
    {
        return Order::with('details')
            ->where('user_id', $userId)
            ->findOrFail($id);
    }

    public function findForUserWithReviewDetails(int $id, int $userId): Order
    {
        return Order::with(['details.product', 'details.variant.color', 'details.variant.size'])
            ->where('user_id', $userId)
            ->findOrFail($id);
    }

    public function findForUser(int $id, int $userId): Order
    {
        return Order::where('user_id', $userId)->findOrFail($id);
    }

    public function lockForUserCancellation(int $id, int $userId): Order
    {
        return Order::with(['refundRequests', 'details'])
            ->where('user_id', $userId)
            ->whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    public function lockForRefundRequest(int $id): Order
    {
        return Order::with([
            'details.variant.color',
            'details.variant.size',
            'details.product',
            'refundRequests.items',
        ])
            ->whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    public function restoreManyWithTrashed(array $ids): int
    {
        return Order::withTrashed()->whereIn('id', $ids)->restore();
    }

    public function forceDeleteManyWithTrashed(array $ids): int
    {
        return Order::withTrashed()->whereIn('id', $ids)->forceDelete();
    }
}
