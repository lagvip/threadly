@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Chi tiết màu</h4>
        </div>
        <div class="card-body">
            <p><strong>ID:</strong> {{ $color->id }}</p>
            <p><strong>Tên:</strong> {{ $color->name }}</p>
            <p><strong>Mã màu:</strong> {{ $color->code }}</p>
            <p>
                <strong>Xem nhanh:</strong>
                <span class="d-inline-block rounded border align-middle ms-2" style="width: 28px; height: 28px; background: {{ $color->code }};"></span>
            </p>
            <p><strong>Ngày tạo:</strong> {{ optional($color->created_at)->format('d/m/Y H:i') }}</p>
            <p><strong>Ngày cập nhật:</strong> {{ optional($color->updated_at)->format('d/m/Y H:i') }}</p>

            <a href="{{ route('listColor.list') }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>
</div>
@endsection
