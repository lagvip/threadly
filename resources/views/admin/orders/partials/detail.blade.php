<div>
    <h5 class="mb-3">Order #{{ $order->order_code }}</h5>

    <ul class="list-group mb-3">
        <li class="list-group-item"><strong>Customer:</strong> {{ $order->customer->full_name }}</li>
        <li class="list-group-item"><strong>Email:</strong> {{ $order->customer->email }}</li>
        <li class="list-group-item"><strong>Phone:</strong> {{ $order->customer->phone }}</li>
        <li class="list-group-item"><strong>Total:</strong> ${{ number_format($order->total_price, 2) }}</li>
        <li class="list-group-item"><strong>Status:</strong> {{ ucfirst($order->order_status) }}</li>
        <li class="list-group-item"><strong>Payment:</strong> {{ ucfirst($order->payment_status) }}</li>
        <li class="list-group-item"><strong>Created at:</strong> {{ $order->created_at->format('M d, Y H:i') }}</li>
    </ul>

    @if ($order->orderDetails && $order->orderDetails->count())
        <h6 class="fw-semibold">Order Items</h6>
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($order->orderDetails as $detail)
                    <tr>
                        <td>{{ $detail->product->name ?? 'N/A' }}</td>
                        <td>{{ $detail->quantity }}</td>
                        <td>${{ number_format($detail->price, 2) }}</td>
                        <td>${{ number_format($detail->price * $detail->quantity, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <p class="text-muted">No items found for this order.</p>
    @endif
</div>
