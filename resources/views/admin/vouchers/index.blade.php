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
            <th>Thời gian</th>
            <th>Số lượt</th>
            <th>Trạng thái</th>
            <th>Hành động</th>
        </tr>
        </thead>
        <tbody>
        @foreach($vouchers as $v)
        <tr>
            <td>{{ $v->code }}</td>
            <td>{{ $v->type == 'percent' ? '%' : 'Trừ tiền' }}</td>
            <td>{{ $v->value }}</td>
            <td>
                {{ $v->start_date }} <br>
                {{ $v->end_date }}
            </td>
            <td>{{ $v->quantity }}</td>
            <td>
                {{ $v->status ? 'Hoạt động' : 'Tắt' }}
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
