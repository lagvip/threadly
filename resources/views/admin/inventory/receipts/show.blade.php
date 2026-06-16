@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Chi tiết phiếu nhập {{ $receipt->receipt_code }}</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.inventory.receipts.index') }}" class="btn btn-light btn-sm">Danh sách</a>
                @if($canPostReceipt)
                    <form method="POST" action="{{ route('admin.inventory.receipts.post', $receipt->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-primary btn-sm" onclick="return confirm('Xác nhận nhập kho và cộng tồn?')">Xác nhận</button>
                    </form>
                    <form method="POST" action="{{ route('admin.inventory.receipts.cancel', $receipt->id) }}">
                        @csrf
                        <button type="submit" class="btn btn-soft-danger btn-sm" onclick="return confirm('Hủy phiếu nhập này?')">Hủy</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="text-muted">Trạng thái</div>
                    <strong>{{ $receiptStatusLabels[$receipt->status] ?? $receipt->status }}</strong>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">Người tạo</div>
                    <strong>{{ $receipt->creator?->name ?? '-' }}</strong>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">Ngày tạo</div>
                    <strong>{{ $receipt->created_at?->format('d/m/Y H:i') }}</strong>
                </div>
                <div class="col-md-3">
                    <div class="text-muted">Ngày xác nhận</div>
                    <strong>{{ $receipt->posted_at?->format('d/m/Y H:i') ?? '-' }}</strong>
                </div>
                <div class="col-12">
                    <div class="text-muted">Ghi chú</div>
                    <div>{{ $receipt->note ?? '-' }}</div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover table-centered">
                    <thead class="bg-light-subtle">
                        <tr>
                            <th>Biến thể</th>
                            <th>SL nhập</th>
                            <th>Giá nhập</th>
                            <th>Thành tiền</th>
                            <th>Tồn trước</th>
                            <th>Tồn sau</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receipt->items as $item)
                            <tr>
                                <td>
                                    #{{ $item->variant?->id }}
                                    - {{ $item->variant?->product?->name ?? 'N/A' }}
                                    | Màu: {{ $item->variant?->color?->name ?? '-' }}
                                    | Size: {{ $item->variant?->size?->name ?? '-' }}
                                </td>
                                <td>{{ number_format((int) $item->quantity, 0, ',', '.') }}</td>
                                <td>{{ $item->unit_cost !== null ? number_format((float) $item->unit_cost, 0, ',', '.') . ' đ' : '-' }}</td>
                                <td>{{ $item->unit_cost !== null ? number_format((float) $item->unit_cost * (int) $item->quantity, 0, ',', '.') . ' đ' : '-' }}</td>
                                <td>{{ $item->stock_before !== null ? number_format((int) $item->stock_before, 0, ',', '.') : '-' }}</td>
                                <td>{{ $item->stock_after !== null ? number_format((int) $item->stock_after, 0, ',', '.') : '-' }}</td>
                                <td>{{ $item->note ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="3" class="text-end">Tổng tiền nhập</th>
                            <th>
                                {{ number_format($receipt->items->sum(fn ($item) => (float) ($item->unit_cost ?? 0) * (int) $item->quantity), 0, ',', '.') }} đ
                            </th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
