@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <h4 class="card-title mb-0">Lịch sử kho</h4>
            <a href="{{ route('admin.inventory.receipts.index') }}" class="btn btn-light btn-sm">Phiếu nhập kho</a>
        </div>

        <div class="card-body">
            <form method="GET" action="{{ route('admin.inventory.movements.index') }}" class="row g-2 mb-3">
                <div class="col-md-5">
                    <input type="text"
                           name="keyword"
                           class="form-control"
                           value="{{ $filters['keyword'] ?? '' }}"
                           placeholder="Tìm theo sản phẩm, màu, size hoặc mã biến thể">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">Tất cả loại</option>
                        @foreach($movementTypeLabels as $value => $label)
                            <option value="{{ $value }}" {{ ($filters['type'] ?? '') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Lọc</button>
                </div>
                <div class="col-md-auto">
                    <a href="{{ route('admin.inventory.movements.index') }}" class="btn btn-secondary btn-sm">Bỏ lọc</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover table-centered">
                    <thead class="bg-light-subtle">
                        <tr>
                            <th>Thời gian</th>
                            <th>Biến thể</th>
                            <th>Loại</th>
                            <th>Thay đổi</th>
                            <th>Tồn trước</th>
                            <th>Tồn sau</th>
                            <th>Giá nhập</th>
                            <th>Thành tiền</th>
                            <th>Người tạo</th>
                            <th>Ghi chú</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $movement)
                            @php
                                $unitCost = $movement->unit_cost
                                    ?? ($movement->type === $importMovementType ? $movement->receipt_unit_cost : null);
                            @endphp
                            <tr>
                                <td>{{ $movement->created_at?->format('d/m/Y H:i') }}</td>
                                <td>
                                    #{{ $movement->variant?->id }}
                                    - {{ $movement->variant?->product?->name ?? 'N/A' }}
                                    | {{ $movement->variant?->color?->name ?? '-' }}
                                    | {{ $movement->variant?->size?->name ?? '-' }}
                                </td>
                                <td>{{ $movementTypeLabels[$movement->type] ?? $movement->type }}</td>
                                <td class="{{ $movement->quantity_change >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ $movement->quantity_change > 0 ? '+' : '' }}{{ number_format((int) $movement->quantity_change, 0, ',', '.') }}
                                </td>
                                <td>{{ number_format((int) $movement->stock_before, 0, ',', '.') }}</td>
                                <td>{{ number_format((int) $movement->stock_after, 0, ',', '.') }}</td>
                                <td>{{ $unitCost !== null ? number_format((float) $unitCost, 0, ',', '.') . ' đ' : '-' }}</td>
                                <td>{{ $unitCost !== null ? number_format(abs((int) $movement->quantity_change) * (float) $unitCost, 0, ',', '.') . ' đ' : '-' }}</td>
                                <td>{{ $movement->creator?->name ?? '-' }}</td>
                                <td>{{ $movement->note ?? '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center">Chưa có biến động kho</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="pt-3">
                {{ $movements->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
