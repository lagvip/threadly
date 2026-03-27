@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-12">
            <form action="{{ route('product.postEdit', $product->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('POST')

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

                        <div class="dz-message needsclick">
                            @error('image_primary')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
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
                                    <label for="name" class="form-label">Tên sản phẩm</label>
                                    <input type="text"
                                        name="name"
                                        id="name"
                                        class="form-control"
                                        value="{{ old('name', $product->name) }}">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="id_category" class="form-label">Danh Mục</label>
                                    <select name="id_category" id="id_category" class="form-control">
                                        <option value="">-- Vui lòng chọn danh mục --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}"
                                                {{ old('id_category', $product->id_category) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_category')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="id_brand" class="form-label">Thương Hiệu</label>
                                    <select name="id_brand" id="id_brand" class="form-control">
                                        <option value="">-- Vui lòng chọn thương hiệu --</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}"
                                                {{ old('id_brand', $product->id_brand) == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_brand')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Trạng Thái</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="active"
                                            {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>
                                            Hoạt Động
                                        </option>
                                        <option value="inactive"
                                            {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>
                                            Không Hoạt Động
                                        </option>
                                    </select>
                                    @error('status')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-4"></div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Mô Tả</label>
                                    <textarea name="description" id="description" class="form-control" rows="4">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="p-3 bg-light mb-3 rounded">
                            <div class="row justify-content-end g-2">
                                <div class="col-lg-1">
                                    <a href="{{ route('product.listProduct') }}" class="btn btn-primary w-100">Cancel</a>
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
                                    <input type="text"
                                        class="form-control"
                                        value="{{ $variant->size->name ?? 'Không xác định' }}"
                                        disabled>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Giá</label>
                                    <input type="text"
                                        class="form-control"
                                        value="{{ number_format($variant->price ?? 0, 0, ',', '.') }} đ"
                                        disabled>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Số lượng</label>
                                    <input type="text"
                                        class="form-control"
                                        value="{{ $variant->quantity ?? 0 }}"
                                        disabled>
                                </div>

                                <div class="col-md-2">
                                    <label class="form-label">Ảnh biến thể</label><br>
                                    @if ($variant->image)
                                        <img src="{{ asset('storage/' . $variant->image) }}"
                                            alt="Ảnh biến thể"
                                            width="80"
                                            class="img-thumbnail">
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
