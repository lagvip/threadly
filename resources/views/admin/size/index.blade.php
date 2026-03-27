@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Danh sách size</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('listSize.addSize') }}" class="btn btn-primary btn-sm">Thêm size</a>
                <a href="{{ route('listSize.trash') }}" class="btn btn-soft-danger btn-sm">Thùng rác</a>
            </div>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning">{{ session('warning') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <form action="{{ route('listSize.searchSize') }}" method="GET" class="row g-2 mb-3">
                <input type="hidden" name="from" value="list">

                <div class="col-md-6">
                    <input type="text" name="keyword" class="form-control"
                           value="{{ $keyword ?? '' }}" placeholder="Nhập tên size cần tìm...">
                </div>

                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Tìm kiếm</button>
                </div>

                <div class="col-md-auto">
                    <a href="{{ route('listSize.list') }}" class="btn btn-secondary btn-sm">Bỏ lọc</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover table-centered">
                    <thead class="bg-light-subtle">
                        <tr>
                            <th>ID</th>
                            <th>Tên size</th>
                            <th>Ngày tạo</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sizes as $size)
                            <tr>
                                <td>{{ $size->id }}</td>
                                <td>{{ $size->name }}</td>
                                <td>{{ optional($size->created_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('listSize.detailSize', $size->id) }}" class="btn btn-light btn-sm">
                                            <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                                        </a>

                                        <a href="{{ route('listSize.editSize', $size->id) }}" class="btn btn-soft-primary btn-sm">
                                            <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon>
                                        </a>

                                        <form action="{{ route('listSize.deleteSize', $size->id) }}" method="POST"
                                              onsubmit="return confirm('Chuyển size này vào thùng rác?')" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-soft-danger btn-sm">
                                                <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center">Không có dữ liệu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $sizes->links() }}
        </div>
    </div>
</div>
@endsection
