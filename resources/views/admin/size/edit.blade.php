@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h4>Sửa size</h4>
        </div>
        <div class="card-body">
            @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif

            <form action="{{ route('listSize.updateSize', $size->id) }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label>Tên size</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $size->name) }}">
                    @error('name')
                        <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary">Cập nhật</button>
                <a href="{{ route('listSize.list') }}" class="btn btn-secondary">Quay lại</a>
            </form>
        </div>
    </div>
</div>
@endsection
