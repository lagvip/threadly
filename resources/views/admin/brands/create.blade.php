@extends('admin.layouts.layout')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4">Thêm thương hiệu</h2>
    
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('brands.store') }}" enctype="multipart/form-data">
        @csrf
        <div class="card">
            <div class="card-header">
                <h4 class="card-title">Thêm ảnh thương hiệu</h4>
            </div>
            <div class="card-body">
                <div class="image-upload-wrapper" id="imageUploadWrapper" 
                    style="border: 2px dashed #d1d5db; border-radius: 8px; padding: 30px; text-align: center; cursor: pointer; transition: all 0.3s ease;">
                    
                    {{-- Input file thực tế, được ẩn đi --}}
                    <input type="file" name="image" id="actualImageInput" style="display: none;" accept="image/*" />

                    <div class="dz-message needsclick">
                        <i class="bx bx-cloud-upload fs-48 text-primary" style="font-size: 48px;"></i>
                        <h3 class="mt-4">Kéo ảnh vào đây, hoặc <span class="text-primary">nhấp để duyệt</span></h3>
                        <span class="text-muted fs-13">
                            1600 x 1200 (4:3) khuyến nghị. Chỉ cho phép file PNG, JPG và GIF.
                        </span>
                        
                        {{-- Hiển thị tên file và ảnh xem trước --}}
                        <p id="selectedFileName" class="selected-file-name mt-2 text-success fw-bold"></p>
                        <div id="imagePreview" class="image-preview mt-3">
                            <img id="img-render" src="#" alt="Preview" style="max-width: 200px; border-radius: 8px; display: none; margin: 0 auto;">
                        </div>
                    </div>

                    @error('image')
                        <span class="text-danger mt-2 d-block">{{ $message }}</span>
                    @enderror
                </div>
            </div>
        </div>
        <div class="mb-3">
            <label for="name" class="form-label">Tên thương hiệu</label>
            <input type="text" name="name" id="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" required>
            @error('name')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <button type="submit" class="btn btn-primary">Tạo mới</button>
        <a href="{{ route('brands.index') }}" class="btn btn-secondary">Quay lại</a>
    </form>
</div>

<script>
    function previewImage(event) {
        const reader = new FileReader();
        reader.onload = function() {
            const output = document.getElementById('img-preview');
            output.src = reader.result;
            output.style.display = 'block';
        };
        reader.readAsDataURL(event.target.files[0]);
    }
</script>
@endsection