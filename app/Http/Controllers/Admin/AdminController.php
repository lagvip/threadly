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
    public function homeAdmin()
{
    $successfulOrdersQuery = Order::query()
        ->where('order_status', OrderStatus::Delivered->value)
        ->where('payment_status', 'paid');

    $kpiTotalOrders = Order::count();
    $kpiTotalProducts = Product::count();
    $kpiTotalStock = (int) ProductVariant::sum('quantity');

    $successfulOrdersAgg = (clone $successfulOrdersQuery)
        ->selectRaw('COALESCE(SUM(total_price), 0) as gross_sales')
        ->selectRaw('COALESCE(SUM(shipping_fee), 0) as shipping_collected')
        ->selectRaw('COALESCE(SUM(discount), 0) as discount_total')
        ->first();

    $grossSales = (float) ($successfulOrdersAgg->gross_sales ?? 0);
    $shippingCollected = (float) ($successfulOrdersAgg->shipping_collected ?? 0);
    $discountTotal = (float) ($successfulOrdersAgg->discount_total ?? 0);

    $netRevenue = $grossSales - $shippingCollected;

    $chartData30Days = $this->buildRevenueSeriesByDay(days: 30);
    $chartData7Days = $this->buildRevenueSeriesByDay(days: 7);
    $chartData12Months = $this->buildRevenueSeriesByMonth(months: 12);

    $topProducts = OrderDetail::query()
        ->join('orders', 'orders.id', '=', 'order_details.order_id')
        ->join('products', 'products.id', '=', 'order_details.product_id')
        ->where('orders.order_status', OrderStatus::Delivered->value)
        ->where('orders.payment_status', 'paid')
        ->groupBy('order_details.product_id', 'products.name')
        ->selectRaw('order_details.product_id as product_id')
        ->selectRaw('products.name as product_name')
        ->selectRaw('SUM(order_details.quantity) as sold_qty')
        ->selectRaw('SUM(order_details.total) as revenue')
        ->orderByDesc('sold_qty')
        ->limit(5)
        ->get();

    $categoryStats = OrderDetail::query()
        ->join('orders', 'orders.id', '=', 'order_details.order_id')
        ->join('products', 'products.id', '=', 'order_details.product_id')
        ->join('categories', 'categories.id', '=', 'products.id_category')
        ->where('orders.order_status', OrderStatus::Delivered->value)
        ->where('orders.payment_status', 'paid')
        ->groupBy('categories.id', 'categories.name')
        ->selectRaw('categories.id as category_id')
        ->selectRaw('categories.name as category_name')
        ->selectRaw('SUM(order_details.quantity) as sold_qty')
        ->selectRaw('SUM(order_details.total) as revenue')
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
            'shipping_collected' => $shippingCollected,
            'discount_total' => $discountTotal,
            'total_orders' => $kpiTotalOrders,
            'total_products' => $kpiTotalProducts,
            'total_stock' => $kpiTotalStock,
        ],
        'chartData30Days' => $chartData30Days,
        'chartData7Days' => $chartData7Days,
        'chartData12Months' => $chartData12Months,
        'topProducts' => $topProducts,
        'categoryStats' => $categoryStats,
        'lowStockVariants' => $lowStockVariants,
    ]);
}

    private function buildRevenueSeriesByDay(int $days): array
    {
        $end = Carbon::today();
        $start = (clone $end)->subDays($days - 1);

        $rows = Order::query()
            ->where('order_status', OrderStatus::Delivered->value)
            ->where('payment_status', 'paid')
            ->whereBetween('created_at', [$start->copy()->startOfDay(), $end->copy()->endOfDay()])
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy(DB::raw('DATE(created_at)'))
            ->selectRaw('DATE(created_at) as day')
            ->selectRaw('SUM(total_price - shipping_fee) as net_revenue')
            ->get();

        $map = $rows->pluck('net_revenue', 'day')->map(fn($v) => (float) $v)->toArray();

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
            ->whereBetween('created_at', [$start->copy()->startOfMonth(), $end->copy()->endOfMonth()])
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->orderBy(DB::raw("DATE_FORMAT(created_at, '%Y-%m')"))
            ->selectRaw("DATE_FORMAT(created_at, '%Y-%m') as ym")
            ->selectRaw('SUM(total_price - shipping_fee) as net_revenue')
            ->get();

        $map = $rows->pluck('net_revenue', 'ym')->map(fn($v) => (float) $v)->toArray();

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
}
