<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn - {{ $order->order_code }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h3>HÓA ĐƠN ĐƠN HÀNG - {{ $order->order_code }}</h3>
    <p><strong>Khách hàng:</strong> {{ $order->name }}</p>
    <p><strong>Email:</strong> {{ $order->email }}</p>
    <p><strong>Số điện thoại:</strong> {{ $order->phone }}</p>
    <p><strong>Địa chỉ:</strong> {{ $order->address }}</p>

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Kích cỡ</th>
                <th>Màu</th>
                <th>SL</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($order->details as $detail)
                <tr>
                    <td>{{ $detail->variant?->product?->name ?? $detail->product_name }}</td>
                    <td>{{ $detail->variant?->size?->name ?? '-' }}</td>
                    <td>{{ $detail->variant?->color?->name ?? '-' }}</td>
                    <td>{{ $detail->quantity }}</td>
                    <td>{{ number_format($detail->unit_price, 0, ',', '.') }}₫</td>
                    <td>{{ number_format($detail->total, 0, ',', '.') }}₫</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center">Không có sản phẩm</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <p><strong>Phí vận chuyển:</strong> {{ number_format($order->shipping_fee, 0, ',', '.') }}₫</p>
    <p><strong>Giảm giá:</strong> {{ number_format($order->discount, 0, ',', '.') }}₫</p>
    <p><strong>Tổng cộng:</strong> {{ number_format($order->total_price, 0, ',', '.') }}₫</p>
    <p><strong>Thanh toán:</strong> {{ $order->payment_status_label }}</p>

    <hr>
    <p class="text-muted">In lúc: {{ now()->format('d/m/Y H:i') }}</p>

    <script>
        window.onload = () => window.print();
    </script>
</body>
</html>
