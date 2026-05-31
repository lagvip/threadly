<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\RefundRequestItemRepositoryInterface;
use App\Models\RefundRequest;
use App\Models\RefundRequestItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class RefundRequestItemRepository implements RefundRequestItemRepositoryInterface
{
    public function create(array $data): RefundRequestItem
    {
        return RefundRequestItem::create($data);
    }

    public function approvedQuantitiesForOrder(int $orderId): Collection
    {
        return RefundRequestItem::query()
            ->select('refund_request_items.order_detail_id', DB::raw('SUM(refund_request_items.quantity) as refunded_quantity'))
            ->join('refund_requests', 'refund_request_items.refund_request_id', '=', 'refund_requests.id')
            ->where('refund_requests.order_id', $orderId)
            ->where('refund_requests.status', RefundRequest::STATUS_APPROVED)
            ->groupBy('refund_request_items.order_detail_id')
            ->pluck('refunded_quantity', 'order_detail_id');
    }
}
