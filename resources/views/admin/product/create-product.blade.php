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

    .variant-item {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 12px 8px;
        background: #fff;
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

    .status-note {
        font-size: 12px;
        color: #6c757d;
        margin-top: 6px;
    }

    .variant-action-box {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }
</style>

@section('content')
<div class="">
    <div class="container-fluid">
        <div class="col-xl-12">
            <form action="{{ route('product.postCreate') }}" method="POST" enctype="multipart/form-data">
                @csrf

                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Thêm ảnh sản phẩm</h4>
                    </div>
                    <div class="card-body">
                        <div class="fallback">
                            <input type="file" name="image_primary" id="image_primary">
                        </div>
                        <div class="dz-message needsclick">
                            <i class="bx bx-cloud-upload fs-48 text-primary"></i>
                            <h3 class="mt-4">Thả hình ảnh ở đây, hoặc <span class="text-primary">nhấp để duyệt</span></h3>
                            <span class="text-muted fs-13">Khuyến nghị 1600 x 1200 (4:3). Chỉ cho phép tệp PNG, JPG và GIF</span>
                            @error('image_primary')
                                <span class="text-danger d-block">{{ $message }}</span>
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
                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Tên sản phẩm</label>
                                    <input
                                        type="text"
                                        name="name"
                                        id="name"
                                        class="form-control"
                                        placeholder="Vui lòng nhập tên sản phẩm"
                                        value="{{ old('name') }}"
                                    >
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="id_category" class="form-label">Danh Mục</label>
                                    <select name="id_category" id="id_category" class="form-control">
                                        <option value="">-- Vui lòng chọn danh mục --</option>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ old('id_category') == $category->id ? 'selected' : '' }}>
                                                {{ $category->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_category')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-4">
                                <div class="mb-3">
                                    <label for="id_brand" class="form-label">Thương Hiệu</label>
                                    <select name="id_brand" id="id_brand" class="form-control">
                                        <option value="">-- Vui lòng chọn thương hiệu --</option>
                                        @foreach ($brands as $brand)
                                            <option value="{{ $brand->id }}" {{ old('id_brand') == $brand->id ? 'selected' : '' }}>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('id_brand')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="description" class="form-label">Mô Tả</label>
                                    <textarea name="description" id="description" class="form-control">{{ old('description') }}</textarea>
                                    @error('description')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label class="form-label d-block">Trạng thái sản phẩm</label>
                                    <div class="status-switch-wrap">
                                        <input type="hidden" name="status" value="{{ $inactiveProductStatus }}">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input status-toggle-input"
                                                   type="checkbox"
                                                   role="switch"
                                                   id="product-status-toggle"
                                                   name="status"
                                                   value="{{ $activeProductStatus }}"
                                                   {{ old('status', $activeProductStatus) == $activeProductStatus ? 'checked' : '' }}>
                                        </div>
                                        <span class="status-toggle-text {{ old('status', $activeProductStatus) == $activeProductStatus ? '' : 'inactive' }}">
                                            {{ $productStatusOptions[old('status', $activeProductStatus)] ?? old('status', $activeProductStatus) }}
                                        </span>
                                    </div>
                                    <div class="status-note">
                                        Khi sản phẩm đang tắt, sản phẩm sẽ không hiển thị ở client và không được xem là còn dùng được.
                                    </div>
                                    @error('status')
                                        <span class="text-danger d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">Tạo nhanh biến thể</h5>
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

                        <h5 class="mt-4">Biến thể sản phẩm</h5>

                        @error('variants')
                            <span class="text-danger d-block mb-2">{{ $message }}</span>
                        @enderror

                        <div id="variant-list">
                            @if(old('variants'))
                                @foreach(old('variants') as $index => $variant)
                                    @php
                                        $selectedColor = $colors->firstWhere('id', $variant['id_color'] ?? null);
                                        $selectedSize = $sizes->firstWhere('id', $variant['id_size'] ?? null);
                                        $variantStatus = $variant['status'] ?? $activeProductStatus;
                                    @endphp

                                    <div class="row variant-item mb-3" data-color="{{ $variant['id_color'] ?? '' }}" data-size="{{ $variant['id_size'] ?? '' }}" data-key="{{ ($variant['id_color'] ?? '') . '-' . ($variant['id_size'] ?? '') }}">
                                        <div class="col-md-2">
                                            <label>Màu sắc</label>
                                            <input type="text" class="form-control" value="{{ $selectedColor->name ?? '' }}" readonly>
                                            <input type="hidden" name="variants[{{ $index }}][id_color]" value="{{ $variant['id_color'] ?? '' }}">
                                            @error("variants.$index.id_color")
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-2">
                                            <label>Kích cỡ</label>
                                            <input type="text" class="form-control" value="{{ $selectedSize->name ?? '' }}" readonly>
                                            <input type="hidden" name="variants[{{ $index }}][id_size]" value="{{ $variant['id_size'] ?? '' }}">
                                            @error("variants.$index.id_size")
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-2">
                                            <label>Giá</label>
                                            <input type="number" name="variants[{{ $index }}][price]" class="form-control" value="{{ $variant['price'] ?? '' }}">
                                            @error("variants.$index.price")
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-2">
                                            <label>Số lượng</label>
                                            <input type="number" name="variants[{{ $index }}][quantity]" class="form-control" value="{{ $variant['quantity'] ?? '' }}">
                                            @error("variants.$index.quantity")
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-2">
                                            <label class="d-block">Trạng thái</label>
                                            <div class="status-switch-wrap">
                                                <input type="hidden" name="variants[{{ $index }}][status]" value="{{ $inactiveProductStatus }}">
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input status-toggle-input"
                                                           type="checkbox"
                                                           role="switch"
                                                           name="variants[{{ $index }}][status]"
                                                           value="{{ $activeProductStatus }}"
                                                           {{ $variantStatus === $activeProductStatus ? 'checked' : '' }}>
                                                </div>
                                                <span class="status-toggle-text {{ $variantStatus === $activeProductStatus ? '' : 'inactive' }}">
                                                    {{ $productStatusOptions[$variantStatus] ?? $variantStatus }}
                                                </span>
                                            </div>
                                            @error("variants.$index.status")
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>

                                        <div class="col-md-2">
                                            <label>Ảnh biến thể</label>
                                            <div class="variant-action-box">
                                                <input type="file" name="variants[{{ $index }}][image]" class="form-control" accept="image/*">
                                                <button type="button" class="btn btn-danger remove-variant">Xóa</button>
                                            </div>
                                            @error("variants.$index.image")
                                                <span class="text-danger">{{ $message }}</span>
                                            @enderror
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>

                        <div class="rounded mt-3">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-primary">Lưu</button>
                                <a href="{{ route('product.listProduct') }}" class="btn btn-primary">Hủy</a>
                            </div>
                        </div>

                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>

    let variantIndex = {{ old('variants') ? count(old('variants')) : 0 }};
    const productStatusLabels = @json($productStatusOptions);
    const activeProductStatus = @json($activeProductStatus);
    const inactiveProductStatus = @json($inactiveProductStatus);
    // Lấy các tùy chọn đã chọn
    function getSelectedOptions(selector) {
        return Array.from(document.querySelectorAll(selector + ':checked')).map(item => ({
            id: item.value,
            name: item.dataset.name
        }));
    }
    // Tạo tất cả tổ hợp giữa màu sắc và kích cỡ đã chọn
    function getSelectedCombinations(colors, sizes) {
        const combinations = [];

        colors.forEach(color => {
            sizes.forEach(size => {
                combinations.push({
                    key: `${color.id}-${size.id}`,
                    color,
                    size
                });
            });
        });

        return combinations;
    }
    // Lấy các dòng biến thể hiện có trên giao diện
    function getExistingVariantRows() {
        return Array.from(document.querySelectorAll('.variant-item'));
    }
    // Cập nhật nhãn trạng thái
    function bindStatusLabel(toggle) {
        const text = toggle.closest('.status-switch-wrap')?.querySelector('.status-toggle-text');
        if (!text) return;
        if (toggle.checked) {
            text.textContent = productStatusLabels[activeProductStatus] || activeProductStatus;
            text.classList.remove('inactive');
        } else {
            text.textContent = productStatusLabels[inactiveProductStatus] || inactiveProductStatus;
            text.classList.add('inactive');
        }
    }
    // Tạo một dòng biến thể mới
    function createVariantRow(color, size) {
        const html = `
            <div class="row variant-item mb-3" data-color="${color.id}" data-size="${size.id}" data-key="${color.id}-${size.id}">
                <div class="col-md-2">
                    <label>Màu sắc</label>
                    <input type="text" class="form-control" value="${color.name}" readonly>
                    <input type="hidden" name="variants[${variantIndex}][id_color]" value="${color.id}">
                </div>

                <div class="col-md-2">
                    <label>Kích cỡ</label>
                    <input type="text" class="form-control" value="${size.name}" readonly>
                    <input type="hidden" name="variants[${variantIndex}][id_size]" value="${size.id}">
                </div>

                <div class="col-md-2">
                    <label>Giá</label>
                    <input type="number" name="variants[${variantIndex}][price]" class="form-control" placeholder="Nhập giá">
                </div>

                <div class="col-md-2">
                    <label>Số lượng</label>
                    <input type="number" name="variants[${variantIndex}][quantity]" class="form-control" placeholder="Nhập số lượng">
                </div>

                <div class="col-md-2">
                    <label class="d-block">Trạng thái</label>
                    <div class="status-switch-wrap">
                        <input type="hidden" name="variants[${variantIndex}][status]" value="${inactiveProductStatus}">
                        <div class="form-check form-switch">
                            <input class="form-check-input status-toggle-input"
                                   type="checkbox"
                                   role="switch"
                                   name="variants[${variantIndex}][status]"
                                   value="${activeProductStatus}"
                                   checked>
                        </div>
                        <span class="status-toggle-text">${productStatusLabels[activeProductStatus] || activeProductStatus}</span>
                    </div>
                </div>

                <div class="col-md-2">
                    <label>Ảnh biến thể</label>
                    <div class="variant-action-box">
                        <input type="file" name="variants[${variantIndex}][image]" class="form-control" accept="image/*">
                        <button type="button" class="btn btn-danger remove-variant">Xóa</button>
                    </div>
                </div>
            </div>
        `;

        document.getElementById('variant-list').insertAdjacentHTML('beforeend', html);
        variantIndex++;

        const newRow = document.querySelector('#variant-list .variant-item:last-child .status-toggle-input');
        if (newRow) {
            bindStatusLabel(newRow);
        }
    }

    function syncVariants() {
        const selectedColors = getSelectedOptions('.color-checkbox');
        const selectedSizes = getSelectedOptions('.size-checkbox');

        if (selectedColors.length === 0 || selectedSizes.length === 0) {
            alert('Vui lòng chọn ít nhất 1 màu sắc và 1 kích cỡ');
            return;
        }

        const selectedCombinations = getSelectedCombinations(selectedColors, selectedSizes);
        const selectedKeys = selectedCombinations.map(item => item.key);

        const existingRows = getExistingVariantRows();
        // Xóa các biến thể không còn trong lựa chọn
        existingRows.forEach(row => {
            const rowKey = row.dataset.key || `${row.dataset.color}-${row.dataset.size}`;
            if (!selectedKeys.includes(rowKey)) {
                row.remove();
            }
        });

        const currentKeys = Array.from(document.querySelectorAll('.variant-item')).map(row => {
            return row.dataset.key || `${row.dataset.color}-${row.dataset.size}`;
        });
        // Thêm các biến thể mới chưa tồn tại
        selectedCombinations.forEach(item => {
            if (!currentKeys.includes(item.key)) {
                createVariantRow(item.color, item.size);
            }
        });
    }
    // Xử lý sự kiện khi nhấn nút tạo biến thể
    document.getElementById('generate-variants').addEventListener('click', function() {
        syncVariants();
    });
    // Xử lý sự kiện khi nhấn nút xóa biến thể
    document.addEventListener('click', function(e) {
        if (e.target && e.target.classList.contains('remove-variant')) {
            e.target.closest('.variant-item').remove();
        }
    });
    // Cập nhật nhãn trạng thái khi thay đổi
    document.addEventListener('change', function (e) {
        if (e.target.classList.contains('status-toggle-input')) {
            bindStatusLabel(e.target);
        }
    });
    // Gắn nhãn trạng thái cho các biến thể đã tồn tại khi tải lại trang
    document.querySelectorAll('.status-toggle-input').forEach(bindStatusLabel);
</script>
@endpush
@endsection
