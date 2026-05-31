<?php

namespace App\Services\Admin\Dashboard;

use App\Contracts\Repositories\DashboardRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use Carbon\Carbon;

class AdminDashboardService
{
    public function __construct(
        protected DashboardRepositoryInterface $dashboard,
        protected ProductVariantRepositoryInterface $variants,
    ) {
    }

    public function data(array $filters): array
    {
        [$from, $to] = $this->dateRange($filters['from'] ?? null, $filters['to'] ?? null);
        $fromDay = $from->copy()->startOfDay();
        $toDay = $to->copy()->endOfDay();
        $successfulOrdersQuery = $this->dashboard->successfulOrdersQuery($fromDay, $toDay);
        $successfulOrdersAgg = (clone $successfulOrdersQuery)
            ->selectRaw('COALESCE(SUM(total_price), 0) as gross_sales')
            ->selectRaw('COALESCE(SUM(COALESCE(refunded_amount, 0)), 0) as refunded_total')
            ->selectRaw('COALESCE(SUM(COALESCE(shipping_fee, 0)), 0) as shipping_collected')
            ->selectRaw('COALESCE(SUM(COALESCE(discount, 0)), 0) as discount_total')
            ->selectRaw('COALESCE(SUM(GREATEST(total_price - COALESCE(shipping_fee, 0) - COALESCE(refunded_amount, 0), 0)), 0) as net_revenue')
            ->first();

        return [
            'kpi' => [
                'net_revenue' => (float) ($successfulOrdersAgg->net_revenue ?? 0),
                'gross_sales' => (float) ($successfulOrdersAgg->gross_sales ?? 0),
                'refunded_total' => (float) ($successfulOrdersAgg->refunded_total ?? 0),
                'shipping_collected' => (float) ($successfulOrdersAgg->shipping_collected ?? 0),
                'discount_total' => (float) ($successfulOrdersAgg->discount_total ?? 0),
                'total_orders' => (clone $successfulOrdersQuery)->count(),
                'total_products' => $this->dashboard->soldQuantity($fromDay, $toDay),
                'total_stock' => $this->variants->totalStock(),
            ],
            'chartDataRange' => $this->revenueSeriesByDateRange($from, $to),
            'filterFrom' => $from->toDateString(),
            'filterTo' => $to->toDateString(),
            'topProducts' => $this->dashboard->topProducts($fromDay, $toDay),
            'categoryStats' => $this->dashboard->categoryStats($fromDay, $toDay),
            'lowStockVariants' => $this->variants->lowStock(),
        ];
    }

    protected function dateRange(?string $fromInput, ?string $toInput): array
    {
        try {
            $to = $toInput ? Carbon::createFromFormat('Y-m-d', $toInput) : Carbon::today();
        } catch (\Throwable $e) {
            $to = Carbon::today();
        }

        try {
            $from = $fromInput ? Carbon::createFromFormat('Y-m-d', $fromInput) : (clone $to)->subDays(29);
        } catch (\Throwable $e) {
            $from = (clone $to)->subDays(29);
        }

        if ($from->greaterThan($to)) {
            [$from, $to] = [$to, $from];
        }

        return [$from, $to];
    }

    protected function revenueSeriesByDateRange(Carbon $from, Carbon $to): array
    {
        $days = $from->copy()->startOfDay()->diffInDays($to->copy()->startOfDay()) + 1;
        $rows = $this->dashboard->revenueRows($from->copy()->startOfDay(), $to->copy()->endOfDay());

        $map = $rows->pluck('net_revenue', 'day')->map(fn ($v) => (float) $v)->toArray();
        $labels = [];
        $data = [];

        for ($i = 0; $i < $days; $i++) {
            $date = $from->copy()->addDays($i);
            $labels[] = $date->format('d/m');
            $data[] = $map[$date->toDateString()] ?? 0;
        }

        return compact('labels', 'data');
    }
}
