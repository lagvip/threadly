<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Models\RefundRequest;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class RefundRequestRepository implements RefundRequestRepositoryInterface
{
    protected function adminIndexQuery(): Builder
    {
        return RefundRequest::with(['order', 'user', 'evidences', 'items']);
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->adminIndexQuery()->latest('id');

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);

            $query->where(function ($q) use ($keyword) {
                $q->whereHas('order', function ($orderQuery) use ($keyword) {
                    $orderQuery->where('order_code', 'like', '%'.$keyword.'%');
                })->orWhereHas('user', function ($userQuery) use ($keyword) {
                    $userQuery->where('email', 'like', '%'.$keyword.'%')
                        ->orWhere('name', 'like', '%'.$keyword.'%');
                });
            });
        }

        return $query->paginate($perPage);
    }

    public function loadForShow(RefundRequest $refundRequest): RefundRequest
    {
        return $refundRequest->load([
            'order.details.variant.product',
            'order.details.variant.color',
            'order.details.variant.size',
            'user',
            'admin',
            'restockedBy',
            'evidences',
            'items.orderDetail.variant.product',
            'walletTransactions',
        ]);
    }

    public function countByStatus(string $status): int
    {
        return RefundRequest::where('status', $status)->count();
    }

    public function create(array $data): RefundRequest
    {
        return RefundRequest::create($data);
    }

    public function update(RefundRequest $refundRequest, array $data): bool
    {
        return $refundRequest->update($data);
    }

    public function lockWithItems(int $id): RefundRequest
    {
        return RefundRequest::with('items')
            ->whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    public function lockWithItemsAndOrderDetail(int $id): RefundRequest
    {
        return RefundRequest::with(['items.orderDetail'])
            ->whereKey($id)
            ->lockForUpdate()
            ->firstOrFail();
    }

    public function lockById(int $id): RefundRequest
    {
        return RefundRequest::whereKey($id)->lockForUpdate()->firstOrFail();
    }
}
