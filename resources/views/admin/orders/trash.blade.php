@extends('admin.layouts.layout')

@section('content')
<div class="container py-4">
    <h3 class="fs-4 fw-semibold mb-4">Đơn hàng đã xoá</h3>

    {{-- Bulk restore form --}}
    <form action="{{ route('deleted.restore') }}" method="POST" id="bulkRestoreForm">
        @csrf
        <div class="card border-0 shadow-sm rounded-4">
            <div class="table-responsive p-3">
                <table class="table table-hover table-striped align-middle text-nowrap rounded">
                    <thead class="table-light text-uppercase text-center small">
                        <tr>
                            <th><input type="checkbox" id="checkAll"></th>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Ngày xoá</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                        <tr class="text-center">
                            <td><input type="checkbox" name="ids[]" value="{{ $order->id }}"></td>
                            <td>#{{ $order->order_code }}</td>
                            <td>{{ $order->user->email ?? 'N/A' }}</td>
                            <td>{{ number_format($order->total_price, 0, ',', '.') }}₫</td>
                            <td>{{ $order->deleted_at }}</td>
                            <td>
                                {{-- Khôi phục đơn hàng --}}
                                <form action="{{ route('deleted.restore') }}" method="POST" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="ids[]" value="{{ $order->id }}">
                                    <button type="submit" class="btn btn-success btn-sm">Khôi phục</button>
                                </form>

                                {{-- Xoá vĩnh viễn --}}
                                <form action="{{ route('deleted.forceDelete') }}" method="POST" style="display:inline">
                                    @csrf
                                    <input type="hidden" name="ids[]" value="{{ $order->id }}">
                                    <button type="submit" class="btn btn-danger btn-sm">Xoá vĩnh viễn</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Không có đơn hàng đã xoá</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>
</div>

{{-- Check all JS --}}
@push('scripts')
<script>
    document.getElementById('checkAll').addEventListener('change', function(e) {
        document.querySelectorAll('input[name="ids[]"]').forEach(cb => cb.checked = e.target.checked);
    });
</script>
@endpush
@endsection
