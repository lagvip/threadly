<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa đơn - {{ $order->order_code }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body class="p-4">
    <h3>HÓA ĐƠN ĐƠN HÀNG - {{ $order->order_code }}</h3>
    <p><strong>Khách hàng:</strong> {{ $order->user->first_name }}</p>
    {{-- <p><strong>Địa chỉ:</strong> {{ $order->user->address }}</p> --}}

    <table class="table table-bordered mt-3">
        <thead>
            <tr>
                <th>Sản phẩm</th>
                <th>Size</th>
                <th>Màu</th>
                <th>SL</th>
                <th>Đơn giá</th>
                <th>Thành tiền</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->orderDetails as $detail)
            <tr>
                <td>{{ $detail->variant->product->name }}</td>
                <td>{{ $detail->variant->size->name ?? '-' }}</td>
                <td>{{ $detail->variant->color->name ?? '-' }}</td>
                <td>{{ $detail->quantity }}</td>
                <td>{{ number_format($detail->unit_price, 0, ',', '.') }}₫</td>
                <td>{{ number_format($detail->total, 0, ',', '.') }}₫</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Tổng cộng:</strong> {{ number_format($order->total_price, 0, ',', '.') }}₫</p>
    <p><strong>Thanh toán:</strong> {{ $order->payment_status === 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}</p>

    <hr>
    <p class="text-muted">In lúc: {{ now()->format('d/m/Y H:i') }}</p>

    <script>
        window.onload = () => window.print();
    </script>
</body>
</html>
