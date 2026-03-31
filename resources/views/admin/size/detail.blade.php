@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Chi tiết size</h4>
        </div>
        <div class="card-body">
            <p><strong>ID:</strong> {{ $size->id }}</p>
            <p><strong>Tên:</strong> {{ $size->name }}</p>
            <p><strong>Ngày tạo:</strong> {{ optional($size->created_at)->format('d/m/Y H:i') }}</p>
            <p><strong>Ngày cập nhật:</strong> {{ optional($size->updated_at)->format('d/m/Y H:i') }}</p>

            <a href="{{ route('listSize.list') }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>
</div>
@endsection
