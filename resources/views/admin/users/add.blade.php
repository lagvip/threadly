@extends('admin.layouts.layout')

<style>
    .img-thumb {
        width: 120px;
        height: 120px;
        object-fit: cover;
        border-radius: 10px;
        border: 1px solid #dee2e6;
        background: #f8f9fa;
    }

    .info-box {
        border: 1px solid #dee2e6;
        border-radius: 10px;
        padding: 14px;
        background: #fff;
        height: 100%;
    }

    .role-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid #e9ecef;
        border-radius: 8px;
        padding: 10px 12px;
        margin-bottom: 10px;
        background: #fff;
    }

    .role-badge {
        font-size: 12px;
        padding: 5px 10px;
        border-radius: 20px;
    }

    .upload-box {
        border: 1px dashed #ced4da;
        border-radius: 10px;
        padding: 20px;
        text-align: center;
        background: #fff;
    }

    .upload-box input[type="file"] {
        margin-top: 12px;
    }

    .form-note {
        font-size: 13px;
        color: #6c757d;
    }
</style>

@section('content')
<div class="">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <form action="{{ route('users.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    {{-- Ảnh đại diện --}}
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Thêm ảnh đại diện</h4>
                        </div>
                        <div class="card-body">
                            <div class="upload-box">
                                <div class="mb-3">
                                    <img
                                        id="avatar-preview"
                                        src="{{ asset('images/placeholder-80x80.png') }}"
                                        alt="Xem trước ảnh đại diện"
                                        class="img-thumb"
                                    >
                                </div>

                                <i class="bx bx-cloud-upload fs-48 text-primary"></i>
                                <h5 class="mt-3">Chọn ảnh đại diện cho user</h5>
                                <span class="text-muted d-block mb-2">
                                    Hỗ trợ tệp PNG, JPG, JPEG, WEBP
                                </span>

                                <input
                                    type="file"
                                    name="avatar"
                                    id="avatar"
                                    class="form-control"
                                    accept="image/*"
                                >

                                @error('avatar')
                                    <span class="text-danger d-block mt-2">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    {{-- Thông tin user --}}
                    <div class="card mt-3">
                        <div class="card-header">
                            <h4 class="card-title">Thông tin người dùng</h4>
                        </div>

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <label class="form-label">Tên</label>
                                    <input
                                        type="text"
                                        name="name"
                                        class="form-control"
                                        placeholder="Vui lòng nhập tên user"
                                        value="{{ old('name') }}"
                                    >
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label">Email</label>
                                    <input
                                        type="email"
                                        name="email"
                                        class="form-control"
                                        placeholder="Vui lòng nhập email"
                                        value="{{ old('email') }}"
                                    >
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label">Mật khẩu</label>
                                    <input
                                        type="password"
                                        name="password"
                                        class="form-control"
                                        placeholder="Vui lòng nhập mật khẩu"
                                    >
                                    @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label class="form-label">Vai trò</label>
                                    <select name="role_id" class="form-control">
                                        <option value="">-- Chọn role --</option>
                                        @foreach($roles as $r)
                                            @php
                                                $badgeClass = 'bg-secondary';
                                                if ($r->slug === 'admin') $badgeClass = 'bg-danger';
                                                if ($r->slug === 'manager') $badgeClass = 'bg-warning text-dark';
                                                if ($r->slug === 'customer') $badgeClass = 'bg-primary';
                                            @endphp

                                            <option value="{{ $r->id }}" {{ old('role_id') == $r->id ? 'selected' : '' }}>
                                                {{ $r->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                    {{-- Nút hành động --}}
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-primary">Thêm</button>
                                <a href="{{ route('users.list') }}" class="btn btn-secondary">Hủy</a>
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
    document.getElementById('avatar').addEventListener('change', function (e) {
        const file = e.target.files[0];
        const preview = document.getElementById('avatar-preview');

        if (file) {
            preview.src = URL.createObjectURL(file);
        }
    });
</script>
@endpush
@endsection
