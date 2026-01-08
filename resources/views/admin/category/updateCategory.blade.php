@extends('admin.layouts.layout')
@section('content')
    <div class="container-xxl">

        <div class="row">
            <div class="col-xl-3 col-lg-4">
                <div class="card">
                    <div class="card-body">
                        <div class="bg-light text-center rounded bg-light">
                            {{-- Hiển thị ảnh hiện có --}}
                            @if ($category['image'])
                                <img src="{{ Storage::url($category['image']) }}" alt="Current Image" class="avatar-xxl"
                                    id="leftImagePreview"> {{-- Thêm ID cho ảnh bên trái --}}
                            @else
                                <p>Không có ảnh hiện tại.</p>
                                <img src="" alt="Image Placeholder" class="avatar-xxl" id="leftImagePreview"
                                    style="display: none;"> {{-- Placeholder nếu không có ảnh --}}
                            @endif
                        </div>
                        <div class="mt-3">
                            <h4>{{ $category['name'] }}</h4>
                            <div class="row">
                                <div class="col-lg-4 col-4">
                                    <p class="mb-1 mt-2">Name:</p>
                                    <h5 class="mb-0">{{ $category['name'] }}</h5>
                                </div>


                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-9 col-lg-8 ">
                <form action="{{ route('listCategory.updateCategory', $category) }}" method="POST"
                    enctype="multipart/form-data" id="updateCategoryForm"> {{-- Đổi ID form --}}
                    @csrf
                    @method('PUT')
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Cập nhật ảnh</h4> {{-- Đổi tiêu đề --}}
                        </div>
                        <div class="card-body">
                            <div class="image-upload-wrapper" id="imageUploadWrapperUpdate"> {{-- Đổi ID --}}
                                {{-- Input file thực tế, sẽ được ẩn đi --}}
                                <input type="file" name="image" id="actualImageInputUpdate" class="hidden-input"
                                    accept="image/*" />

                                <div class="dz-message needsclick">
                                    <i class="bx bx-cloud-upload fs-48 text-primary"></i>
                                    <h3 class="mt-4">Kéo ảnh mới vào đây, hoặc <span class="text-primary">nhấp để
                                            duyệt</span></h3> {{-- Đổi text --}}
                                    <span class="text-muted fs-13">
                                        1600 x 1200 (4:3) khuyến nghị. Chỉ cho phép file PNG, JPG và GIF.
                                    </span>
                                    
                                    <p id="selectedFileNameUpdate" class="selected-file-name mt-2"></p>
                                    <div id="imagePreviewUpdate" class="image-preview mt-3">
                                        {{-- Hiển thị ảnh xem trước mặc định nếu có ảnh cũ --}}
                                        @if ($category['image'])
                                            <img src="{{ Storage::url($category['image']) }}" alt="Current Image"
                                                style="max-width: 100%; max-height: 120px; display: block; margin: 0 auto; border-radius: 4px;">
                                        @endif
                                    </div>
                                    
                                </div>
                                
                            </div>
                        </div>
                       
                    </div>
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Cập nhật danh mục</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-lg-6">

                                    <div class="mb-3">
                                        <label for="category-title" class="form-label">Tên</label>
                                        <input type="text" name="name" value="{{ $category['name'] }}"
                                            id="category-title" class="form-control" placeholder="Enter Name">
                                        @if ($errors->has('name'))
                                            <span style="color: red;">{{ $errors->first('name') }}</span>
                                        @endif
                                    </div>

                                </div>
                                <div class="col-lg-6">




                                </div>

                            </div>
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label for="category-parent" class="form-label">Danh mục cha</label>
                                    <select name="id_parent" id="category-parent" class="form-control">
                                        <option value="">-- Không có danh mục cha --</option>
                                        @foreach ($allCategories as $cat)
                                            <option value="{{ $cat->id }}"
                                                {{ $category->id_parent == $cat->id ? 'selected' : '' }}>
                                                {{ $cat->name }}
                                            </option>
                                        @endforeach
                                    </select>
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
                                <a href="{{ route('listCategory.list') }}" class="btn btn-primary w-100">Hủy</a>
                                {{-- Đổi link hủy --}}
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    </div>
@endsection
