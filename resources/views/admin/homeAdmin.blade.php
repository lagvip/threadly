@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <h3 class="fw-bold text-uppercase mb-4">📊 Bảng điều khiển thống kê</h3>

    <div class="card p-3 mb-4 shadow-sm">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label mb-1">Từ ngày</label>
                <input
                    type="date"
                    class="form-control"
                    name="from"
                    value="{{ $filterFrom ?? '' }}"
                />
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label mb-1">Đến ngày</label>
                <input
                    type="date"
                    class="form-control"
                    name="to"
                    value="{{ $filterTo ?? '' }}"
                />
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Lọc</button>
                <a href="{{ route('admin.homeAdmin') }}" class="btn btn-outline-secondary">Mặc định</a>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card p-3 d-flex flex-row align-items-center shadow-sm h-100">
                <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                    <i class="fas fa-sack-dollar text-success fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Tổng Doanh Thu</div>
                    <div class="fw-bold fs-5">{{ number_format((float)($kpi['net_revenue'] ?? 0), 0, ',', '.') }} đ</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card p-3 d-flex flex-row align-items-center shadow-sm h-100">
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                    <i class="fas fa-shopping-cart text-warning fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Tổng Đơn Hàng</div>
                    <div class="fw-bold fs-5">{{ number_format((int)($kpi['total_orders'] ?? 0), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card p-3 d-flex flex-row align-items-center shadow-sm h-100">
                <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3">
                    <i class="fas fa-boxes text-info fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Tổng Sản Phẩm</div>
                    <div class="fw-bold fs-5">{{ number_format((int)($kpi['total_products'] ?? 0), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card p-3 d-flex flex-row align-items-center shadow-sm h-100">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                    <i class="fas fa-users text-primary fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Tồn Kho</div>
                    <div class="fw-bold fs-5">{{ number_format((int)($kpi['total_stock'] ?? 0), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12 mb-4">
            <div class="card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">📈 Thống Kê Doanh Thu</h6>
                    <div class="text-muted small">{{ $filterFrom ?? '' }} - {{ $filterTo ?? '' }}</div>
                </div>
                <div class="chart-container" style="position: relative; height:350px; width:100%">
                    <canvas id="mainRevenueChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 fw-bold">🔥 Top 5 Sản Phẩm Bán Chạy</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th class="text-center">Đã bán</th>
                                    <th class="text-end">Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($topProducts ?? []) as $item)
                                    <tr>
                                        <td>{{ $item->product_name }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info rounded-pill">{{ number_format((int)$item->sold_qty, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-end">{{ number_format((float)$item->revenue, 0, ',', '.') }} đ</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">Chưa có dữ liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 fw-bold">📂 Thống Kê Theo Danh Mục</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Danh mục</th>
                                    <th class="text-center">SL Bán</th>
                                    <th class="text-end">Tổng Doanh thu</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($categoryStats ?? []) as $item)
                                    <tr>
                                        <td>{{ $item->category_name }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-info rounded-pill">{{ number_format((int)$item->sold_qty, 0, ',', '.') }}</span>
                                        </td>
                                        <td class="text-end fw-medium">{{ number_format((float)$item->revenue, 0, ',', '.') }} đ</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted py-3">Chưa có dữ liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 fw-bold">⚠️ Sản Phẩm Sắp Hết Hàng (Tồn kho <= 5)</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tên sản phẩm</th>
                                    <th class="text-center">Số lượng còn</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse(($lowStockVariants ?? []) as $variant)
                                    <tr>
                                        <td>{{ $variant->product->name ?? 'Không có' }}</td>
                                        <td class="text-center">
                                            <span class="badge bg-danger fw-bold">{{ number_format((int)$variant->quantity, 0, ',', '.') }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted py-3">Không có sản phẩm sắp hết hàng</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table th, .table td {
    vertical-align: middle;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let mainRevenueChart;

    const chartDataRange = @json($chartDataRange ?? ['labels' => [], 'data' => []]);

    const defaultLineOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return 'Doanh thu: ' + new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(context.parsed.y);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: value => new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(value)
                }
            },
            x: {
                grid: { display: false },
                ticks: { autoSkip: true, maxRotation: 0, maxTicksLimit: 12 }
            }
        },
        interaction: { intersect: false, mode: 'index' }
    };

    const initMainRevenueChart = (chartData) => {
        const ctx = document.getElementById('mainRevenueChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 350);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');

        if (mainRevenueChart) {
            mainRevenueChart.destroy();
        }

        mainRevenueChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: chartData.labels,
                datasets: [{
                    label: 'Doanh thu',
                    data: chartData.data,
                    backgroundColor: gradient,
                    borderColor: '#3b82f6',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: '#3b82f6',
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: defaultLineOptions
        });
    };

    initMainRevenueChart(chartDataRange);
});
</script>
@endsection
