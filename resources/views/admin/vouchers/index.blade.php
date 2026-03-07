@extends('admin.layouts.layout')

@section('content')
<div class="container">
    <h2>Quản lý Voucher</h2>

    <a href="{{ route('vouchers.create') }}" class="btn btn-primary mb-3">
        Thêm Voucher
    </a>

    <!-- Search Form -->
    <div class="card mb-3">
        <div class="card-body">
            <form action="{{ route('vouchers.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label for="search" class="form-label">Tìm kiếm theo mã</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="Nhập mã voucher..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label for="type" class="form-label">Loại voucher</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">-- Tất cả --</option>
                        <option value="percent" {{ request('type') == 'percent' ? 'selected' : '' }}>Giảm %</option>
                        <option value="fixed" {{ request('type') == 'fixed' ? 'selected' : '' }}>Trừ tiền</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="status" class="form-label">Trạng thái</label>
                    <select class="form-select" id="status" name="status">
                        <option value="">-- Tất cả --</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Tắt</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Hết hạn</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-info w-100">Tìm kiếm</button>
                    <a href="{{ route('vouchers.index') }}" class="btn btn-secondary">Xóa bộ lọc</a>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-bordered">
        <thead>
        <tr>
            <th>Mã</th>
            <th>Loại</th>
            <th>Giá trị</th>
            <th>Giảm tối đa</th>
            <th>Thời gian bắt đầu - Kết thúc</th>
            <th>Số lượt</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
        </tr>
        </thead>
        <tbody>
        @if($vouchers->count() == 0)
            <tr>
                <td colspan="8" class="text-center text-muted py-4">
                    <em>Không tìm thấy voucher nào</em>
                </td>
            </tr>
        @else
        @foreach($vouchers as $v)
        <tr>
            <td><strong>{{ $v->code }}</strong></td>
            <td>
                @if($v->type == 'percent')
                    <span class="badge bg-info">Giảm {{ $v->value }}%</span>
                @else
                    <span class="badge bg-warning">Trừ {{ number_format($v->value, 0, ',', '.') }}đ</span>
                @endif
            </td>
            <td>
                @if($v->type == 'percent')
                    {{ $v->value }}%
                @else
                    {{ number_format($v->value, 0, ',', '.') }}đ
                @endif
            </td>
            <td>
                @if($v->max_discount && $v->type == 'percent')
                    {{ number_format($v->max_discount, 0, ',', '.') }}đ
                @else
                    <em class="text-muted">-</em>
                @endif
            </td>
            <td>
                <small>
                    {{ $v->start_date->format('d/m/Y H:i') }} <br>
                    <strong>→</strong> {{ $v->end_date->format('d/m/Y H:i') }}
                </small>
            </td>
            <td>{{ $v->quantity }}</td>
            <td>
                @if($v->actual_status == 'active')
                    <span class="badge bg-success">Hoạt động</span>
                @elseif($v->actual_status == 'inactive')
                    <span class="badge bg-warning">Tắt</span>
                @else
                    <span class="badge bg-danger">Hết hạn</span>
                @endif
            </td>
            <td>
                <a href="{{ route('vouchers.edit',$v) }}" class="btn btn-sm btn-warning">Sửa</a>

                <form action="{{ route('vouchers.destroy',$v) }}" method="POST" style="display:inline">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Bạn có chắc chắn muốn xóa voucher này không?')">Xóa</button>
                </form>
            </td>
        </tr>
        @endforeach
        @endif
        </tbody>
    </table>

    <!-- Pagination Info -->
    <div class="d-flex justify-content-between align-items-center mt-3 mb-3">
        <div class="text-muted">
            Hiển thị {{ $vouchers->count() }} / {{ $vouchers->total() }} voucher
        </div>
        <div>
            {{ $vouchers->links() }}
        </div>
    </div>
</div>
@endsection