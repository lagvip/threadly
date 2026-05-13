@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Chi tiết role</h4>
        </div>
        <div class="card-body">
            <p><strong>ID:</strong> {{ $role->id }}</p>
            <p><strong>Tên:</strong> {{ $role->name }}</p>
            <p><strong>Đường dẫn:</strong> {{ $role->slug }}</p>

            <a href="{{ route('roles.list') }}" class="btn btn-secondary">Quay lại</a>
        </div>
    </div>
</div>
@endsection
