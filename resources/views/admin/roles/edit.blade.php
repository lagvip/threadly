@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Sửa role</h4>
        </div>
        <div class="card-body">
            <form action="{{ route('roles.update', $role->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Tên role</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $role->name) }}">
                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="mb-3">
                    <label>Đường dẫn</label>
                    <input type="text" name="slug" class="form-control" value="{{ old('slug', $role->slug) }}">
                    @error('slug')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="{{ route('roles.list') }}" class="btn btn-secondary">Quay lại</a>
            </form>
        </div>
    </div>
</div>
@endsection
