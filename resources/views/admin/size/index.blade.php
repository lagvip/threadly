@extends('admin.layouts.layout')

@section('content')

<a href="{{ route('listSize.addSize') }}" class="btn btn-primary mb-3">
    Thêm Size
</a>

@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<table class="table table-bordered">
    <tr>
        <th>ID</th>
        <th>Tên Size</th>
        <th>Ngày tạo</th>
        <th>Hành động</th>
    </tr>

    @foreach($sizes as $size)
    <tr>
        <td>{{ $size->id }}</td>
        <td>{{ $size->name }}</td>
        <td>{{ $size->created_at }}</td>
        <td>
            <a href="{{ route('listSize.editSize', $size->id) }}"
               class="btn btn-warning btn-sm">Sửa</a>

            <form action="{{ route('listSize.deleteSize', $size->id) }}"
                  method="POST"
                  style="display:inline-block;">
                @csrf
                @method('DELETE')
                <button class="btn btn-danger btn-sm"
                        onclick="return confirm('Xóa?')">
                    Xóa
                </button>
            </form>
        </td>
    </tr>
    @endforeach
</table>

{{ $sizes->links() }}

@endsection
