@extends('admin.layouts.layout')

@section('content')

<h4>Thêm Size</h4>

<form action="{{ route('listSize.storeSize') }}" method="POST">
    @csrf

    <div class="mb-3">
        <label>Tên Size</label>

        <input type="text" 
               name="name" 
               value="{{ old('name') }}"
               class="form-control">

        @error('name')
            <small class="text-danger">{{ $message }}</small>
        @enderror
    </div>

    <button class="btn btn-success">Lưu</button>

    <a href="{{ route('listSize.list') }}" class="btn btn-secondary">
        Quay lại
    </a>

</form>

@endsection
