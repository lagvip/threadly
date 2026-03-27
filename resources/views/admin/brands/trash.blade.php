@extends('admin.layouts.layout')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Thùng rác Thương hiệu</h2>
    
    <a href="{{ route('brands.index') }}" class="btn btn-secondary mb-3">Quay lại danh sách</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>ID</th>
                <th>Ảnh</th>
                <th>Tên thương hiệu</th>
                <th>Ngày xóa</th>
                <th>Hành động</th>
            </tr>
        </thead>
        <tbody>
            @forelse($trashedBrands as $brand)
                <tr>
                    <td>{{ $brand->id }}</td>
                    <td>
                        <img src="{{ asset('storage/' . $brand->image) }}" width="50">
                    </td>
                    <td>{{ $brand->name }}</td>
                    <td>{{ $brand->deleted_at->format('d/m/Y H:i') }}</td>
                    <td>
                        <a href="{{ route('brands.restore', $brand->id) }}" class="btn btn-success btn-sm">Khôi phục</a>
                        
                        <form action="{{ route('brands.forceDelete', $brand->id) }}" method="POST" style="display:inline">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Bạn có chắc chắn muốn xóa vĩnh viễn?')">Xóa vĩnh viễn</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center">Thùng rác trống.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection