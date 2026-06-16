<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Models\RefundRequest;
use Illuminate\Database\Eloquent\Builder;

class RefundRequestRepository implements RefundRequestRepositoryInterface
{
    public function adminIndexQuery(): Builder
    {
        return RefundRequest::with(['order', 'user', 'evidences', 'items']);
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
