<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Enums\OrderStatus;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    public function homeAdmin(Request $request)
    {
        $fromInput = $request->query('from');
        $toInput = $request->query('to');

        try {
            $to = $toInput ? Carbon::createFromFormat('Y-m-d', (string) $toInput) : Carbon::today();
        } catch (\Throwable $e) {
            $to = Carbon::today();
        }

        try {
            $from = $fromInput ? Carbon::createFromFormat('Y-m-d', (string) $fromInput) : (clone $to)->subDays(29);
        } catch (\Throwable $e) {
            $from = (clone $to)->subDays(29);
        }

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        $fromDay = $from->copy()->startOfDay();
        $toDay = $to->copy()->endOfDay();

        /*
         * Doanh thu tính theo ngày khách xác nhận nhận hàng.
         * Chỉ tính đơn đã giao, đã thanh toán và khách đã xác nhận.
         * Doanh thu thực nhận = total_price - shipping_fee - refunded_amount.
         */
        $successfulOrdersQuery = Order::query()
            ->where('order_status', OrderStatus::Delivered->value)
            ->where('payment_status', 'paid')
            ->whereNotNull('customer_confirmed_at')
            ->whereBetween('customer_confirmed_at', [$fromDay, $toDay]);

        $kpiTotalProducts = Product::count();
        $kpiTotalStock = (int) ProductVariant::sum('quantity');

        $successfulOrdersAgg = (clone $successfulOrdersQuery)
            ->selectRaw('COALESCE(SUM(total_price), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(COALESCE(refunded_amount, 0)), 0) as refunded_total')
            ->selectRaw('COALESCE(SUM(COALESCE(shipping_fee, 0)), 0) as shipping_collected')
            ->selectRaw('COALESCE(SUM(COALESCE(discount, 0)), 0) as discount_total')
            ->selectRaw('COALESCE(SUM(GREATEST(total_price - COALESCE(shipping_fee, 0) - COALESCE(refunded_amount, 0), 0)), 0) as net_revenue')
            ->first();

        $grossSales = (float) ($successfulOrdersAgg->gross_sales ?? 0);
        $refundedTotal = (float) ($successfulOrdersAgg->refunded_total ?? 0);
        $shippingCollected = (float) ($successfulOrdersAgg->shipping_collected ?? 0);
        $discountTotal = (float) ($successfulOrdersAgg->discount_total ?? 0);
        $netRevenue = (float) ($successfulOrdersAgg->net_revenue ?? 0);

        $kpiTotalOrders = (clone $successfulOrdersQuery)->count();

        $approvedRefundItemsSub = $this->approvedRefundItemsSubquery();

        $kpiTotalProductsSold = (float) OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->leftJoinSub($approvedRefundItemsSub, 'approved_refund_items', function ($join) {
                $join->on('approved_refund_items.order_detail_id', '=', 'order_details.id');
            })
            ->where('orders.order_status', OrderStatus::Delivered->value)
            ->where('orders.payment_status', 'paid')
            ->whereNotNull('orders.customer_confirmed_at')
            ->whereBetween('orders.customer_confirmed_at', [$fromDay, $toDay])
            ->selectRaw('COALESCE(SUM(GREATEST(order_details.quantity - COALESCE(approved_refund_items.refunded_quantity, 0), 0)), 0) as sold_qty')
            ->value('sold_qty');

        $chartDataRange = $this->buildRevenueSeriesByDateRange($from, $to);

        $topProducts = OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->leftJoinSub($this->approvedRefundItemsSubquery(), 'approved_refund_items', function ($join) {
                $join->on('approved_refund_items.order_detail_id', '=', 'order_details.id');
            })
            ->where('orders.order_status', OrderStatus::Delivered->value)
            ->where('orders.payment_status', 'paid')
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

        $categoryStats = OrderDetail::query()
            ->join('orders', 'orders.id', '=', 'order_details.order_id')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->join('categories', 'categories.id', '=', 'products.id_category')
            ->leftJoinSub($this->approvedRefundItemsSubquery(), 'approved_refund_items', function ($join) {
                $join->on('approved_refund_items.order_detail_id', '=', 'order_details.id');
            })
            ->where('orders.order_status', OrderStatus::Delivered->value)
            ->where('orders.payment_status', 'paid')
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

        $lowStockVariants = ProductVariant::query()
            ->with('product')
            ->where('quantity', '<=', 5)
            ->orderBy('quantity')
            ->limit(10)
            ->get();

        return view('admin.homeAdmin', [
            'kpi' => [
                'net_revenue' => $netRevenue,
                'gross_sales' => $grossSales,
                'refunded_total' => $refundedTotal,
                'shipping_collected' => $shippingCollected,
                'discount_total' => $discountTotal,
                'total_orders' => $kpiTotalOrders,
                'total_products' => $kpiTotalProductsSold,
                'total_stock' => $kpiTotalStock,
            ],
            'chartDataRange' => $chartDataRange,
            'filterFrom' => $from->toDateString(),
            'filterTo' => $to->toDateString(),
            'topProducts' => $topProducts,
            'categoryStats' => $categoryStats,
            'lowStockVariants' => $lowStockVariants,
        ]);
    }

    private function approvedRefundItemsSubquery()
    {
        return DB::table('refund_request_items')
            ->join('refund_requests', 'refund_requests.id', '=', 'refund_request_items.refund_request_id')
            ->where('refund_requests.status', 'approved')
            ->groupBy('refund_request_items.order_detail_id')
            ->selectRaw('refund_request_items.order_detail_id')
            ->selectRaw('COALESCE(SUM(refund_request_items.quantity), 0) as refunded_quantity')
            ->selectRaw('COALESCE(SUM(refund_request_items.line_amount), 0) as refunded_amount');
    }

    private function buildRevenueSeriesByDay(int $days): array
    {
        $end = Carbon::today();
        $start = (clone $end)->subDays($days - 1);

        $rows = Order::query()
            ->where('order_status', OrderStatus::Delivered->value)
            ->where('payment_status', 'paid')
            ->whereNotNull('customer_confirmed_at')
            ->whereBetween('customer_confirmed_at', [
                $start->copy()->startOfDay(),
                $end->copy()->endOfDay(),
            ])
            ->groupBy(DB::raw('DATE(customer_confirmed_at)'))
            ->orderBy(DB::raw('DATE(customer_confirmed_at)'))
            ->selectRaw('DATE(customer_confirmed_at) as day')
            ->selectRaw('COALESCE(SUM(GREATEST(total_price - COALESCE(shipping_fee, 0) - COALESCE(refunded_amount, 0), 0)), 0) as net_revenue')
            ->get();

        $map = $rows->pluck('net_revenue', 'day')->map(fn ($v) => (float) $v)->toArray();

        $labels = [];
        $data = [];

        for ($i = 0; $i < $days; $i++) {
            $d = $start->copy()->addDays($i);
            $key = $d->toDateString();

            $labels[] = $d->format($days <= 7 ? 'D' : 'd/m');
            $data[] = $map[$key] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function buildRevenueSeriesByMonth(int $months): array
    {
        $end = Carbon::now()->startOfMonth();
        $start = (clone $end)->subMonths($months - 1);

        $rows = Order::query()
            ->where('order_status', OrderStatus::Delivered->value)
            ->where('payment_status', 'paid')
            ->whereNotNull('customer_confirmed_at')
            ->whereBetween('customer_confirmed_at', [
                $start->copy()->startOfMonth(),
                $end->copy()->endOfMonth(),
            ])
            ->groupBy(DB::raw("DATE_FORMAT(customer_confirmed_at, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(customer_confirmed_at, '%Y-%m')"))
            ->selectRaw("DATE_FORMAT(customer_confirmed_at, '%Y-%m') as ym")
            ->selectRaw('COALESCE(SUM(GREATEST(total_price - COALESCE(shipping_fee, 0) - COALESCE(refunded_amount, 0), 0)), 0) as net_revenue')
            ->get();

        $map = $rows->pluck('net_revenue', 'ym')->map(fn ($v) => (float) $v)->toArray();

        $labels = [];
        $data = [];

        for ($i = 0; $i < $months; $i++) {
            $m = $start->copy()->addMonths($i);
            $key = $m->format('Y-m');

            $labels[] = 'T' . $m->format('n');
            $data[] = $map[$key] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    private function buildRevenueSeriesByDateRange(Carbon $from, Carbon $to): array
    {
        $fromDay = $from->copy()->startOfDay();
        $toDay = $to->copy()->endOfDay();

        $days = $from->copy()
            ->startOfDay()
            ->diffInDays($to->copy()->startOfDay()) + 1;

        $rows = Order::query()
            ->where('order_status', OrderStatus::Delivered->value)
            ->where('payment_status', 'paid')
            ->whereNotNull('customer_confirmed_at')
            ->whereBetween('customer_confirmed_at', [$fromDay, $toDay])
            ->groupBy(DB::raw('DATE(customer_confirmed_at)'))
            ->orderBy(DB::raw('DATE(customer_confirmed_at)'))
            ->selectRaw('DATE(customer_confirmed_at) as day')
            ->selectRaw('COALESCE(SUM(GREATEST(total_price - COALESCE(shipping_fee, 0) - COALESCE(refunded_amount, 0), 0)), 0) as net_revenue')
            ->get();

        $map = $rows->pluck('net_revenue', 'day')->map(fn ($v) => (float) $v)->toArray();

        $labels = [];
        $data = [];

        for ($i = 0; $i < $days; $i++) {
            $d = $from->copy()->addDays($i);
            $key = $d->toDateString();

            $labels[] = $d->format('d/m');
            $data[] = $map[$key] ?? 0;
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }
}
