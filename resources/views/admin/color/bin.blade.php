@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Thùng rác màu</h4>
            <a href="{{ route('listColor.list') }}" class="btn btn-secondary btn-sm">Quay lại danh sách</a>
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

            <form action="{{ route('listColor.search') }}" method="GET" class="row g-2 mb-3">
                <input type="hidden" name="from" value="trash">

                <div class="col-md-6">
                    <input type="text" name="keyword" class="form-control"
                           value="{{ $keyword ?? '' }}" placeholder="Tìm màu trong thùng rác...">
                </div>

                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Tìm kiếm</button>
                </div>

                <div class="col-md-auto">
                    <a href="{{ route('listColor.bin') }}" class="btn btn-secondary btn-sm">Bỏ lọc</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên màu</th>
                            <th>Mã màu</th>
                            <th>Ngày xoá</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($colors as $color)
                            <tr>
                                <td>{{ $color->id }}</td>
                                <td>{{ $color->name }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="d-inline-block rounded border" style="width: 22px; height: 22px; background: {{ $color->code }};"></span>
                                        <span>{{ $color->code }}</span>
                                    </div>
                                </td>
                                <td>{{ optional($color->deleted_at)->format('d/m/Y H:i') }}</td>
                                <td class="d-flex gap-2">
                                    <form action="{{ route('listColor.restore', $color->id) }}" method="POST"
                                          onsubmit="return confirm('Khôi phục màu này?')">
                                        @csrf
                                        <button type="submit" class="btn btn-success btn-sm">
                                            Khôi phục
                                        </button>
                                    </form>

                                    <form action="{{ route('listColor.forceDelete', $color->id) }}" method="POST"
                                          onsubmit="return confirm('Xóa vĩnh viễn màu này?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm">
                                            Xóa vĩnh viễn
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">Không có màu nào trong thùng rác</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $colors->links() }}
        </div>
    </div>
</div>
@endsection
