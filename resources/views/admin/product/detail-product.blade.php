@extends('admin.layouts.layout')

<style>
    .status-switch-wrap {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .status-switch-wrap .form-check {
        margin-bottom: 0;
    }

    .status-switch-wrap .form-check-input {
        width: 3rem;
        height: 1.5rem;
    }

    .status-toggle-text {
        font-weight: 600;
        font-size: 13px;
        color: #198754;
    }

    .status-toggle-text.inactive {
        color: #dc3545;
    }
</style>

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-12">
            <form action="#" method="POST">
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Ảnh sản phẩm</h4>
                    </div>
                    <div class="card-body">
                        <div class="fallback">
                            @if($product->image_primary)
                                <img src="{{ asset('storage/' . $product->image_primary) }}"
                                    alt="{{ $product->name }}"
                                    width="100"
                                    class="mt-2 img-thumbnail">
                            @else
                                <span class="text-muted">Chưa có ảnh sản phẩm</span>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Thông tin sản phẩm</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Tên sản phẩm</label>
                                    <input type="text" class="form-control" value="{{ $product->name }}" disabled>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Danh Mục</label>
                                    <input type="text" class="form-control" value="{{ $product->category->name ?? 'Không có' }}" disabled>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label">Thương Hiệu</label>
                                    <input type="text" class="form-control" value="{{ $product->brand->name ?? 'Không có' }}" disabled>
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label class="form-label d-block">Trạng Thái</label>
                                    <div class="status-switch-wrap">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input"
                                                   type="checkbox"
                                                   role="switch"
                                                   disabled
                                                   {{ $product->status === 'active' ? 'checked' : '' }}>
                                        </div>
                                        <span class="status-toggle-text {{ $product->status === 'active' ? '' : 'inactive' }}">
                                            {{ $product->status === 'active' ? 'Hoạt động' : 'Không hoạt động' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="col-lg-4"></div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label">Mô Tả</label>
                                    <textarea class="form-control" rows="4" disabled>{{ $product->description }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-light mb-3 rounded">
                            <div class="row justify-content-end g-2">
                                <div class="col-lg-2">
                                    <a href="{{ route('product.listProduct') }}" class="btn btn-primary w-100">Quay lại</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Biến thể sản phẩm</h4>
                    </div>
                    <div class="card-body">
                        @forelse ($product->variants as $variant)
                            <div class="row border p-3 mb-2 rounded bg-light align-items-center">
                                <div class="col-md-2">
                                    <label class="form-label">Màu sắc</label>
                                    <div class="d-flex align-items-center">
                                        <div style="width: 20px; height: 20px; border-radius: 50%; background-color: {{ $variant->color->code ?? '#ccc' }}; margin-right: 10px; border: 1px solid #ddd;"></div>
                                        <span>{{ $variant->color->name ?? 'Không xác định' }}</span>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Kích cỡ</label>
                                    <input type="text" class="form-control" value="{{ $variant->size->name ?? 'Không xác định' }}" disabled>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Giá</label>
                                    <input type="text" class="form-control" value="{{ number_format($variant->price ?? 0, 0, ',', '.') }} đ" disabled>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Số lượng</label>
                                    <input type="text" class="form-control" value="{{ $variant->quantity ?? 0 }}" disabled>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label d-block">Trạng thái</label>
                                    <div class="status-switch-wrap">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" role="switch" disabled {{ $variant->status === 'active' ? 'checked' : '' }}>
                                        </div>
                                        <span class="status-toggle-text {{ $variant->status === 'active' ? '' : 'inactive' }}">
                                            {{ $variant->status === 'active' ? 'Hoạt động' : 'Không hoạt động' }}
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Ảnh biến thể</label><br>
                                    @if ($variant->image)
                                        <img src="{{ asset('storage/' . $variant->image) }}" alt="Ảnh biến thể" width="80" class="img-thumbnail">
                                    @else
                                        <span class="text-muted">Chưa có ảnh</span>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="alert alert-warning">Sản phẩm này chưa có biến thể.</div>
                        @endforelse
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
