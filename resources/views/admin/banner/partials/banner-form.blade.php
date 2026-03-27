@php
    $isEdit = isset($banner);
    $idSuffix = $isEdit ? 'Update' : '';
    $imagePreviewPath = $banner->image ?? null;
    if ($imagePreviewPath && str_starts_with($imagePreviewPath, 'public/')) {
        $imagePreviewPath = substr($imagePreviewPath, 7);
    }
    $imagePreviewUrl = $imagePreviewPath ? asset('storage/' . $imagePreviewPath) : null;
    $submitLabel = $submitLabel ?? ($isEdit ? 'Cập nhật' : 'Thêm');
    $formId = $formId ?? 'bannerForm';
@endphp

<form action="{{ $action }}" method="POST" enctype="multipart/form-data" id="{{ $formId }}">
    @csrf
    @isset($method)
        @method($method)
    @endisset

    @if ($errors->any())
        <div class="alert alert-danger">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ $isEdit ? 'Cập nhật ảnh' : 'Thêm ảnh' }}</h4>
        </div>
        <div class="card-body">
            <div class="image-upload-wrapper" id="imageUploadWrapper{{ $idSuffix }}">
                <input type="file" name="image" id="actualImageInput{{ $idSuffix }}" class="hidden-input"
                    accept="image/*" />
                <div class="dz-message needsclick">
                    <i class="bx bx-cloud-upload fs-48 text-primary"></i>
                    <h3 class="mt-4">Kéo ảnh vào đây, hoặc <span class="text-primary">nhấp để duyệt</span>
                    </h3>
                    <span class="text-muted fs-13">
                        1600 x 1200 (4:3) khuyến nghị. Chỉ cho phép file PNG, JPG và GIF.
                    </span>
                    <p id="selectedFileName{{ $idSuffix }}" class="selected-file-name mt-2"></p>
                    <div id="imagePreview{{ $idSuffix }}" class="image-preview mt-3">
                        @if ($imagePreviewUrl)
                            <img src="{{ $imagePreviewUrl }}" alt="Current Image"
                                style="max-width: 100%; max-height: 120px; display: block; margin: 0 auto; border-radius: 4px;">
                        @endif
                    </div>
                </div>
                @error('image')
                    <span style="color: red;">{{ $message }}</span>
                @enderror
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h4 class="card-title">{{ $isEdit ? 'Cập nhật banner' : 'Thêm banner' }}</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label for="banner-title" class="form-label">Tiêu đề</label>
                        <input type="text" name="title" id="banner-title" class="form-control"
                            placeholder="Nhập tiêu đề" value="{{ old('title', $banner->title ?? '') }}">
                        @error('title')
                            <span style="color: red;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label for="banner-link" class="form-label">Link</label>
                        <input type="text" name="link" id="banner-link" class="form-control"
                            placeholder="Nhập link (tùy chọn)" value="{{ old('link', $banner->link ?? '') }}">
                        @error('link')
                            <span style="color: red;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label for="banner-position" class="form-label">Vị trí</label>
                        <input type="number" name="position" id="banner-position" class="form-control"
                            placeholder="Nhập vị trí" value="{{ old('position', $banner->position ?? 0) }}" min="0">
                        @error('position')
                            <span style="color: red;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label for="banner-status" class="form-label">Trạng thái</label>
                        <div class="form-check form-switch">
                            <input type="hidden" name="is_active" value="0">
                            @php
                                $activeValue = old('is_active', $banner->is_active ?? 1);
                            @endphp
                            <input class="form-check-input" type="checkbox" name="is_active" id="banner-status" value="1"
                                {{ $activeValue ? 'checked' : '' }}>
                            <label class="form-check-label" for="banner-status">Kích hoạt</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="p-3 bg-light mb-3 rounded">
        <div class="row justify-content-end g-2">
            <div class="col-lg-2">
                <button type="submit" class="btn btn-outline-secondary w-100">{{ $submitLabel }}</button>
            </div>
            <div class="col-lg-2">
                <a href="{{ route('listBanner.list') }}" class="btn btn-primary w-100">Hủy</a>
            </div>
        </div>
    </div>
</form>
