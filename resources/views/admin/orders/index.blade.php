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

        <form method="GET" action="{{ route('orders.index') }}" class="row g-2 mb-4 align-items-center">
            <div class="col-md-2">
                <input type="text" name="order_code" value="{{ request('order_code') }}" class="form-control" placeholder="Mã đơn hàng">
            </div>

            <div class="col-md-2">
                <input type="text" name="customer" value="{{ request('customer') }}" class="form-control" placeholder="Khách hàng">
            </div>

            <div class="col-md-2">
                <select name="payment_status" class="form-select">
                    <option value="">-- Trạng thái thanh toán --</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>Đã thanh toán</option>
                    <option value="unpaid" {{ request('payment_status') == 'unpaid' ? 'selected' : '' }}>Chưa thanh toán</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Đang chờ thanh toán</option>
                    <option value="failed" {{ request('payment_status') == 'failed' ? 'selected' : '' }}>Thanh toán lỗi</option>
                    <option value="cancelled" {{ request('payment_status') == 'cancelled' ? 'selected' : '' }}>Thanh toán đã hủy</option>
                    <option value="expired" {{ request('payment_status') == 'expired' ? 'selected' : '' }}>Thanh toán hết hạn</option>
                </select>
            </div>

            <div class="col-md-2">
                <select name="order_status" class="form-select">
                    <option value="">-- Trạng thái đơn hàng --</option>
                    <option value="pending" {{ request('order_status') == 'pending' ? 'selected' : '' }}>Chờ xử lý</option>
                    <option value="processing" {{ request('order_status') == 'processing' ? 'selected' : '' }}>Đang xử lý</option>
                    <option value="shipped" {{ request('order_status') == 'shipped' ? 'selected' : '' }}>Đang giao hàng</option>
                    <option value="delivered" {{ request('order_status') == 'delivered' ? 'selected' : '' }}>Đã giao</option>
                    <option value="cancelled" {{ request('order_status') == 'cancelled' ? 'selected' : '' }}>Đã hủy</option>
                    <option value="waiting_for_cancellation" {{ request('order_status') == 'waiting_for_cancellation' ? 'selected' : '' }}>Chờ duyệt hủy</option>
                </select>
            </div>

            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">Tìm kiếm</button>
                <a href="{{ route('orders.index') }}" class="btn btn-outline-secondary">Đặt lại</a>
            </div>
        </form>

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
                        @forelse ($orders as $order)
                            @php
                                $paymentLabels = [
                                    'paid' => ['label' => 'Đã thanh toán', 'color' => 'bg-success'],
                                    'unpaid' => ['label' => 'Chưa thanh toán', 'color' => 'bg-secondary'],
                                    'pending' => ['label' => 'Đang chờ thanh toán', 'color' => 'bg-warning text-dark'],
                                    'failed' => ['label' => 'Thanh toán thất bại', 'color' => 'bg-danger'],
                                    'cancelled' => ['label' => 'Thanh toán đã hủy', 'color' => 'bg-dark'],
                                    'expired' => ['label' => 'Thanh toán hết hạn', 'color' => 'bg-secondary'],
                                ];

                                $paymentInfo = $paymentLabels[$order->payment_status] ?? ['label' => ucfirst($order->payment_status), 'color' => 'bg-light text-dark'];

                                $statusLabels = [
                                    'pending' => ['label' => 'Chờ xử lý', 'color' => 'bg-warning text-dark'],
                                    'processing' => ['label' => 'Đang xử lý', 'color' => 'bg-info'],
                                    'shipped' => ['label' => 'Đang giao hàng', 'color' => 'bg-primary'],
                                    'delivered' => ['label' => 'Đã giao', 'color' => 'bg-success'],
                                    'cancelled' => ['label' => 'Đã hủy', 'color' => 'bg-danger'],
                                    'waiting_for_cancellation' => ['label' => 'Chờ duyệt hủy', 'color' => 'bg-dark'],
                                ];

                                $statusInfo = $statusLabels[$order->order_status] ?? ['label' => ucfirst($order->order_status), 'color' => 'bg-light text-dark'];
                            @endphp

                            <tr class="text-center">
                                <td>#{{ $order->order_code }}</td>
                                <td>{{ $order->created_at->format('d/m/Y') }}</td>
                                <td class="text-danger fw-semibold">{{ $order->user->email ?? $order->email ?? 'N/A' }}</td>
                                <td class="fw-medium">{{ number_format($order->total_price, 0, ',', '.') }}₫</td>

                                <td>
                                    <span class="badge rounded-pill px-3 py-2 {{ $paymentInfo['color'] }}">
                                        {{ $paymentInfo['label'] }}
                                    </span>
                                </td>

                                <td>
                                    <span class="badge rounded-pill px-3 py-2 {{ $statusInfo['color'] }}">
                                        {{ $statusInfo['label'] }}
                                    </span>
                                </td>

                                <td>
                                    <a href="{{ route('orders.show', $order->id) }}" class="btn btn-sm btn-outline-primary me-1" title="Xem chi tiết">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>

                                    @if (!in_array($order->order_status, ['delivered', 'cancelled'], true))
                                        <button
                                            class="btn btn-sm btn-outline-warning me-1 btn-edit-order"
                                            data-id="{{ $order->id }}"
                                            data-status="{{ $order->order_status }}"
                                            data-previous-status="{{ $order->previous_status }}"
                                            data-payment-method="{{ $order->payment_method }}"
                                            data-payment-status="{{ $order->payment_status }}"
                                            title="Chỉnh sửa">
                                            <i class="bi bi-pencil-fill"></i>
                                        </button>
                                    @endif

                                    @if ($order->order_status === 'cancelled')
                                        <button onclick="showDeleteModal({{ $order->id }})" class="btn btn-sm btn-outline-danger" title="Xóa">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Không có đơn hàng nào</td>
                            </tr>
                        @endforelse
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
                const orderId = this.dataset.id;
                const orderStatus = this.dataset.status;
                const previousStatus = this.dataset.previousStatus || 'processing';
                const paymentMethod = this.dataset.paymentMethod;
                const paymentStatus = this.dataset.paymentStatus;
                const isPaidVnpay = paymentMethod === 'vnpay' && paymentStatus === 'paid';
                const form = document.getElementById('formEditOrder');
                const select = document.getElementById('orderStatusSelect');
                const note = document.getElementById('orderStatusNote');
                const statusHelp = document.getElementById('statusHelp');

                form.action = '/orders/' + orderId + '/update-status';
                note.value = '';

                // reset options
                Array.from(select.options).forEach(option => option.hidden = false);
                statusHelp.innerText = '';

                if (isPaidVnpay) {
                    const cancelOption = Array.from(select.options).find(option => option.value === 'cancelled');
                    if (cancelOption) {
                        cancelOption.hidden = true;
                    }
                }

                if (orderStatus === 'waiting_for_cancellation') {
                    // dữ liệu cũ: nếu là VNPay đã thanh toán thì chỉ được trả về trạng thái trước đó
                    Array.from(select.options).forEach(option => option.hidden = true);

                    const allowed = isPaidVnpay ? [previousStatus] : ['cancelled', previousStatus];
                    Array.from(select.options).forEach(option => {
                        if (allowed.includes(option.value)) {
                            option.hidden = false;
                        }
                    });

                    select.value = isPaidVnpay ? previousStatus : 'cancelled';
                    statusHelp.innerText = isPaidVnpay
                        ? 'Đơn VNPay đã thanh toán không thể hủy vì hệ thống không hỗ trợ hoàn tiền. Bạn chỉ có thể trả đơn về trạng thái trước đó.'
                        : 'Đơn đang chờ duyệt hủy. Bạn có thể duyệt hủy hoặc trả về trạng thái trước đó.';
                } else {
                    select.value = orderStatus;

                    if (isPaidVnpay) {
                        statusHelp.innerText = 'Đơn VNPay đã thanh toán không thể chuyển sang trạng thái đã hủy vì hệ thống không hỗ trợ hoàn tiền.';
                    }
                }

                new bootstrap.Modal(document.getElementById('modalEditOrder')).show();
            });
        });

        function showDeleteModal(orderId) {
            const form = document.getElementById('formDeleteOrder');
            form.action = '/orders/' + orderId;
            new bootstrap.Modal(document.getElementById('modalDeleteOrder')).show();
        }
    </script>
    @endpush

    <div class="modal fade" id="modalEditOrder" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formEditOrder" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Cập nhật trạng thái đơn hàng</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    <div class="modal-body">
                        <select id="orderStatusSelect" name="order_status" class="form-select mb-2" required>
                            <option value="pending">Chờ xử lý</option>
                            <option value="processing">Đang xử lý</option>
                            <option value="shipped">Đang giao hàng</option>
                            <option value="delivered">Đã giao</option>
                            <option value="cancelled">Đã hủy</option>
                        </select>

                        <small id="statusHelp" class="text-muted d-block mb-3"></small>

                        <label for="orderStatusNote" class="form-label">Ghi chú (không bắt buộc)</label>
                        <textarea id="orderStatusNote" name="note" rows="3" class="form-control" placeholder="Nhập ghi chú nếu cần..."></textarea>
                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Cập nhật</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

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
