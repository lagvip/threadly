@extends('client.account._layout')

@section('account_content')
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
            <div>
                <h4 class="mb-1">Tài khoản của tôi</h4>
                <p class="text-muted mb-0">Theo dõi nhanh tài khoản, đơn hàng và địa chỉ mặc định.</p>
            </div>

            <a href="{{ route('client.account.detail') }}" class="btn btn-outline-primary rounded-pill">
                Chỉnh sửa thông tin
            </a>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="p-3 rounded-4 bg-light h-100">
                    <div class="text-muted small mb-1">Tổng đơn hàng</div>
                    <div class="fs-4 fw-bold">{{ $stats['total_orders'] }}</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="p-3 rounded-4 bg-light h-100">
                    <div class="text-muted small mb-1">Đang xử lý</div>
                    <div class="fs-4 fw-bold">{{ $stats['pending_orders'] }}</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="p-3 rounded-4 bg-light h-100">
                    <div class="text-muted small mb-1">Đã giao</div>
                    <div class="fs-4 fw-bold">{{ $stats['delivered_orders'] }}</div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="p-3 rounded-4 bg-light h-100">
                    <div class="text-muted small mb-1">Đã hủy</div>
                    <div class="fs-4 fw-bold">{{ $stats['cancelled_orders'] }}</div>
                </div>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-md-6">
                <div class="border rounded-4 p-3 h-100">
                    <div class="fw-semibold mb-2">Thông tin cơ bản</div>
                    <div class="mb-2"><strong>Họ tên:</strong> {{ $user->name }}</div>
                    <div class="mb-2"><strong>Email:</strong> {{ $user->email }}</div>
                    <div class="mb-0">
                        <strong>SĐT mặc định:</strong>
                        {{ $defaultAddress->phone_number ?? 'Chưa có' }}
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="border rounded-4 p-3 h-100">
                    <div class="fw-semibold mb-2">Địa chỉ mặc định</div>
                    @if($defaultAddress)
                        <div class="mb-1"><strong>Người nhận:</strong> {{ $defaultAddress->recipient_name }}</div>
                        <div class="mb-1"><strong>SĐT:</strong> {{ $defaultAddress->phone_number }}</div>
                        <div class="mb-1">
                            <strong>Địa chỉ:</strong>
                            {{ $defaultAddress->detailed_address }},
                            {{ $defaultAddress->ward }},
                            {{ $defaultAddress->district }},
                            {{ $defaultAddress->province }}
                        </div>
                        <div class="mb-0"><strong>Loại:</strong> {{ $defaultAddress->address_type }}</div>
                    @else
                        <div class="text-muted">Bạn chưa có địa chỉ mặc định.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h5 class="mb-0">Đơn hàng gần đây</h5>
            <a href="{{ route('client.orders.index') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                Xem tất cả
            </a>
        </div>

        @if($recentOrders->isEmpty())
            <div class="text-muted">Bạn chưa có đơn hàng nào.</div>
        @else
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Mã đơn</th>
                            <th>Ngày tạo</th>
                            <th>Thanh toán</th>
                            <th>Đơn hàng</th>
                            <th>Tổng tiền</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentOrders as $order)
                            <tr>
                                <td class="fw-semibold">{{ $order->order_code }}</td>
                                <td>{{ optional($order->created_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <span class="badge bg-{{ $order->payment_status_badge }}">
                                        {{ $order->payment_status_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $order->order_status_badge }}">
                                        {{ $order->order_status_label }}
                                    </span>
                                </td>
                                <td class="fw-semibold">{{ number_format($order->total_price, 0, ',', '.') }} đ</td>
                                <td class="text-end">
                                    <a href="{{ route('client.orders.show', $order->id) }}"
                                       class="btn btn-sm btn-outline-dark rounded-pill">
                                        Chi tiết
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
