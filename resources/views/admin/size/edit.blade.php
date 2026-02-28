@extends('admin.layouts.layout')

@section('content')

<h4>Sửa Size</h4>

<form action="{{ route('listSize.updateSize', $size->id) }}" method="POST">
    @csrf
    @method('PUT')

    <div class="mb-3">
        <label>Tên Size</label>
        <input type="text" name="name" 
               value="{{ $size->name }}" 
               class="form-control">
    </div>

    <button class="btn btn-success">Cập nhật</button>
</form>

@endsection
