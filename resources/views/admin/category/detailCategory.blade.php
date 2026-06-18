@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center gap-2">
                    <div>
                        <h4 class="card-title flex-grow-1 mb-1">Sản phẩm thuộc danh mục: {{ $category->name }}</h4>
                        <small class="text-muted">
                            Danh mục cha:
                            {{ $category->parent ? $category->parent->name : 'Danh mục cha' }}
                        </small>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="{{ route('product.create') }}" class="btn btn-sm btn-primary">
                            Thêm mới sản phẩm
                        </a>

                        <a href="{{ route('listCategory.list') }}" class="btn btn-sm btn-secondary">
                            Quay lại danh mục
                        </a>
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
                                    <th>Ngày tạo</th>
                                    <th>Hành động</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($products as $product)
                                    <tr>
                                        <td>
                                            <div class="form-check ms-1">
                                                <input type="checkbox" class="form-check-input" value="{{ $product->id }}" name="ids[]">
                                                <label class="form-check-label"></label>
                                            </div>
                                        </td>

                                        <td>{{ $products->firstItem() + $loop->index }}</td>

                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded bg-light avatar-md d-flex align-items-center justify-content-center">
                                                    <img src="{{ asset('storage/' . $product->image_primary) }}" alt="{{ $product->name }}" class="avatar-md">
                                                </div>
                                                <div>
                                                    {{ $product->name }}
                                                </div>
                                            </div>
                                        </td>

                                        <td>{{ $product->brand->name ?? 'Không có' }}</td>
                                        <td>{{ $product->category->name ?? 'Không có' }}</td>
                                        <td>{{ $product->status }}</td>
                                        <td>{{ $product->created_at ? $product->created_at->format('d/m/Y H:i') : '' }}</td>

                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('product.detail', $product->id) }}" class="btn btn-light btn-sm">
                                                    <iconify-icon icon="solar:eye-broken" class="align-middle fs-18"></iconify-icon>
                                                </a>

                                                <a href="{{ route('product.edit', $product->id) }}" class="btn btn-soft-primary btn-sm">
                                                    <iconify-icon icon="solar:pen-2-broken" class="align-middle fs-18"></iconify-icon>
                                                </a>

                                                <form action="{{ route('product.destroy', $product->id) }}" method="POST"
                                                      onsubmit="return confirm('Bạn có chắc chắn muốn xóa?')">
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
                                        <td colspan="8" class="text-center text-muted py-4">
                                            Danh mục này hiện chưa có sản phẩm nào
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer border-top">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
