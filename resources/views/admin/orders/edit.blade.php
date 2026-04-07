@extends('admin.layouts.layout')

@section('content')
<div class="container py-4">
    <h4 class="mb-4">
        Cập nhật trạng thái đơn hàng
        <span class="text-primary">#{{ $order->order_code }}</span>
    </h4>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @elseif (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <form method="POST" action="{{ route('orders.updateStatus', $order->id) }}">
        @csrf

        <div class="mb-3">
            <label for="order_status" class="form-label">Trạng thái đơn hàng</label>
            <select name="order_status" id="order_status" class="form-select" required>
                @foreach (\App\Enums\OrderStatus::editableStatuses() as $status)
                    <option value="{{ $status }}" {{ $order->order_status === $status ? 'selected' : '' }}>
                        {{ \App\Enums\OrderStatus::from($status)->label() }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="note" class="form-label">Ghi chú (không bắt buộc)</label>
            <textarea name="note" id="note" rows="3" class="form-control" placeholder="Nhập ghi chú nếu cần..."></textarea>
        </div>

        <button type="submit" class="btn btn-primary">Cập nhật</button>
        <a href="{{ route('orders.index') }}" class="btn btn-secondary">Hủy</a>
    </form>
</div>
@endsection
