@extends('admin.layouts.layout')

@section('content')
<div class="container-xxl">

    <div class="row">
        {{-- Cột bên trái: Hiển thị ảnh hiện tại --}}
        <div class="col-xl-3 col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="bg-light text-center rounded">
                        {{-- Hiển thị ảnh hiện có --}}
                        @if ($brand->image)
                            <img src="{{ asset('storage/' . $brand->image) }}" alt="Current Image" class="avatar-xxl"
                                id="leftImagePreview"> {{-- ID đồng bộ để cập nhật ảnh bên trái --}}
                        @else
                            <p id="noImageText">Không có ảnh hiện tại.</p>
                            <img src="" alt="Image Placeholder" class="avatar-xxl" id="leftImagePreview"
                                style="display: none;">
                        @endif
                    </div>
                    <div class="mt-3">
                        <h4>{{ $brand->name }}</h4>
                        <div class="row">
                            <div class="col-lg-12">
                                <p class="mb-1 mt-2">Thương hiệu:</p>
                                <h5 class="mb-0">{{ $brand->name }}</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Cột bên phải: Form cập nhật --}}
        <div class="col-xl-9 col-lg-8">
            <form action="{{ route('brands.update', $brand->id) }}" method="POST"
                enctype="multipart/form-data" id="updateBrandForm">
                @csrf
                @method('PUT')
                
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Cập nhật ảnh</h4>
                    </div>
                    <div class="card-body">
                        {{-- Image Upload Wrapper giống hệt Category --}}
                        <div class="image-upload-wrapper" id="imageUploadWrapperUpdate" 
                             style="cursor: pointer; border: 2px dashed #dee2e6; padding: 30px; text-align: center;"
                             onclick="document.getElementById('actualImageInputUpdate').click()">
                            
                            {{-- Input file thực tế ẩn đi --}}
                            <input type="file" name="image" id="actualImageInputUpdate" class="d-none"
                                accept="image/*" onchange="previewImage(this)" />

                            <div class="dz-message needsclick">
                                <i class="bx bx-cloud-upload fs-48 text-primary"></i>
                                <h3 class="mt-4">Kéo ảnh mới vào đây, hoặc <span class="text-primary">nhấp để duyệt</span></h3>
                                <span class="text-muted fs-13">
                                    Khuyến nghị ảnh tỉ lệ 4:3. Chỉ cho phép file PNG, JPG và GIF.
                                </span>

                                <p id="selectedFileNameUpdate" class="selected-file-name mt-2"></p>
                                
                                <div id="imagePreviewUpdate" class="image-preview mt-3">
                                    {{-- Hiển thị ảnh xem trước mặc định nếu có ảnh cũ --}}
                                    @if ($brand->image)
                                        <img src="{{ asset('storage/' . $brand->image) }}" alt="Current Image"
                                            style="max-width: 100%; max-height: 120px; display: block; margin: 0 auto; border-radius: 4px;">
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header">
                        <h4 class="card-title">Thông tin thương hiệu</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="mb-3">
                                    <label for="brand-name" class="form-label">Tên thương hiệu</label>
                                    <input type="text" name="name" value="{{ old('name', $brand->name) }}"
                                        id="brand-name" class="form-control" placeholder="Nhập tên thương hiệu">
                                    @error('name')
                                        <span style="color: red;">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light mb-3 rounded">
                    <div class="row justify-content-end g-2">
                        <div class="col-lg-2">
                            <button type="submit" class="btn btn-outline-secondary w-100">Cập nhật</button>
                        </div>
                        <div class="col-lg-2">
                            <a href="{{ route('brands.index') }}" class="btn btn-primary w-100">Hủy</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Script xử lý preview giống logic Category --}}
<script>
function previewImage(input) {
    const file = input.files[0];
    const previewContainer = document.getElementById('imagePreviewUpdate');
    const leftPreview = document.getElementById('leftImagePreview');
    const noImageText = document.getElementById('noImageText');
    const fileNameDisplay = document.getElementById('selectedFileNameUpdate');

    if (file) {
        // Hiển thị tên file
        fileNameDisplay.textContent = "File đã chọn: " + file.name;

        const reader = new FileReader();
        reader.onload = function(e) {
            // 1. Cập nhật preview ở vùng upload (bên phải)
            previewContainer.innerHTML = `<img src="${e.target.result}" style="max-width: 100%; max-height: 120px; display: block; margin: 0 auto; border-radius: 4px;">`;
            
            // 2. Cập nhật ảnh ở cột bên trái
            if (leftPreview) {
                leftPreview.src = e.target.result;
                leftPreview.style.display = 'block';
                leftPreview.style.margin = '0 auto';
            }
            
            // 3. Ẩn dòng chữ "Không có ảnh" nếu đang hiển thị
            if (noImageText) {
                noImageText.style.display = 'none';
            }
        }
        reader.readAsDataURL(file);
    }
}
</script>
@endsection