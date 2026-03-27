@extends('admin.layouts.layout')

@section('content')
<div class="p-6">
    <h2 class="text-2xl font-semibold mb-4">Chi tiết đơn hàng - {{ $order->order_code }}</h2>

    {{-- Progress đơn hàng --}}
    <div class="mb-6">
        <div class="flex items-center text-sm text-gray-700 mb-1 space-x-3">
            <span class="font-medium {{ $order->progress >= 1 ? 'text-green-600' : '' }}">Đã xác nhận</span>
            <span>→</span>
            <span class="font-medium {{ $order->progress >= 2 ? 'text-green-600' : '' }}">Thanh toán</span>
            <span>→</span>
            <span class="font-medium {{ $order->progress >= 3 ? 'text-yellow-600' : '' }}">Đang đóng gói</span>
            <span>→</span>
            <span class="{{ $order->progress >= 4 ? 'text-blue-600' : '' }}">Giao hàng</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-2">
            <div class="h-2 rounded-full transition-all duration-300"
                 style="width: {{ $order->progress * 25 }}%; background-color:
                 {{ $order->progress == 1 ? '#10B981' : ($order->progress == 2 ? '#10B981' : ($order->progress == 3 ? '#F59E0B' : '#3B82F6')) }}">
            </div>
        </div>
    </div>

    {{-- Layout 2 cột --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        {{-- Sản phẩm --}}
        <div class="md:col-span-2 bg-white p-4 rounded shadow">
            <h3 class="text-lg font-bold mb-3">Danh sách sản phẩm</h3>
            <table class="w-full text-sm">
                <thead class="text-left border-b">
                    <tr>
                        <th class="py-2">Sản phẩm</th>
                        <th>Kích cỡ</th>
                        <th>Màu</th>
                        <th>SL</th>
                        <th>Đơn giá</th>
                        <th>Thành tiền</th>
                        <th>Trạng thái</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->items as $item)
                    <tr class="border-t">
                        <td class="py-2 flex items-center gap-2">
                            @if ($item->product->thumbnail)
                                <img src="{{ asset('storage/' . $item->product->thumbnail) }}" alt="Ảnh" class="w-10 h-10 rounded object-cover">
                            @endif
                            {{ $item->product->name }}
                        </td>
                        <td>{{ $item->size }}</td>
                        <td>{{ $item->color }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ number_format($item->price, 0, ',', '.') }}₫</td>
                        <td>{{ number_format($item->price * $item->quantity, 0, ',', '.') }}₫</td>
                        <td>
                            <span class="px-2 py-1 text-xs rounded 
                                {{ $item->status == 'ready' ? 'bg-green-100 text-green-600' : 'bg-gray-100 text-gray-600' }}">
                                {{ $item->status == 'ready' ? 'Sẵn sàng' : 'Đóng gói' }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Tóm tắt + khách hàng --}}
        <div class="bg-white p-4 rounded shadow">
            <h3 class="text-lg font-bold mb-2">Tóm tắt đơn</h3>
            <ul class="text-sm text-gray-800 space-y-1">
                <li><strong>Tạm tính:</strong> {{ number_format($order->total_price, 0, ',', '.') }}₫</li>
                <li><strong>Giảm giá:</strong> {{ number_format($order->discount ?? 0, 0, ',', '.') }}₫</li>
                <li><strong>Phí giao hàng:</strong> 0₫</li>
                <li class="text-base mt-2"><strong>Tổng cộng:</strong> 
                    <span class="text-blue-600 font-semibold">
                        {{ number_format($order->total_price - ($order->discount ?? 0), 0, ',', '.') }}₫
                    </span>
                </li>
                <li><strong>Thanh toán:</strong>
                    <span class="text-green-600 font-medium">
                        {{ $order->payment_status == 'paid' ? 'Đã thanh toán' : 'Chưa thanh toán' }}
                    </span>
                </li>
                <li><strong>Trạng thái đơn:</strong>
                    @switch($order->order_status)
                        @case('packaging') Đang đóng gói @break
                        @case('completed') Hoàn tất @break
                        @case('canceled') Đã hủy @break
                        @default Nháp
                    @endswitch
                </li>
                <li><strong>Ngày tạo:</strong> {{ $order->created_at->format('d/m/Y H:i') }}</li>
            </ul>

            {{-- Thông tin khách --}}
            <div class="mt-6">
                <h4 class="font-semibold mb-1">Khách hàng</h4>
                <p class="text-sm text-gray-800">{{ $order->customer->name }}</p>
                <p class="text-sm text-gray-600">{{ $order->customer->email }}</p>
                <p class="text-sm text-gray-600">{{ $order->customer->phone }}</p>
                <p class="text-sm text-gray-600">{{ $order->customer->address }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
