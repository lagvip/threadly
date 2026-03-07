@extends('admin.layouts.layout')

@section('content')
<div class="container-xxl">
    <div class="card">
        <div class="card-header">
            <h4 class="card-title mb-0">Chi tiết User</h4>
        </div>

        <div class="card-body">
            <p><b>ID:</b> {{ $user->id }}</p>
            <p><b>Tên:</b> {{ $user->name }}</p>
            <p><b>Email:</b> {{ $user->email }}</p>
            <p><b>Role:</b>
                @if($user->roles->count())
                    {{ $user->roles->pluck('name')->join(', ') }}
                @else
                    <span class="text-muted">Chưa có role</span>
                @endif
            </p>
            <p><b>Ngày tạo:</b> {{ $user->created_at }}</p>
        </div>

        <div class="card-footer">
            <a href="{{ route('users.list') }}" class="btn btn-primary">Quay lại</a>
        </div>
    </div>
</div>
@endsection
