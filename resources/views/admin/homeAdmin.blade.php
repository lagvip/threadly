@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <h3 class="fw-bold text-uppercase mb-4">📊 Dashboard Thống Kê</h3>

    <div class="row">
        <div class="col-lg-3 col-md-6 mb-4">
            <div class="card p-3 d-flex flex-row align-items-center shadow-sm h-100">
                <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                    <i class="fas fa-sack-dollar text-success fs-4"></i>
                </div>
                <div>
                    <div class="text-muted small">Tổng Doanh Thu</div>
                    <div class="fw-bold fs-5">125,450,000 đ</div>
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
                    <div class="fw-bold fs-5">342</div>
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
                    <div class="fw-bold fs-5">156</div>
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
                    <div class="fw-bold fs-5">2,847</div>
                </div>
            </div>
        </div>
    </div>

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
                                <tr>
                                    <td>Áo thun nam cao cấp</td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill">245</span>
                                    </td>
                                    <td class="text-end">24,500,000 đ</td>
                                </tr>
                                <tr>
                                    <td>Quần jean nữ skinny</td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill">198</span>
                                    </td>
                                    <td class="text-end">19,800,000 đ</td>
                                </tr>
                                <tr>
                                    <td>Giày thể thao nam</td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill">176</span>
                                    </td>
                                    <td class="text-end">17,600,000 đ</td>
                                </tr>
                                <tr>
                                    <td>Túi xách nữ da thật</td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill">154</span>
                                    </td>
                                    <td class="text-end">15,400,000 đ</td>
                                </tr>
                                <tr>
                                    <td>Đồng hồ nam thời trang</td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill">132</span>
                                    </td>
                                    <td class="text-end">13,200,000 đ</td>
                                </tr>
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
                                <tr>
                                    <td>Thời trang nam</td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill">456</span>
                                    </td>
                                    <td class="text-end fw-medium">45,600,000 đ</td>
                                </tr>
                                <tr>
                                    <td>Thời trang nữ</td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill">389</span>
                                    </td>
                                    <td class="text-end fw-medium">38,900,000 đ</td>
                                </tr>
                                <tr>
                                    <td>Giày dép</td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill">267</span>
                                    </td>
                                    <td class="text-end fw-medium">26,700,000 đ</td>
                                </tr>
                                <tr>
                                    <td>Phụ kiện</td>
                                    <td class="text-center">
                                        <span class="badge bg-info rounded-pill">198</span>
                                    </td>
                                    <td class="text-end fw-medium">19,800,000 đ</td>
                                </tr>
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
                                <tr>
                                    <td>Áo khoác denim vintage</td>
                                    <td class="text-center">
                                        <span class="badge bg-danger fw-bold">3</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Giày sneaker trắng</td>
                                    <td class="text-center">
                                        <span class="badge bg-danger fw-bold">5</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Túi đeo chéo mini</td>
                                    <td class="text-center">
                                        <span class="badge bg-danger fw-bold">2</span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.card {
    margin: 0 !important;
}
.table th, .table td {
    vertical-align: middle;
}
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    let mainRevenueChart;

    const chartData30Days = {
        labels: ['Ngày 1', 'Ngày 2', 'Ngày 3', 'Ngày 4', 'Ngày 5', 'Ngày 6', 'Ngày 7', 'Ngày 8', 'Ngày 9', 'Ngày 10',
                 'Ngày 11', 'Ngày 12', 'Ngày 13', 'Ngày 14', 'Ngày 15', 'Ngày 16', 'Ngày 17', 'Ngày 18', 'Ngày 19', 'Ngày 20',
                 'Ngày 21', 'Ngày 22', 'Ngày 23', 'Ngày 24', 'Ngày 25', 'Ngày 26', 'Ngày 27', 'Ngày 28', 'Ngày 29', 'Ngày 30'],
        data: [3200000, 4100000, 3800000, 4500000, 5200000, 4800000, 3900000, 4200000, 4700000, 5100000,
               4300000, 3700000, 4900000, 5300000, 4600000, 3500000, 4400000, 5000000, 4100000, 3800000,
               4800000, 5200000, 4500000, 3900000, 4300000, 5100000, 4700000, 4000000, 4600000, 5400000]
    };

    const chartData7Days = {
        labels: ['Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7', 'CN'],
        data: [4200000, 4500000, 5100000, 4800000, 5300000, 4900000, 3800000]
    };

    const chartData12Months = {
        labels: ['T1', 'T2', 'T3', 'T4', 'T5', 'T6', 'T7', 'T8', 'T9', 'T10', 'T11', 'T12'],
        data: [95000000, 102000000, 118000000, 125000000, 135000000, 128000000, 142000000, 138000000, 145000000, 152000000, 148000000, 156000000]
    };

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

    document.getElementById('revenuePeriodSelector').addEventListener('click', (e) => {
        if (e.target.tagName === 'BUTTON' && !e.target.classList.contains('active')) {
            e.currentTarget.querySelectorAll('button').forEach(btn => btn.classList.remove('active'));
            e.target.classList.add('active');
            
            const period = e.target.dataset.period;
            let selectedData;
            
            if (period === '7d') {
                selectedData = chartData7Days;
            } else if (period === '30d') {
                selectedData = chartData30Days;
            } else if (period === '12m') {
                selectedData = chartData12Months;
            }
            
            initMainRevenueChart(selectedData);
        }
    });

    initMainRevenueChart(chartData30Days);
});
</script>
@endsection
