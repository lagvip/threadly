<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\RefundRequestStatus;
use App\Models\Order;
use App\Models\OrderDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardRepository implements DashboardRepositoryInterface
{
    public function successfulOrdersQuery(Carbon $fromDay, Carbon $toDay)
    {
        return Order::query()
            ->where('order_status', OrderStatus::Delivered->value)
            ->where('payment_status', OrderPaymentStatus::Paid->value)
            ->whereNotNull('customer_confirmed_at')
            ->whereBetween('customer_confirmed_at', [$fromDay, $toDay]);
    }

    public function soldQuantity(Carbon $fromDay, Carbon $toDay): float
    {
        return (float) OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->leftJoinSub($this->approvedRefundItemsSubquery(), 'approved_refund_items', function ($join) {
                $join->on('approved_refund_items.order_detail_id', '=', 'order_details.id');
            })
            ->where('orders.order_status', OrderStatus::Delivered->value)
            ->where('orders.payment_status', OrderPaymentStatus::Paid->value)
            ->whereNotNull('orders.customer_confirmed_at')
            ->whereBetween('orders.customer_confirmed_at', [$fromDay, $toDay])
            ->selectRaw('COALESCE(SUM(GREATEST(order_details.quantity - COALESCE(approved_refund_items.refunded_quantity, 0), 0)), 0) as sold_qty')
            ->value('sold_qty');
    }

    public function topProducts(Carbon $fromDay, Carbon $toDay)
    {
        return OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->leftJoinSub($this->approvedRefundItemsSubquery(), 'approved_refund_items', function ($join) {
                $join->on('approved_refund_items.order_detail_id', '=', 'order_details.id');
            })
            ->where('orders.order_status', OrderStatus::Delivered->value)
            ->where('orders.payment_status', OrderPaymentStatus::Paid->value)
            ->whereNotNull('orders.customer_confirmed_at')
            ->whereBetween('orders.customer_confirmed_at', [$fromDay, $toDay])
            ->groupBy('order_details.product_id', 'products.name')
            ->selectRaw('order_details.product_id as product_id')
            ->selectRaw('products.name as product_name')
            ->selectRaw('COALESCE(SUM(GREATEST(order_details.quantity - COALESCE(approved_refund_items.refunded_quantity, 0), 0)), 0) as sold_qty')
            ->selectRaw('COALESCE(SUM(GREATEST(order_details.total - COALESCE(approved_refund_items.refunded_amount, 0), 0)), 0) as revenue')
            ->havingRaw('sold_qty > 0')
            ->orderByDesc('sold_qty')
            ->limit(5)
            ->get();
    }

    public function categoryStats(Carbon $fromDay, Carbon $toDay)
    {
        return OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->join('categories', 'categories.id', '=', 'products.id_category')
            ->leftJoinSub($this->approvedRefundItemsSubquery(), 'approved_refund_items', function ($join) {
                $join->on('approved_refund_items.order_detail_id', '=', 'order_details.id');
            })
            ->where('orders.order_status', OrderStatus::Delivered->value)
            ->where('orders.payment_status', OrderPaymentStatus::Paid->value)
            ->whereNotNull('orders.customer_confirmed_at')
            ->whereBetween('orders.customer_confirmed_at', [$fromDay, $toDay])
            ->groupBy('categories.id', 'categories.name')
            ->selectRaw('categories.id as category_id')
            ->selectRaw('categories.name as category_name')
            ->selectRaw('COALESCE(SUM(GREATEST(order_details.quantity - COALESCE(approved_refund_items.refunded_quantity, 0), 0)), 0) as sold_qty')
            ->selectRaw('COALESCE(SUM(GREATEST(order_details.total - COALESCE(approved_refund_items.refunded_amount, 0), 0)), 0) as revenue')
            ->havingRaw('revenue > 0')
            ->orderByDesc('revenue')
            ->get();
    }

    public function revenueRows(Carbon $fromDay, Carbon $toDay)
    {
        return Order::query()
            ->where('order_status', OrderStatus::Delivered->value)
            ->where('payment_status', OrderPaymentStatus::Paid->value)
            ->whereNotNull('customer_confirmed_at')
            ->whereBetween('customer_confirmed_at', [$fromDay, $toDay])
            ->groupBy(DB::raw('DATE(customer_confirmed_at)'))
            ->orderBy(DB::raw('DATE(customer_confirmed_at)'))
            ->selectRaw('DATE(customer_confirmed_at) as day')
            ->selectRaw('COALESCE(SUM(GREATEST(total_price - COALESCE(shipping_fee, 0) - COALESCE(refunded_amount, 0), 0)), 0) as net_revenue')
            ->get();
    }

    protected function approvedRefundItemsSubquery()
    {
        return DB::table('refund_request_items')
            ->join('refund_requests', 'refund_requests.id', '=', 'refund_request_items.refund_request_id')
            ->where('refund_requests.status', RefundRequestStatus::Approved->value)
            ->groupBy('refund_request_items.order_detail_id')
            ->selectRaw('refund_request_items.order_detail_id')
            ->selectRaw('COALESCE(SUM(refund_request_items.quantity), 0) as refunded_quantity')
            ->selectRaw('COALESCE(SUM(refund_request_items.line_amount), 0) as refunded_amount');
    }
}
