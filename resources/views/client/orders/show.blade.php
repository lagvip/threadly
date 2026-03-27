@extends('client.account._layout')

@section('account_content')
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
            <div>
                <h4 class="mb-1">Chi tiết đơn hàng</h4>
                <div class="text-muted">Mã đơn: <strong>{{ $order->order_code }}</strong></div>
            </div>

            <div class="text-end">
                <div class="mb-1">
                    <span class="badge bg-{{ $order->payment_status_badge }}">
                        {{ $order->payment_status_label }}
                    </span>
                </div>
                <div>
                    <span class="badge bg-{{ $order->order_status_badge }}">
                        {{ $order->order_status_label }}
                    </span>
                </div>
            </div>
        </div>

        <hr>

        <div class="row g-3">
            <div class="col-md-6">
                <div><strong>Người nhận:</strong> {{ $order->name }}</div>
                <div><strong>Số điện thoại:</strong> {{ $order->phone }}</div>
                <div><strong>Email:</strong> {{ $order->email ?: 'Không có' }}</div>
                <div><strong>Ngày tạo:</strong> {{ optional($order->created_at)->format('d/m/Y H:i') }}</div>
            </div>

            <div class="col-md-6">
                <div><strong>Thanh toán:</strong> {{ strtoupper($order->payment_method) }}</div>
                <div><strong>Địa chỉ:</strong> {{ $order->address }}</div>

                @if($order->cancel_reason)
                    <div class="mt-2 text-danger">
                        <strong>Lý do hủy:</strong> {{ $order->cancel_reason }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-4">
        <h5 class="mb-3">Sản phẩm trong đơn</h5>

        @foreach($order->details as $item)
            @php
                $variantImage = $item->variant->image ?? null;
                $productImage = optional($item->product)->image_primary;
                $image = $variantImage ?: $productImage;
                $imageUrl = $image ? asset('storage/' . $image) : null;
            @endphp

            <div class="d-flex gap-3 py-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                <div style="width:84px;flex:0 0 84px;">
                    @if($imageUrl)
                        <img src="{{ $imageUrl }}"
                             alt="{{ $item->product_name }}"
                             class="img-fluid rounded-3 border"
                             style="width:84px;height:84px;object-fit:cover;">
                    @else
                        <div class="rounded-3 border bg-light d-flex align-items-center justify-content-center"
                             style="width:84px;height:84px;">
                            <span class="text-muted small">No image</span>
                        </div>
                    @endif
                </div>

                <div class="flex-grow-1">
                    <div class="fw-semibold">{{ $item->product_name }}</div>

                    <div class="text-muted small">
                        @if(optional($item->variant)->color?->name)
                            Màu: {{ $item->variant->color->name }}
                        @endif

                        @if(optional($item->variant)->size?->name)
                            @if(optional($item->variant)->color?->name) | @endif
                            Size: {{ $item->variant->size->name }}
                        @endif
                    </div>

                    <div class="small text-muted mt-1">
                        SL: {{ $item->quantity }} × {{ number_format($item->unit_price, 0, ',', '.') }} đ
                    </div>
                </div>

                <div class="fw-bold text-end">
                    {{ number_format($item->total, 0, ',', '.') }} đ
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <h5 class="mb-3">Tổng kết</h5>

        <div class="d-flex justify-content-between mb-2">
            <span>Phí ship</span>
            <strong>{{ number_format($order->shipping_fee, 0, ',', '.') }} đ</strong>
        </div>

        <div class="d-flex justify-content-between mb-2">
            <span>Giảm giá</span>
            <strong>- {{ number_format($order->discount, 0, ',', '.') }} đ</strong>
        </div>

        <div class="d-flex justify-content-between fs-5 mt-3 pt-3 border-top">
            <span class="fw-bold">Tổng thanh toán</span>
            <span class="fw-bold text-danger">{{ number_format($order->total_price, 0, ',', '.') }} đ</span>
        </div>

        <div class="mt-4 d-flex flex-wrap gap-2">
            <a href="{{ route('client.orders.index') }}" class="btn btn-outline-dark rounded-pill">
                Quay lại danh sách
            </a>

            <form action="{{ route('client.orders.reorder', $order->id) }}" method="POST">
                @csrf
                <button type="submit" class="btn btn-outline-primary rounded-pill">
                    Mua lại
                </button>
            </form>

            @if($order->can_repay)
                <form action="{{ route('client.orders.repay-vnpay', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-warning rounded-pill">
                        Thanh toán lại
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection
