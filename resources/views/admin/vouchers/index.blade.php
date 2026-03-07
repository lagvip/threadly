@extends('admin.layouts.layout')

@section('content')
<div class="container">
    <h2>Quản lý Voucher</h2>

    <a href="{{ route('vouchers.create') }}" class="btn btn-primary mb-3">
        Thêm Voucher
    </a>

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
                    <button class="btn btn-sm btn-danger">Xóa</button>
                </form>
            </td>
        </tr>
        @endforeach
        </tbody>
    </table>
</div>
@endsection