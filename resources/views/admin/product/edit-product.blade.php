@extends('admin.layouts.layout')
@section('content')
<div class="">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12 ">
                <form action="{{ route('product.postEdit', $product->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')

                    {{-- Ảnh chính --}}
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Sửa ảnh sản phẩm</h4>
                        </div>
                        <div class="card-body">
                            <input type="file" name="image_primary" id="image_primary" class="form-control mb-2">
                            @if($product->image_primary)
                                <img src="{{ asset('storage/' . $product->image_primary) }}" alt="{{ $product->name }}" width="120" class="rounded border">
                            @endif
                            @error('image_primary')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Thông tin sản phẩm --}}
                    <div class="card mt-3">
                        <div class="card-header">
                            <h4 class="card-title">Thông tin sản phẩm</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <label for="name" class="form-label">Tên sản phẩm</label>
                                    <input type="text" name="name" id="name" class="form-control"
                                        value="{{ old('name', $product->name) }}">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-lg-4">
                                    <label for="id_category" class="form-label">Danh Mục</label>
                                    <select name="id_category" id="id_category" class="form-control">
                                        <option value="">-- Chọn danh mục --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('id_category', $product->id_category) == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-4">
                                    <label for="id_brand" class="form-label">Thương Hiệu</label>
                                    <select name="id_brand" id="id_brand" class="form-control">
                                        <option value="">-- Chọn thương hiệu --</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('id_brand', $product->id_brand) == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-lg-12">
                                    <label for="description" class="form-label">Mô Tả</label>
                                    <textarea name="description" id="description" class="form-control">{{ old('description', $product->description) }}</textarea>
                                </div>

                                <div class="col-lg-4">
                                    <label for="status" class="form-label">Trạng Thái</label>
                                    <select name="status" id="status" class="form-control">
                                        <option value="active" {{ old('status', $product->status) == 'active' ? 'selected' : '' }}>Hoạt Động</option>
                                        <option value="inactive" {{ old('status', $product->status) == 'inactive' ? 'selected' : '' }}>Không Hoạt Động</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Biến thể --}}
                    <div class="card mt-3">
                        <div class="card-header">
                            <h4 class="card-title">Biến thể sản phẩm</h4>
                        </div>
                        <div class="card-body">
                            @forelse ($product->variants as $index => $variant)
                                <div class="row border p-3 mb-3 rounded bg-light variant-row">
                                    <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">

                                    <div class="col-md-2">
                                        <label class="form-label">Màu sắc</label>
                                        <select name="variants[{{ $index }}][id_color]" class="form-control">
                                            @foreach ($colors as $color)
                                                <option value="{{ $color->id }}" {{ $variant->id_color == $color->id ? 'selected' : '' }}>
                                                    {{ $color->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Kích cỡ</label>
                                        <select name="variants[{{ $index }}][id_size]" class="form-control">
                                            @foreach ($sizes as $size)
                                                <option value="{{ $size->id }}" {{ $variant->id_size == $size->id ? 'selected' : '' }}>
                                                    {{ $size->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Giá</label>
                                        <input type="number" class="form-control"
                                            name="variants[{{ $index }}][price]"
                                            value="{{ old('variants.' . $index . '.price', $variant->price) }}">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label">Số lượng</label>
                                        <input type="number" class="form-control"
                                            name="variants[{{ $index }}][quantity]"
                                            value="{{ old('variants.' . $index . '.quantity', $variant->quantity) }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label">Ảnh biến thể</label>
                                        <div class="variant-image-card text-center border rounded p-2">
                                            @php $previewId = 'variant-preview-'.$index; @endphp
                                            <img id="{{ $previewId }}"
                                                src="{{ $variant->image ? asset('storage/'.$variant->image) : asset('images/placeholder-80x80.png') }}"
                                                class="img-thumb mb-2 rounded" alt="preview">
                                            <input type="file"
                                                name="variants[{{ $index }}][image]"
                                                class="form-control form-control-sm variant-image-input"
                                                accept="image/*"
                                                data-preview="{{ $previewId }}">
                                        </div>
                                    </div>

                                    <div class="col-md-1 custom-delete d-flex align-items-end">
                                        <button type="button" class="btn btn-danger w-100 btn-mark-delete">Xóa</button>
                                        <input type="hidden" name="variants[{{ $index }}][delete]" value="0" class="delete-flag">
                                    </div>

                                </div>
                            @empty
                                <div class="alert alert-warning">Sản phẩm này chưa có biến thể.</div>
                            @endforelse

                            <div id="variant-new-list" class="mt-3"></div>

                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" class="btn btn-success" id="add-variant-btn">+ Thêm biến thể</button>
                                <button type="submit" class="btn btn-primary">Lưu</button>
                                <a href="{{ route('product.listProduct') }}" class="btn btn-secondary">Hủy</a>
                                {{-- Nút sang trang biến thể đã xoá --}}

                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let variantIndex = {{ count($product->variants) ?? 0 }};

    document.getElementById('add-variant-btn').addEventListener('click', function() {
        const html = `
        <div class="row variant-item mb-3 border p-3 bg-light rounded">
            <div class="col-md-2">
                <label>Màu sắc</label>
                <select name="variants_new[${variantIndex}][id_color]" class="form-control">
                    @foreach ($colors as $color)
                        <option value="{{ $color->id }}">{{ $color->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Kích cỡ</label>
                <select name="variants_new[${variantIndex}][id_size]" class="form-control">
                    @foreach ($sizes as $size)
                        <option value="{{ $size->id }}">{{ $size->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label>Giá</label>
                <input type="number" name="variants_new[${variantIndex}][price]" class="form-control" required>
            </div>
            <div class="col-md-2">
                <label>Số lượng</label>
                <input type="number" name="variants_new[${variantIndex}][quantity]" class="form-control" required>
            </div>
            <div class="col-md-3">
                <label>Ảnh</label>
                <div class="variant-image-card text-center border rounded p-2">
                    <img id="variant-new-preview-${variantIndex}" src="{{ asset('images/placeholder-80x80.png') }}" class="img-thumb mb-2 rounded" alt="preview">
                    <input type="file" name="variants_new[${variantIndex}][image]" class="form-control form-control-sm variant-image-input" accept="image/*" data-preview="variant-new-preview-${variantIndex}">
                </div>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger remove-variant">Xóa</button>
            </div>
        </div>`;
        document.getElementById('variant-new-list').insertAdjacentHTML('beforeend', html);
        variantIndex++;
    });

    // preview ảnh khi chọn file
    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('variant-image-input')) {
            const previewId = e.target.getAttribute('data-preview');
            const previewEl = document.getElementById(previewId);
            const file = e.target.files[0];
            if (file && previewEl) {
                previewEl.src = URL.createObjectURL(file);
            }
        }
    });

    // remove variant mới thêm
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-variant')) {
            e.target.closest('.variant-item').remove();
        }
    });
    // mark biến thể xoá
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-mark-delete')) {
            if (!confirm('Bạn có chắc muốn xoá biến thể này?')) return;

            let row = e.target.closest('.variant-row');
            row.querySelector('.delete-flag').value = 1;

            // Ẩn row cho trực quan
            row.style.display = 'none';
        }
});

</script>
@endpush

<style>
    .custom-delete {
        display: flex;
        justify-content: end;
    }
    .img-thumb {
        width: 80px;
        height: 80px;
        object-fit: cover;
    }
</style>
@endsection
