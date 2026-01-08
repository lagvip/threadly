@extends('admin.layouts.layout')
@section('content')
    <div class="container-xxl">
        <div class="">
            <form action="{{ route('listCategory.storeCategory') }}" method="POST" enctype="multipart/form-data"
                id="categoryForm">
                @csrf
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Thêm ảnh</h4>
                    </div>
                    <div class="card-body">
                        <div class="image-upload-wrapper" id="imageUploadWrapper">
                            {{-- Input file thực tế, sẽ được ẩn đi --}}
                            <input type="file" name="image" id="actualImageInput" class="hidden-input"
                                accept="image/*" />

                            <div class="dz-message needsclick">
                                <i class="bx bx-cloud-upload fs-48 text-primary"></i>
                                <h3 class="mt-4">Kéo ảnh vào đây, hoặc <span class="text-primary">nhấp để duyệt</span>
                                </h3>
                                <span class="text-muted fs-13">
                                    1600 x 1200 (4:3) khuyến nghị. Chỉ cho phép file PNG, JPG và GIF.
                                </span>
                                <p id="selectedFileName" class="selected-file-name mt-2"></p>
                                <div id="imagePreview" class="image-preview mt-3"></div>
                            </div>
                            @if ($errors->has('image'))
                                <span style="color: red;">{{ $errors->first('image') }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card">
                    <div class="card-header">
                        <h4 class="card-title">Thêm danh mục</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-6">

                                <div class="mb-3">
                                    <label for="category-title" class="form-label">Tên</label>
                                    <input type="text" name="name" id="category-title" class="form-control"
                                        placeholder="Nhập tên" value="{{ old('name', $category->name ?? '') }}">
                                    @if ($errors->has('name'))
                                        <span style="color: red;">{{ $errors->first('name') }}</span>
                                    @endif
                                </div>

                            </div>


                        </div>
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label for="category-parent" class="form-label">Danh mục cha</label>
                                <select name="id_parent" id="category-parent" class="form-control">
                                    <option value="">-- Không có danh mục cha --</option>
                                    @foreach ($categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-light mb-3 rounded">
                    <div class="row justify-content-end g-2">
                        <div class="col-lg-2">
                            <button type="submit" class="btn btn-outline-secondary w-100">Thêm</button>
                        </div>
                        <div class="col-lg-2">
                            <a href="{{ route('listCategory.list') }}" class="btn btn-primary w-100">Hủy</a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
