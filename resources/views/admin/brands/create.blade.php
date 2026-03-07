@extends('admin.layouts.layout')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Thêm thương hiệu</h2>
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form method="POST" action="{{ route('brands.store') }}">
        @csrf

        <div class="mb-3">
            <label for="name" class="form-label">Tên thương hiệu</label>
            <input type="text" name="name" id="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Tạo mới</button>
        <a href="{{ route('brands.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>
@endsection




