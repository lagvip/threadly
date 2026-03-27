@extends('admin.layouts.layout')

<style>
    .custom-delete {
        display: flex;
        justify-content: end;
    }

    .option-box {
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 12px;
        max-height: 220px;
        overflow-y: auto;
        background: #fff;
    }

    .option-box .form-check {
        margin-bottom: 8px;
    }

    .variant-item,
    .variant-row {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 8px;
        background: #fff;
    }

    .img-thumb {
        width: 80px;
        height: 80px;
        object-fit: cover;
    }

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
        cursor: pointer;
    }

    .status-toggle-text {
        font-weight: 600;
        font-size: 13px;
        color: #198754;
    }

    .status-toggle-text.inactive {
        color: #dc3545;
    }

    .variant-action-box {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .variant-deleted {
        opacity: 0.45;
    }
</style>

@section('content')
<div class="">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <form action="{{ route('product.postEdit', $product->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('POST')

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
                                <span class="text-danger d-block">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

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
                                    @error('id_category')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
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
                                    @error('id_brand')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-lg-12">
                                    <label for="description" class="form-label">Mô Tả</label>
                                    <textarea name="description" id="description" class="form-control">{{ old('description', $product->description) }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label class="form-label d-block">Trạng thái sản phẩm</label>
                                    <div class="status-switch-wrap">
                                        <input type="hidden" name="status" value="inactive">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input status-toggle-input"
                                                   type="checkbox"
                                                   role="switch"
                                                   id="product-status-toggle"
                                                   name="status"
                                                   value="active"
                                                   {{ old('status', $product->status) == 'active' ? 'checked' : '' }}>
                                        </div>
                                        <span class="status-toggle-text {{ old('status', $product->status) == 'active' ? '' : 'inactive' }}">
                                            {{ old('status', $product->status) == 'active' ? 'Hoạt động' : 'Không hoạt động' }}
                                        </span>
                                    </div>
                                    <small class="text-muted">Khi sản phẩm đang tắt, sản phẩm và các biến thể sẽ không được dùng ở client.</small>
                                    @error('status')
                                        <span class="text-danger d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h4 class="card-title">Tạo nhanh biến thể</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-5">
                                    <label class="form-label">Chọn màu sắc</label>
                                    <div class="option-box">
                                        @foreach ($colors as $color)
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input color-checkbox"
                                                    type="checkbox"
                                                    value="{{ $color->id }}"
                                                    id="color_{{ $color->id }}"
                                                    data-name="{{ $color->name }}"
                                                >
                                                <label class="form-check-label" for="color_{{ $color->id }}">
                                                    {{ $color->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="col-md-5">
                                    <label class="form-label">Chọn kích cỡ</label>
                                    <div class="option-box">
                                        @foreach ($sizes as $size)
                                            <div class="form-check">
                                                <input
                                                    class="form-check-input size-checkbox"
                                                    type="checkbox"
                                                    value="{{ $size->id }}"
                                                    id="size_{{ $size->id }}"
                                                    data-name="{{ $size->name }}"
                                                >
                                                <label class="form-check-label" for="size_{{ $size->id }}">
                                                    {{ $size->name }}
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>

                                <div class="col-md-2 d-flex align-items-end">
                                    <button type="button" class="btn btn-success w-100" id="generate-variants">
                                        Tạo biến thể
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header">
                            <h4 class="card-title">Biến thể sản phẩm</h4>
                        </div>
                        <div class="card-body">
                            @error('variants')
                                <span class="text-danger d-block mb-2">{{ $message }}</span>
                            @enderror

                            <div id="variant-list">
                                @forelse ($product->variants as $index => $variant)
                                    @php
                                        $oldStatus = old('variants.' . $index . '.status', $variant->status ?? 'active');
                                    @endphp
                                    <div class="row variant-row mb-3"
                                         data-type="old"
                                         data-color="{{ $variant->id_color }}"
                                         data-size="{{ $variant->id_size }}"
                                         data-key="{{ $variant->id_color . '-' . $variant->id_size }}">

                                        <input type="hidden" name="variants[{{ $index }}][id]" value="{{ $variant->id }}">

                                        <div class="col-md-2">
                                            <label class="form-label">Màu sắc</label>
                                            <select name="variants[{{ $index }}][id_color]" class="form-control">
                                                @foreach($colors as $color)
                                                    <option value="{{ $color->id }}"
                                                        {{ old('variants.'.$index.'.id_color', $variant->id_color) == $color->id ? 'selected' : '' }}>
                                                        {{ $color->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("variants.$index.id_color")
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label">Kích cỡ</label>
                                            <select name="variants[{{ $index }}][id_size]" class="form-control">
                                                @foreach($sizes as $size)
                                                    <option value="{{ $size->id }}"
                                                        {{ old('variants.'.$index.'.id_size', $variant->id_size) == $size->id ? 'selected' : '' }}>
                                                        {{ $size->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error("variants.$index.id_size")
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label">Giá</label>
                                            <input type="number"
                                                   class="form-control"
                                                   name="variants[{{ $index }}][price]"
                                                   value="{{ old('variants.' . $index . '.price', $variant->price) }}">
                                            @error("variants.$index.price")
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label">Số lượng</label>
                                            <input type="number"
                                                   class="form-control"
                                                   name="variants[{{ $index }}][quantity]"
                                                   value="{{ old('variants.' . $index . '.quantity', $variant->quantity) }}">
                                            @error("variants.$index.quantity")
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label d-block">Trạng thái</label>
                                            <div class="status-switch-wrap">
                                                <input type="hidden" name="variants[{{ $index }}][status]" value="inactive">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input status-toggle-input"
                                                           type="checkbox"
                                                           role="switch"
                                                           name="variants[{{ $index }}][status]"
                                                           value="active"
                                                           {{ $oldStatus == 'active' ? 'checked' : '' }}>
                                                </div>
                                                <span class="status-toggle-text {{ $oldStatus == 'active' ? '' : 'inactive' }}">
                                                    {{ $oldStatus == 'active' ? 'Hoạt động' : 'Không hoạt động' }}
                                                </span>
                                            </div>
                                            @error("variants.$index.status")
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-2">
                                            <label class="form-label">Ảnh biến thể</label>
                                            <div class="variant-action-box">
                                                <div class="variant-image-card text-center border rounded p-2">
                                                    @php $previewId = 'variant-preview-'.$index; @endphp
                                                    <img id="{{ $previewId }}"
                                                         src="{{ $variant->image ? asset('storage/'.$variant->image) : asset('images/placeholder-80x80.png') }}"
                                                         class="img-thumb mb-2 rounded"
                                                         alt="preview">

                                                    <input type="file"
                                                           name="variants[{{ $index }}][image]"
                                                           class="form-control form-control-sm variant-image-input"
                                                           accept="image/*"
                                                           data-preview="{{ $previewId }}">
                                                </div>

                                                <button type="button" class="btn btn-danger btn-mark-delete">Xóa</button>
                                                <input type="hidden" name="variants[{{ $index }}][delete]" value="0" class="delete-flag">
                                            </div>
                                            @error("variants.$index.image")
                                                <span class="text-danger d-block">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                @empty
                                    <div class="alert alert-warning">Sản phẩm này chưa có biến thể.</div>
                                @endforelse
                            </div>

                            <div id="variant-new-list" class="mt-3"></div>

                            <div class="d-flex justify-content-end gap-2 mt-3">
                                <button type="submit" class="btn btn-primary">Lưu</button>
                                <a href="{{ route('product.listProduct') }}" class="btn btn-secondary">Hủy</a>
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
    let variantNewIndex = 0;
    const PLACEHOLDER_SRC = `{{ asset('images/placeholder-80x80.png') }}`;

    function getSelectedOptions(selector) {
        return Array.from(document.querySelectorAll(selector + ':checked')).map(item => ({
            id: item.value,
            name: item.dataset.name
        }));
    }

    function bindStatusLabel(toggle) {
        const text = toggle.closest('.status-switch-wrap')?.querySelector('.status-toggle-text');
        if (!text) return;
        if (toggle.checked) {
            text.textContent = 'Hoạt động';
            text.classList.remove('inactive');
        } else {
            text.textContent = 'Không hoạt động';
            text.classList.add('inactive');
        }
    }

    function getSelectedKeys() {
        const colors = getSelectedOptions('.color-checkbox');
        const sizes = getSelectedOptions('.size-checkbox');
        const keys = [];

        colors.forEach(color => {
            sizes.forEach(size => {
                keys.push({
                    key: `${color.id}-${size.id}`,
                    color,
                    size
                });
            });
        });

        return keys;
    }

    function getOldVariantRows() {
        return Array.from(document.querySelectorAll('#variant-list .variant-row'));
    }

    function getNewVariantRows() {
        return Array.from(document.querySelectorAll('#variant-new-list .variant-item'));
    }

    function rowHasUserData(row) {
        const priceInput = row.querySelector('input[name*="[price]"]');
        const quantityInput = row.querySelector('input[name*="[quantity]"]');
        const imageInput = row.querySelector('input[type="file"]');
        const previewImg = row.querySelector('.img-thumb');

        const hasPrice = priceInput && priceInput.value !== '' && Number(priceInput.value) > 0;
        const hasQuantity = quantityInput && quantityInput.value !== '' && Number(quantityInput.value) > 0;
        const hasNewFile = imageInput && imageInput.files && imageInput.files.length > 0;

        let hasExistingImage = false;
        if (previewImg && previewImg.getAttribute('src')) {
            const currentSrc = previewImg.getAttribute('src');
            hasExistingImage = currentSrc && !currentSrc.includes('placeholder-80x80.png');
        }

        return hasPrice || hasQuantity || hasNewFile || hasExistingImage;
    }

    function getVisibleExistingKeys() {
        const keys = [];

        getOldVariantRows().forEach(row => {
            const deleteFlag = row.querySelector('.delete-flag');
            if (deleteFlag && deleteFlag.value === '0' && row.style.display !== 'none') {
                keys.push(row.dataset.key);
            }
        });

        getNewVariantRows().forEach(row => {
            if (row.style.display !== 'none') {
                keys.push(row.dataset.key);
            }
        });

        return keys;
    }

    function createNewVariantRow(color, size) {
        const html = `
            <div class="row variant-item mb-3"
                 data-type="new"
                 data-color="${color.id}"
                 data-size="${size.id}"
                 data-key="${color.id}-${size.id}">
                <div class="col-md-2">
                    <label>Màu sắc</label>
                    <select name="variants_new[${variantNewIndex}][id_color]" class="form-control variant-color-select">
                        @foreach($colors as $color)
                            <option value="{{ $color->id }}" ${String({{ $color->id }}) === String(color.id) ? 'selected' : ''}>{{ $color->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Kích cỡ</label>
                    <select name="variants_new[${variantNewIndex}][id_size]" class="form-control variant-size-select">
                        @foreach($sizes as $size)
                            <option value="{{ $size->id }}" ${String({{ $size->id }}) === String(size.id) ? 'selected' : ''}>{{ $size->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label>Giá</label>
                    <input type="number" name="variants_new[${variantNewIndex}][price]" class="form-control" placeholder="Nhập giá">
                </div>

                <div class="col-md-2">
                    <label>Số lượng</label>
                    <input type="number" name="variants_new[${variantNewIndex}][quantity]" class="form-control" placeholder="Nhập số lượng">
                </div>

                <div class="col-md-2">
                    <label class="d-block">Trạng thái</label>
                    <div class="status-switch-wrap">
                        <input type="hidden" name="variants_new[${variantNewIndex}][status]" value="inactive">
                        <div class="form-check form-switch">
                            <input class="form-check-input status-toggle-input"
                                   type="checkbox"
                                   role="switch"
                                   name="variants_new[${variantNewIndex}][status]"
                                   value="active"
                                   checked>
                        </div>
                        <span class="status-toggle-text">Hoạt động</span>
                    </div>
                </div>

                <div class="col-md-2">
                    <label>Ảnh biến thể</label>
                    <div class="variant-action-box">
                        <div class="variant-image-card text-center border rounded p-2">
                            <img id="variant-new-preview-${variantNewIndex}"
                                 src="${PLACEHOLDER_SRC}"
                                 class="img-thumb mb-2 rounded"
                                 alt="preview">

                            <input type="file"
                                   name="variants_new[${variantNewIndex}][image]"
                                   class="form-control form-control-sm variant-image-input"
                                   accept="image/*"
                                   data-preview="variant-new-preview-${variantNewIndex}">
                        </div>
                        <button type="button" class="btn btn-danger remove-variant">Xóa</button>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('variant-new-list').insertAdjacentHTML('beforeend', html);
        variantNewIndex++;

        const newToggle = document.querySelector('#variant-new-list .variant-item:last-child .status-toggle-input');
        if (newToggle) {
            bindStatusLabel(newToggle);
        }
    }

    function syncVariantsBySelection() {
        const selected = getSelectedKeys();
        const selectedKeyMap = selected.map(item => item.key);

        getOldVariantRows().forEach(row => {
            const rowKey = row.dataset.key;
            const deleteFlag = row.querySelector('.delete-flag');
            const hasData = rowHasUserData(row);

            if (selectedKeyMap.includes(rowKey)) {
                deleteFlag.value = '0';
                row.style.display = '';
                row.classList.remove('variant-deleted');
            } else {
                if (!hasData) {
                    deleteFlag.value = '1';
                    row.style.display = 'none';
                }
            }
        });

        getNewVariantRows().forEach(row => {
            const hasData = rowHasUserData(row);

            if (!selectedKeyMap.includes(row.dataset.key) && !hasData) {
                row.remove();
            }
        });

        const existingKeys = getVisibleExistingKeys();

        selected.forEach(item => {
            if (!existingKeys.includes(item.key)) {
                createNewVariantRow(item.color, item.size);
            }
        });
    }

    document.getElementById('generate-variants').addEventListener('click', function() {
        const colors = getSelectedOptions('.color-checkbox');
        const sizes = getSelectedOptions('.size-checkbox');

        if (colors.length === 0 || sizes.length === 0) {
            alert('Vui lòng chọn ít nhất 1 màu sắc và 1 kích cỡ');
            return;
        }

        syncVariantsBySelection();
    });

    document.addEventListener('change', function(e) {
        if (e.target.classList.contains('variant-image-input')) {
            const previewId = e.target.getAttribute('data-preview');
            const previewEl = document.getElementById(previewId);
            const file = e.target.files[0];

            if (file && previewEl) {
                previewEl.src = URL.createObjectURL(file);
            }
        }

        if (e.target.classList.contains('status-toggle-input')) {
            bindStatusLabel(e.target);
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('remove-variant')) {
            e.target.closest('.variant-item').remove();
        }
    });

    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('btn-mark-delete')) {
            if (!confirm('Bạn có chắc muốn xoá biến thể này?')) return;

            const row = e.target.closest('.variant-row');
            row.querySelector('.delete-flag').value = '1';
            row.style.display = 'none';
            row.classList.add('variant-deleted');
        }
    });

    document.querySelectorAll('.status-toggle-input').forEach(bindStatusLabel);
</script>
@endpush
@endsection
