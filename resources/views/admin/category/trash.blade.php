@extends('admin.layouts.layout')

@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">Thùng Rác Danh Mục</h4>

                        <div class="d-flex gap-2">
                            <a href="{{ route('listCategory.list') }}" class="btn btn-sm btn-secondary">
                                <i class="bx bx-arrow-back me-1"></i>Quay lại danh sách
                            </a>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th style="width: 20px;">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="checkAll">
                                            </div>
                                        </th>
                                        <th>Danh mục</th>
                                        <th>Danh mục cha</th>
                                        <th>Ngày xóa</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($category as $value)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="customCheck{{ $value->id }}">
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded bg-light avatar-md d-flex align-items-center justify-content-center overflow-hidden" style="width: 50px; height: 50px;">
                                                        @if($value->image)
                                                            <img src="{{ asset('storage/' . $value->image) }}" 
                                                                 alt="{{ $value->name }}" 
                                                                 class="img-fluid object-fit-cover" 
                                                                 style="width: 100%; height: 100%;">
                                                        @else
                                                            <i class="bx bx-image fs-24 text-muted"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <p class="text-dark fw-medium mb-0">{{ $value->name }}</p>
                                                        <small class="text-muted">ID: {{ $value->id }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-secondary fs-12">
                                                    {{ $value->parent ? $value->parent->name : 'Danh mục gốc' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-danger fw-medium">
                                                    {{ $value->deleted_at->format('d/m/Y H:i') }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    {{-- Nút Khôi phục --}}
                                                    <form action="{{ route('listCategory.restore', $value->id) }}" method="POST" style="display:inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-soft-success btn-sm" title="Khôi phục">
                                                            <iconify-icon icon="solar:restart-broken" class="align-middle fs-18 me-1"></iconify-icon>
                                                            Khôi phục
                                                        </button>
                                                    </form>

                                                    {{-- Form Xóa vĩnh viễn --}}
                                                    <form action="{{ route('listCategory.forceDelete', $value->id) }}" method="post" style="display:inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button onclick="return confirm('Bạn có chắc muốn xoá vĩnh viễn? Dữ liệu này sẽ không thể khôi phục!')" 
                                                                type="submit" class="btn btn-soft-danger btn-sm" title="Xóa vĩnh viễn">
                                                            <iconify-icon icon="solar:trash-bin-trash-broken" class="align-middle fs-18 me-1"></iconify-icon>
                                                            Xóa vĩnh viễn
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">Thùng rác hiện đang trống.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer border-top">
                        {{ $category->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
