@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Phiếu nhập kho</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.inventory.movements.index') }}" class="btn btn-light btn-sm">Lịch sử kho</a>
                <a href="{{ route('admin.inventory.receipts.create') }}" class="btn btn-primary btn-sm">Tạo phiếu nhập</a>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.inventory.receipts.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text" name="keyword" class="form-control" value="{{ $filters['keyword'] ?? '' }}" placeholder="Mã phiếu hoặc người tạo">
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">Tất cả trạng thái</option>
                        @foreach(['draft' => 'Nháp', 'posted' => 'Đã xác nhận', 'cancelled' => 'Đã hủy'] as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['status'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
                </div>
                <div class="col-md-auto">
                    <a href="{{ route('admin.inventory.receipts.index') }}" class="btn btn-secondary btn-sm">Bỏ lọc</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover table-centered">
                    <thead class="bg-light-subtle">
                        <tr>
                            <th>Mã phiếu</th>
                            <th>Trạng thái</th>
                            <th>Người tạo</th>
                            <th>Số dòng</th>
                            <th>Tổng SL</th>
                            <th>Tổng tiền nhập</th>
                            <th>Ngày tạo</th>
                            <th>Ngày xác nhận</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receipts as $receipt)
                            <tr>
                                <td>{{ $receipt->receipt_code }}</td>
                                <td>
                                    @php
                                        $badge = ['draft' => 'warning', 'posted' => 'success', 'cancelled' => 'secondary'][$receipt->status] ?? 'secondary';
                                        $label = ['draft' => 'Nháp', 'posted' => 'Đã xác nhận', 'cancelled' => 'Đã hủy'][$receipt->status] ?? $receipt->status;
                                    @endphp
                                    <span class="badge bg-{{ $badge }}">{{ $label }}</span>
                                </td>
                                <td>{{ $receipt->creator?->name ?? '-' }}</td>
                                <td>{{ $receipt->items_count }}</td>
                                <td>{{ number_format((int) ($receipt->total_quantity ?? 0), 0, ',', '.') }}</td>
                                <td>{{ number_format((float) ($receipt->total_cost ?? 0), 0, ',', '.') }} đ</td>
                                <td>{{ $receipt->created_at?->format('d/m/Y H:i') }}</td>
                                <td>{{ $receipt->posted_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                <td>
                                    <a href="{{ route('admin.inventory.receipts.show', $receipt->id) }}" class="btn btn-light btn-sm">
                                        <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center">Chưa có phiếu nhập</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-3">
                {{ $receipts->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
