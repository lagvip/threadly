@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid "> {{-- Sử dụng container-fluid để rộng hơn --}}
    <h3 class="fw-bold text-uppercase mb-4">📊 Dashboard Thống Kê</h3>

    {{-- Tổng quan --}}
    <div class="row">
        {{-- Dùng col-lg-3 col-md-6 để responsive tốt hơn --}}
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card p-3 d-flex flex-row align-items-center shadow-sm h-100">
                <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                    <i class="fas fa-sack-dollar text-success fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Tổng Doanh Thu</div>
                    <div class="fw-bold fs-5">{{ number_format($totalRevenue, 0, ',', '.') }} đ</div>
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
                    <div class="fw-bold fs-5">{{ $totalOrders }}</div>
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
                    <div class="fw-bold fs-5">{{ $totalProducts }}</div>
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
                    <div class="fw-bold fs-5">{{ $totalStock }}</div>
                </div>
            </div>
        </div>
    </div>


    {{-- Biểu đồ --}}
     <div class="row">
        <div class="col-12 mb-4">
            <div class="card p-3 h-100">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="mb-0">📈 Thống Kê Doanh Thu</h6>
                    <div class="btn-group btn-group-sm" role="group" id="revenuePeriodSelector">
                        <button type="button" class="btn btn-outline-primary" data-period="7d">7 Ngày</button>
                        <button type="button" class="btn btn-outline-primary active" data-period="30d">30 Ngày</button>
                        <button type="button" class="btn btn-outline-primary" data-period="12m">12 Tháng</button>
                    </div>
                </div>
                <div class="chart-container" style="position: relative; height:350px; width:100%">
                    <canvas id="mainRevenueChart"></canvas>
                    <div id="revenue-chart-loader" class="chart-loader">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
     <div class="row">
        {{-- Bảng: Top 5 Sản phẩm bán chạy --}}
        <div class="col-lg-6 mb-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 fw-bold">🔥 Top 5 Sản Phẩm Bán Chạy</h6>
                </div>
                <div class="card-body p-0">
                    @if($topProducts->count() > 0)
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
                                    @foreach($topProducts as $product)
                                        <tr>
                                            <td>{{ $product->product_name }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info rounded-pill">{{ $product->total_sold }}</span>
                                            </td>
                                            <td class="text-end">{{ number_format($product->total_revenue, 0, ',', '.') }} đ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center py-5 mb-0 text-muted">Chưa có dữ liệu bán hàng.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bảng: Doanh thu theo Danh mục --}}
        <div class="col-lg-6 mb-4">
             <div class="card h-100 shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 fw-bold">📂 Thống Kê Theo Danh Mục</h6>
                </div>
                <div class="card-body p-0">
                    @if($categoryRevenue->count() > 0)
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
                                    @foreach($categoryRevenue as $category)
                                        <tr>
                                            <td>{{ $category->category_name }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info rounded-pill">{{ $category->total_quantity }}</span>
                                            </td>
                                            <td class="text-end fw-medium">{{ number_format($category->total_revenue, 0, ',', '.') }} đ</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center py-5 mb-0 text-muted">Chưa có dữ liệu.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Bảng: Sản phẩm sắp hết hàng --}}
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-transparent border-0">
                    <h6 class="mb-0 fw-bold">⚠️ Sản Phẩm Sắp Hết Hàng (Tồn kho <= 5)</h6>
                </div>
                <div class="card-body p-0">
                    @if($lowStock->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Tên sản phẩm</th>
                                        <th class="text-center">Số lượng còn</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($lowStock as $item)
                                        <tr>
                                            <td>{{ $item->name }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-danger fw-bold">{{ $item->quantity }}</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-center py-4 mb-0 text-muted">🎉 Tuyệt vời! Không có sản phẩm nào sắp hết hàng.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- CSS cho loader và thông báo --}}
<style>
.chart-loader {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    background-color: rgba(255, 255, 255, 0.7);
    display: flex; justify-content: center; align-items: center;
    z-index: 10; display: none;
}
.no-data-message {
    position: absolute; top: 50%; left: 50%;
    transform: translate(-50%, -50%);
    color: #888;
}
.card{
    margin: 0 !important;
}
.chart-loader {
    position: absolute; top: 0; left: 0; width: 100%; height: 100%;
    background-color: rgba(255, 255, 255, 0.7);
    display: flex; justify-content: center; align-items: center;
    z-index: 10; display: none;
}
/* Căn chỉnh bảng cho đẹp */
.table th, .table td {
    vertical-align: middle;
}
</style>

{{-- SCRIPT (Giữ nguyên như phiên bản trước) --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {

    // --- Biến toàn cục cho các biểu đồ ---
    let mainRevenueChart;
    let topProductsChart;
    let revenueByCategoryChart;

    // --- Dữ liệu ban đầu từ Controller ---
    const chartData = {
        initialRevenueData: {!! json_encode($charts['initialRevenueData']) !!},
    };
    
    // --- Bảng màu đẹp mắt ---
    const niceColors = [
        '#3b82f6', '#10b981', '#ef4444', '#f97316', '#8b5cf6',
        '#06b6d4', '#d946ef', '#f59e0b', '#6366f1', '#ec4899'
    ];

    /**
     * Hàm render biểu đồ chung và kiểm tra dữ liệu rỗng
     */
    function renderOrShowMessage(canvasId, chartInstance, chartConfig, data) {
        const canvas = document.getElementById(canvasId);
        const noDataEl = document.getElementById(canvasId + '-nodata');
        
        const hasData = data && data.data && data.data.length > 0 && data.data.reduce((a, b) => a + b, 0) > 0;

        if (canvas && noDataEl) {
            if (hasData) {
                noDataEl.style.display = 'none';
                canvas.style.display = 'block';
                if (chartInstance) {
                    chartInstance.destroy();
                }
                return new Chart(canvas.getContext('2d'), chartConfig);
            } else {
                canvas.style.display = 'none';
                noDataEl.style.display = 'block';
                return null;
            }
        }
    }

    // --- 1. BIỂU ĐỒ DOANH THU CHÍNH (DYNAMIC) ---
    const defaultLineOptions = {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { /* config */ } },
        scales: {
            y: { beginAtZero: true, ticks: { callback: value => new Intl.NumberFormat('vi-VN', { notation: 'compact' }).format(value) } },
            x: { grid: { display: false }, ticks: { autoSkip: true, maxRotation: 0, maxTicksLimit: 12 } }
        },
        interaction: { intersect: false, mode: 'index' },
    };

    const initMainRevenueChart = () => {
        const ctx = document.getElementById('mainRevenueChart').getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 350);
        gradient.addColorStop(0, 'rgba(59, 130, 246, 0.5)');
        gradient.addColorStop(1, 'rgba(59, 130, 246, 0)');
        
        mainRevenueChart = new Chart(ctx, {
            type: 'line', data: {
                labels: chartData.initialRevenueData.labels,
                datasets: [{ label: 'Doanh thu', data: chartData.initialRevenueData.data,
                    backgroundColor: gradient, borderColor: '#3b82f6', borderWidth: 2,
                    fill: true, tension: 0.4, pointBackgroundColor: '#3b82f6', pointRadius: 2,
                }]
            }, options: defaultLineOptions
        });
    };

    const updateRevenueChart = async (period) => {
        const loader = document.getElementById('revenue-chart-loader');
        loader.style.display = 'flex';
        try {
            const response = await fetch(`{{ route('admin.dashboard.revenueChartData') }}?period=${period}`);
            if (!response.ok) throw new Error('Network response was not ok');
            const newData = await response.json();
            mainRevenueChart.data.labels = newData.labels;
            mainRevenueChart.data.datasets[0].data = newData.data;
            mainRevenueChart.update();
        } catch (error) {
            console.error('Failed to fetch chart data:', error);
        } finally {
            loader.style.display = 'none';
        }
    };
    
    document.getElementById('revenuePeriodSelector').addEventListener('click', (e) => {
        if (e.target.tagName === 'BUTTON' && !e.target.classList.contains('active')) {
            e.currentTarget.querySelectorAll('button').forEach(btn => btn.classList.remove('active'));
            e.target.classList.add('active');
            updateRevenueChart(e.target.dataset.period);
        }
    });

    // --- 2. BIỂU ĐỒ TOP SẢN PHẨM (STATIC) ---
    
    // --- GỌI CÁC HÀM KHỞI TẠO ---
    initMainRevenueChart();
});
</script>
@endsection