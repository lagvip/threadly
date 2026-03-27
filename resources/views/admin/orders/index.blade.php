@extends('admin.layouts.layout')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="container py-4">
        <h3 class="fs-4 fw-semibold mb-4">Tổng quan đơn hàng</h3>

        {{-- Phần thống kê đơn hàng --}}
        <div class="row g-4 mb-5">
            @php
                $statuses = [
                    ['count' => $orderCancel, 'label' => 'Đã hủy', 'icon' => 'cart-x', 'color' => 'danger'],
                    ['count' => $orderDelivering, 'label' => 'Đang giao', 'icon' => 'truck', 'color' => 'info'],
                    ['count' => $pendingPayment, 'label' => 'Chờ thanh toán', 'icon' => 'clock', 'color' => 'warning'],
                    ['count' => $orderDelivered, 'label' => 'Đã giao', 'icon' => 'box-seam', 'color' => 'success'],
                ];
            @endphp
            @foreach ($statuses as $item)
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 d-flex flex-row align-items-center justify-content-between">
                        <div>
                            <div class="fs-3 fw-bold">{{ $item['count'] }}</div>
                            <small class="text-muted">{{ $item['label'] }}</small>
                        </div>
                        <div class="bg-light p-3 rounded-circle">
                            <i class="bi bi-{{ $item['icon'] }} fs-4 text-{{ $item['color'] }}"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Tìm kiếm và lọc đơn hàng -->
