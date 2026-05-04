@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Danh sách màu</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('listColor.add') }}" class="btn btn-primary btn-sm">Thêm màu</a>
                <a href="{{ route('listColor.bin') }}" class="btn btn-soft-danger btn-sm">Thùng rác</a>
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

            <form action="{{ route('listColor.search') }}" method="GET" class="row g-2 mb-3">
                <input type="hidden" name="from" value="list">

                <div class="col-md-6">
                    <input type="text" name="keyword" class="form-control"
                           value="{{ $keyword ?? '' }}" placeholder="Nhập tên màu hoặc mã màu cần tìm...">
                </div>

                <div class="col-md-auto">
                    <button type="submit" class="btn btn-primary btn-sm">Tìm kiếm</button>
                </div>

                <div class="col-md-auto">
                    <a href="{{ route('listColor.list') }}" class="btn btn-secondary btn-sm">Bỏ lọc</a>
                </div>
            </form>

            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover table-centered">
                    <thead class="bg-light-subtle">
                        <tr>
                            <th>ID</th>
                            <th>Tên màu</th>
                            <th>Mã màu</th>
                            <th>Xem nhanh</th>
                            <th>Ngày tạo</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($colors as $color)
                            <tr>
                                <td>{{ $color->id }}</td>
                                <td>{{ $color->name }}</td>
                                <td>{{ $color->code }}</td>
                                <td>
                                    <span class="d-inline-block rounded border" style="width: 28px; height: 28px; background: {{ $color->code }};"></span>
                                </td>
                                <td>{{ optional($color->created_at)->format('d/m/Y H:i') }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('listColor.detail', $color->id) }}" class="btn btn-light btn-sm">
                                            <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                                        </a>

                                        <a href="{{ route('listColor.edit', $color->id) }}" class="btn btn-soft-primary btn-sm">
                                            <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon>
                                        </a>

                                        <form action="{{ route('listColor.delete', $color->id) }}" method="POST"
                                              onsubmit="return confirm('Chuyển màu này vào thùng rác?')" class="m-0">
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
                                <td colspan="6" class="text-center">Không có dữ liệu</td>
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
