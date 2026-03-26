@extends('admin.layouts.layout')

@section('content')
    <div class="container-xxl">
        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">Danh Sách Danh Mục</h4>

                        <div class="d-flex gap-2">
                            <a href="{{ route('listCategory.addCategory') }}" class="btn btn-sm btn-primary">
                                <i class="bx bx-plus me-1"></i>Thêm mới danh mục
                            </a>

                            <a href="{{ route('listCategory.trash') }}" class="btn btn-soft-danger btn-sm">
                                <iconify-icon icon="solar:trash-bin-minimalistic-broken" class="align-middle me-1"></iconify-icon>
                                Thùng rác
                            </a>
                        </div>

                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle btn btn-sm btn-outline-light" data-bs-toggle="dropdown" aria-expanded="false">
                                Thao tác nhanh
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#!" class="dropdown-item">Tải xuống (PDF)</a>
                                <a href="#!" class="dropdown-item">Xuất Excel</a>
                                <a href="#!" class="dropdown-item">Nhập dữ liệu</a>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-0"> {{-- Sửa lỗi: Thêm card-body và đóng thẻ đúng chỗ --}}
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th style="width: 20px;">
                                            <div class="form-check">
                                                <input type="checkbox" class="form-check-input" id="customCheck1">
                                                <label class="form-check-label" for="customCheck1"></label>
                                            </div>
                                        </th>
                                        <th>Danh mục</th>
                                        <th>Danh mục cha</th>
                                        <th>Hành động</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($category as $key => $value)
                                        <tr>
                                            <td>
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" id="customCheck{{ $value->id }}">
                                                    <label class="form-check-label" for="customCheck{{ $value->id }}"></label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="rounded bg-light avatar-md d-flex align-items-center justify-content-center overflow-hidden">
                                                        @if($value->image)
                                                            <img src="{{ asset('storage/' . $value->image) }}" alt="" class="avatar-md object-fit-cover">
                                                        @else
                                                            <i class="bx bx-image fs-24 text-muted"></i>
                                                        @endif
                                                    </div>
                                                    <p class="text-dark fw-medium fs-15 mb-0">{{ $value->name }}</p>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-secondary fs-12">
                                                    {{ $value->parent ? $value->parent->name : 'Danh mục gốc' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('listCategory.detailCategory', $value->id) }}"
                                                        class="btn btn-light btn-sm" title="Xem chi tiết">
                                                        <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                                                    </a>

                                                    <a href="{{ route('listCategory.editCategory', $value->id) }}"
                                                        class="btn btn-soft-primary btn-sm" title="Chỉnh sửa">
                                                        <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon>
                                                    </a>

                                                    <form action="{{ route('listCategory.deleteCategory', $value->id) }}" method="post">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button onclick="return confirm('Bạn có muốn chuyển danh mục này vào thùng rác?')"
                                                            type="submit" class="btn btn-soft-danger btn-sm" title="Xoá">
                                                            <iconify-icon icon="solar:trash-bin-minimalistic-2-broken" class="align-middle fs-18"></iconify-icon>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">Không có dữ liệu danh mục.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div> {{-- Đóng thẻ card-body đã bị thiếu --}}

                    <div class="card-footer border-top">
                        {{ $category->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection