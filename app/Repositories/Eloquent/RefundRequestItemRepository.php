<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\RefundRequestItemRepositoryInterface;
use App\Models\RefundRequest;
use App\Models\RefundRequestItem;
use Illuminate\Support\Collection;

class RefundRequestItemRepository implements RefundRequestItemRepositoryInterface
{
    public function create(array $data): RefundRequestItem
    {
        return RefundRequestItem::create($data);
    }

    public function update(RefundRequestItem $item, array $data): bool
    {
        return $item->update($data);
    }

    public function approvedQuantitiesForOrder(int $orderId): Collection
    {
        return $this->approvedSummaryForOrder($orderId)
            ->map(fn ($row) => (int) $row->refunded_quantity);
    }

    public function approvedSummaryForOrder(int $orderId): Collection
    {
        return RefundRequestItem::query()
            ->join('refund_requests', 'refund_request_items.refund_request_id', '=', 'refund_requests.id')
            ->where('refund_requests.order_id', $orderId)
            ->where('refund_requests.status', RefundRequest::STATUS_APPROVED)
            ->groupBy('refund_request_items.order_detail_id')
            ->selectRaw('refund_request_items.order_detail_id')
            ->selectRaw('COALESCE(SUM(refund_request_items.quantity), 0) as refunded_quantity')
            ->selectRaw('COALESCE(SUM(refund_request_items.line_amount), 0) as refunded_amount')
            ->get()
            ->keyBy('order_detail_id');
    }
}