<form method="GET" action="{{ route('orders.index') }}" class="row g-2 mb-4 align-items-center">
    <div class="col-md-2">
        <input type="text" name="order_code" value="{{ request('order_code') }}" class="form-control" placeholder="Mã đơn hàng">
    </div>
    <div class="col-md-2">
        <input type="text" name="customer" value="{{ request('customer') }}" class="form-control" placeholder="Khách hàng">
    </div>
    <div class="col-md-2">
        <select name="payment_status" class="form-control">
            <option value="">-- Trạng thái thanh toán --</option>
            <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
            <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
            <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Thanh toán lỗi</option>
        </select>
    </div>
    <div class="col-md-2">
        <select name="order_status" class="form-control">
            <option value="">-- Trạng thái đơn hàng --</option>
            <option value="pending" {{ request('order_status') == 'pending' ? 'selected' : '' }}>Đang chờ</option>
            <option value="processing" {{ request('order_status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
            <option value="shipped" {{ request('order_status') == 'shipped' ? 'selected' : '' }}>Đã vận chuyển</option>
            <option value="delivered" {{ request('order_status') == 'delivered' ? 'selected' : '' }}>Đã giao</option>
            <option value="cancelled" {{ request('order_status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
            <option value="waiting_for_cancellation" {{ request('order_status') == 'waiting_for_cancellation' ? 'selected' : '' }}>Xin huỷ đơn hàng</option>
        </select>
    </div>

    <!-- 2 nút nằm cạnh nhau trên cùng một hàng -->
    <div class="col-md-4 d-flex gap-2">
        <button type="submit" class="btn btn-primary flex-fill">Tìm kiếm</button>

    </div>
</form>


        {{-- Bảng đơn hàng --}}
        <div class="card border-0 shadow-sm rounded-4">
            <div class="table-responsive p-3">
                <table class="table table-hover table-striped align-middle text-nowrap rounded">
                    <thead class="table-light text-uppercase text-center small">
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày tạo</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>TT thanh toán</th>
                            <th>Trạng thái đơn</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr class="text-center">
                                <td>#{{ $order->order_code }}</td>
                                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                <td class="text-danger fw-semibold">{{ $order->user->email }}</td>
                                <td class="fw-medium">{{ number_format($order->total_price, 0, ',', '.') }}₫</td>

                                <td>
                                    @php
                                        $badgeClass = match ($order->payment_status) {
                                            'paid' => 'bg-success',
                                            'unpaid' => 'bg-secondary',
                                            default => 'bg-light text-dark',
                                        };
                                    @endphp
                                    <span class="badge rounded-pill px-3 py-2 {{ $badgeClass }}">
                                     {{
                                        ucfirst($order->payment_status) == 'Paid' ? 'Đã thanh toán' :
                                        (ucfirst($order->payment_status) == 'Unpaid' ? 'Chưa thanh toán' : 'Thanh toán thất bại')
                                    }}

                                    </span>
                                </td>

                                <td>
                                    @php
                                        $status = ucfirst($order->order_status);
                                        $statusLabels = [
                                            'Pending' => ['label' => 'Đang chờ', 'color' => 'bg-warning'],
                                            'Processing' => ['label' => 'Đang xử lý', 'color' => 'bg-info'],
                                            'Shipped' => ['label' => 'Đã vận chuyển', 'color' => 'bg-primary'],
                                            'Delivered' => ['label' => 'Đã giao', 'color' => 'bg-success'],
                                            'Cancelled' => ['label' => 'Đã huỷ', 'color' => 'bg-danger'],
                                            'Waiting_for_cancellation' => ['label' => 'Xin huỷ đơn hàng', 'color' => 'bg-dark'],

                                        ];
                                    @endphp

                                    @if(isset($statusLabels[$status]))
                                        <span class="badge rounded-pill px-3 py-2 {{ $statusLabels[$status]['color'] }}">
                                            {{ $statusLabels[$status]['label'] }}
                                        </span>
                                    @else
                                        <span class="badge rounded-pill px-3 py-2 bg-light text-dark">
                                            {{ $status }}
                                        </span>
                                    @endif
                                </td>

                                <td>
                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Xem chi tiết">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <button
                                        class="btn btn-sm btn-outline-warning me-1 btn-edit-order"
                                        data-id="{{ $order->id }}"
                                        data-status="{{ $order->order_status }}"
                                        title="Chỉnh sửa">
                                        <i class="bi bi-pencil-fill"></i>
                                    </button>

                                    <button onclick="showDeleteModal({{ $order->id }})" class="btn btn-sm btn-outline-danger" title="Xóa">
                                        <i class="bi bi-trash-fill"></i>
                                    </button>

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{ $orders->withQueryString()->links() }}
    </div>

   @push('scripts')
<script>
    document.querySelectorAll('.btn-edit-order').forEach(button => {
        button.addEventListener('click', function () {
            let orderId = this.dataset.id;
            let orderStatus = this.dataset.status;

            // Cập nhật action của form
            document.getElementById('formEditOrder').action = '/orders/' + orderId + '/update-status';

            // Set trạng thái hiện tại
            document.getElementById('orderStatusSelect').value = orderStatus;

            // Mở modal
            new bootstrap.Modal(document.getElementById('modalEditOrder')).show();
        });
    });
</script>
@endpush


   {{-- Modal: Chỉnh sửa trạng thái đơn hàng --}}
<div class="modal fade" id="modalEditOrder" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <form id="formEditOrder" method="POST">
            @csrf
            @method('POST')
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Cập nhật trạng thái đơn hàng</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <select id="orderStatusSelect" name="order_status" class="form-select" required>
                        <option value="pending">Đang chờ</option>
                        <option value="processing">Đang xử lý</option>
                        <option value="shipped">Đã vận chuyển</option>
                        <option value="delivered">Đã giao</option>
                        <option value="cancelled">Đã huỷ</option>
                    </select>

                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Cập nhật</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                </div>
            </div>
        </form>
    </div>
</div>


    {{-- Modal: Xóa đơn hàng --}}
    <div class="modal fade" id="modalDeleteOrder" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formDeleteOrder" method="POST">
                @csrf
                @method('DELETE')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Xóa đơn hàng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Bạn có chắc chắn muốn xóa đơn hàng này không?</p>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-danger">Xóa</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
<script>
function showDeleteModal(orderId) {
    let form = document.getElementById('formDeleteOrder');
    form.action = '/orders/' + orderId;
    new bootstrap.Modal(document.getElementById('modalDeleteOrder')).show();
}
</script>
