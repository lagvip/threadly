@extends('admin.layouts.layout')

@section('content')
<div class="container py-4">
    <h3 class="fs-4 fw-semibold mb-4">Đơn hàng đã xóa</h3>

    <form action="{{ route('deleted.restore') }}" method="POST" id="bulkRestoreForm">
        @csrf

        <div class="d-flex gap-2 mb-3">
            <button type="submit" class="btn btn-success">Khôi phục đã chọn</button>
        </div>

        <div class="card border-0 shadow-sm rounded-4">
            <div class="table-responsive p-3">
                <table class="table table-hover table-striped align-middle text-nowrap rounded">
                    <thead class="table-light text-uppercase text-center small">
                        <tr>
                            <th><input type="checkbox" id="checkAll"></th>
                            <th>Mã đơn</th>
                            <th>Khách hàng</th>
                            <th>Tổng tiền</th>
                            <th>Ngày xóa</th>
                            <th>Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            <tr class="text-center">
                                <td><input type="checkbox" name="ids[]" value="{{ $order->id }}"></td>
                                <td>#{{ $order->order_code }}</td>
                                <td>{{ $order->user->email ?? $order->email ?? 'Không có' }}</td>
                                <td>{{ number_format($order->total_price, 0, ',', '.') }}₫</td>
                                <td>{{ $order->deleted_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    <button type="submit"
                                            name="ids[]"
                                            value="{{ $order->id }}"
                                            class="btn btn-success btn-sm">
                                        Khôi phục
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Không có đơn hàng đã xóa</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </form>

    <form action="{{ route('deleted.forceDelete') }}" method="POST" id="bulkForceDeleteForm" class="mt-3">
        @csrf
        <div class="d-flex gap-2">
            <button type="submit" class="btn btn-danger">Xóa vĩnh viễn đã chọn</button>
        </div>
    </form>

    <form action="{{ route('deleted.forceDelete') }}" method="POST" id="singleForceDeleteForm" class="d-none">
        @csrf
        <input type="hidden" name="ids[]" id="singleForceDeleteId">
    </form>
</div>

@push('scripts')
<script>
    const checkAll = document.getElementById('checkAll');

    checkAll?.addEventListener('change', function(e) {
        document.querySelectorAll('#bulkRestoreForm input[name="ids[]"]').forEach(cb => {
            cb.checked = e.target.checked;
        });
    });

    document.getElementById('bulkForceDeleteForm')?.addEventListener('submit', function(e) {
        e.preventDefault();

        const checked = Array.from(document.querySelectorAll('#bulkRestoreForm input[name="ids[]"]:checked'))
            .map(cb => cb.value);

        if (checked.length === 0) {
            alert('Vui lòng chọn ít nhất một đơn hàng để xóa vĩnh viễn.');
            return;
        }

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('deleted.forceDelete') }}";

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = "{{ csrf_token() }}";
        form.appendChild(csrf);

        checked.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    });
</script>
@endpush
@endsection
