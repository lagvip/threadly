@extends('admin.layouts.layout')

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="container py-4">
        <div class="card mb-4 p-3">
            <h5><strong>Đơn hàng:</strong> {{ $order->order_code }}</h5>
            <p class="mb-1">Ngày tạo: {{ $order->created_at->format('d/m/Y H:i') }}</p>

            @php
                $statusLabels = [
                    'pending' => 'Chờ xử lý',
                    'processing' => 'Đang xử lý',
                    'shipped' => 'Đang giao hàng',
                    'delivered' => 'Đã giao',
                    'cancelled' => 'Đã hủy',
                    'waiting_for_cancellation' => 'Chờ duyệt hủy',
                ];

                $paymentLabels = [
                    'paid' => 'Đã thanh toán',
                    'unpaid' => 'Chưa thanh toán',
                    'pending' => 'Đang chờ thanh toán',
                    'failed' => 'Thanh toán thất bại',
                    'cancelled' => 'Thanh toán đã hủy',
                    'expired' => 'Thanh toán hết hạn',
                ];
            @endphp

            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-primary">
                    {{ $order->payment_method === 'cod' ? 'Thanh toán khi nhận hàng' : strtoupper($order->payment_method) }}
                </span>

                <span class="badge bg-success">
                    {{ $paymentLabels[$order->payment_status] ?? ucfirst($order->payment_status) }}
                </span>

                <span class="badge bg-warning text-dark">
                    {{ $statusLabels[$order->order_status] ?? ucfirst($order->order_status) }}
                </span>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header fw-bold">Sản phẩm</div>
                    <div class="card-body">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Sản phẩm</th>
                                    <th>Số lượng</th>
                                    <th>Giá</th>
                                    <th>Thành tiền</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($order->details as $item)
                                    @php
                                        $product = $item->variant?->product;
                                        $size = $item->variant?->size?->name ?? '-';
                                        $color = $item->variant?->color?->name ?? '-';
                                        $image = $product?->image_primary ? asset('storage/' . $product->image_primary) : 'https://via.placeholder.com/50x50?text=No+Image';
                                    @endphp

                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $image }}"
                                                    alt="{{ $product?->name ?? $item->product_name }}"
                                                    class="rounded" style="width:50px;height:50px;object-fit:cover">
                                                <div>
                                                    <div class="fw-bold">{{ $product?->name ?? $item->product_name }}</div>
                                                    <small class="text-muted d-block">Size: {{ $size }}</small>
                                                    <small class="text-muted d-block">Color: {{ $color }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $item->quantity }}</td>
                                        <td>{{ number_format($item->unit_price, 0, ',', '.') }} VNĐ</td>
                                        <td>{{ number_format($item->total, 0, ',', '.') }} VNĐ</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">Không có sản phẩm</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card mt-3 shadow-sm">
                    <div class="card-body">
                        @php
                            $subtotal = $order->details->sum(fn($d) => (float) $d->total);
                            $discount = (float) ($order->discount ?? 0);
                            $shippingFee = (float) ($order->shipping_fee ?? 0);
                        @endphp

                        <p><strong>Tổng tiền hàng:</strong> {{ number_format($subtotal, 0, ',', '.') }} VNĐ</p>
                        <p><strong>Phí vận chuyển:</strong> {{ number_format($shippingFee, 0, ',', '.') }} VNĐ</p>
                        <p><strong>Giảm giá:</strong> {{ number_format($discount, 0, ',', '.') }} VNĐ</p>
                        <h5 class="mt-3 text-primary fw-bold">
                            Tổng thanh toán: {{ number_format($order->total_price, 0, ',', '.') }} VNĐ
                        </h5>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">Thông tin người nhận</div>
                    <div class="card-body">
                        <p><strong>Tên người nhận:</strong> {{ $order->name }}</p>
                        <p><strong>Email:</strong> {{ $order->email }}</p>
                        <p><strong>SĐT:</strong> {{ $order->phone }}</p>
                        <p><strong>Địa chỉ:</strong> {{ $order->address }}</p>
                    </div>
                </div>

                <div class="card shadow-sm mb-3">
                    <div class="card-header fw-bold">Lý do hủy đơn</div>
                    <div class="card-body">
                        <span class="badge bg-secondary">
                            {{ $order->cancel_reason ?: 'Không có lý do hủy đơn' }}
                        </span>
                    </div>
                </div>

                <div class="card shadow-sm">
                    <div class="card-header fw-bold">Voucher áp dụng</div>
                    <div class="card-body">
                        @php
                            $appliedDiscount = (float) ($order->discount ?? 0);
                            $voucherTypeLabel = [
                                'percent' => 'Giảm theo phần trăm',
                                'fixed' => 'Giảm số tiền cố định',
                            ];
                        @endphp

                        @if ($order->voucher_code)
                            <p class="mb-2">
                                <strong>Mã voucher:</strong>
                                <span class="badge bg-success">{{ $order->voucher_code }}</span>
                            </p>

                            <p class="mb-2">
                                <strong>Số tiền đã giảm:</strong>
                                {{ number_format($appliedDiscount, 0, ',', '.') }} VNĐ
                            </p>

                            @if ($order->voucher)
                                <p class="mb-2">
                                    <strong>Loại voucher:</strong>
                                    {{ $voucherTypeLabel[$order->voucher->type] ?? $order->voucher->type }}
                                </p>

                                <p class="mb-0">
                                    <strong>Giá trị voucher:</strong>
                                    @if ($order->voucher->type === 'percent')
                                        {{ rtrim(rtrim(number_format((float) $order->voucher->value, 2, '.', ''), '0'), '.') }}%
                                    @else
                                        {{ number_format((float) $order->voucher->value, 0, ',', '.') }} VNĐ
                                    @endif
                                </p>
                            @endif
                        @else
                            <p class="mb-0 text-muted">Đơn hàng không áp dụng voucher.</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
