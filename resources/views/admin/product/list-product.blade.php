@extends('admin.layouts.layout')
@section('content')
    <!-- Start Container Fluid -->
    <div class="container-fluid">

        <div class="row">
            <div class="col-xl-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center gap-1">
                        <h4 class="card-title flex-grow-1">Danh sách sản phẩm</h4>

                        <a href="{{ route('product.create') }}" class="btn btn-sm btn-primary">
                            Thêm mới sản phẩm
                        </a>

                        <a href="{{ route('product.trash') }}" class="btn btn-soft-danger btn-sm">Đã Xóa</a>
                        <a href="{{ route('product.variant.trash') }}" class="btn btn-soft-danger btn-sm">
                                    Biến thể đã xoá
                                </a>

                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle btn btn-sm btn-outline-light" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                This Month
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <!-- item-->
                                <a href="#!" class="dropdown-item">Download</a>
                                <!-- item-->
                                <a href="#!" class="dropdown-item">Export</a>
                                <!-- item-->
                                <a href="#!" class="dropdown-item">Import</a>
                            </div>
                        </div>
                    </div>
                    <div>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0 table-hover table-centered">
                                <thead class="bg-light-subtle">
                                    <tr>
                                        <th style="width: 20px;">
                                            <div class="form-check ms-1">
                                                <input type="checkbox" class="form-check-input" id="customCheck1">
                                                <label class="form-check-label" for="customCheck1"></label>
                                            </div>
                                        </th>
                                        <th>STT</th>
                                        <th>Tên sản phẩm</th>
                                        <th>Thương hiệu</th>
                                        <th>Danh mục</th>
                                        <th>Trạng thái</th>
                                        <th>Ngày Tạo</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($products as $product)
                                        <tr>
                                            <td>
                                                <div class="form-check ms-1">
                                                    <input type="checkbox" class="form-check-input"
                                                        value="{{ $product->id }}" name="ids[]">
                                                    <label class="form-check-label"> </label>
                                                </div>
                                            </td>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <div
                                                        class="rounded bg-light avatar-md d-flex align-items-center justify-content-center">
                                                        <img src="{{ asset('storage/' . $product->image_primary) }}"
                                                            alt="" class="avatar-md">
                                                    </div>
                                                    <div>
                                                        {{ $product->name }}
                                                        {{-- <a href="#!" class="text-dark fw-medium fs-15">Black T-shirt</a>
                                                                <p class="text-muted mb-0 mt-1 fs-13"><span>Size : </span>S , M , L , Xl </p> --}}
                                                    </div>
                                                </div>

                                            </td>
                                            <td>{{ $product->brand->name ?? 'N/A' }}</td>
                                            <td>{{ $product->category->name ?? 'N/A' }}</td>
                                            <td>{{ $product->status }}</td>
                                            <td>{{ $product->created_at }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('product.detail', $product->id) }}"
                                                        class="btn btn-light btn-sm"><iconify-icon icon="solar:eye-broken"
                                                            class="align-middle fs-18"></iconify-icon></a>
                                                    <a href="{{ route('product.edit', $product->id) }}"
                                                        class="btn btn-soft-primary btn-sm"><iconify-icon
                                                            icon="solar:pen-2-broken"
                                                            class="align-middle fs-18"></iconify-icon></a>
                                                    <a href="{{ route('product.destroy', $product->id) }}"
                                                        onclick="return confirm('Bạn có chắc chắn muốn xóa?')"
                                                        class="btn btn-soft-danger btn-sm"><iconify-icon
                                                            icon="solar:trash-bin-minimalistic-2-broken"
                                                            class="align-middle fs-18"></iconify-icon></a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
