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

    .user-info-box {
        border: 1px solid #e9ecef;
        border-radius: 10px;
        padding: 12px;
        background: #fff;
    }

    .role-note {
        font-size: 13px;
        color: #6c757d;
    }

    .badge-role {
        font-size: 12px;
        padding: 6px 10px;
        border-radius: 20px;
    }
</style>

@section('content')
<div class="">
    <div class="container-fluid">
        <div class="row">
            <div class="col-xl-12">
                <form action="{{ route('users.update', $user->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    @php
                        $currentRole = $user->roles->first();
                        $currentRoleId = optional($currentRole)->id;
                        $currentRoleName = optional($currentRole)->name;

                        // truyền từ controller sang:
                        // $hasAdmin = RoleUser::whereHas('role', fn($q) => $q->where('name', 'admin'))->exists();
                        // hoặc check theo slug nếu bạn có slug
                    @endphp

                    {{-- Ảnh đại diện --}}
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Ảnh đại diện User</h4>
                        </div>
                        <div class="card-body">
                            <input type="file" name="avatar" id="avatar" class="form-control mb-3" accept="image/*">

                            <div class="text-center text-md-start">
                                <img
                                    id="avatar-preview"
                                    src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/placeholder-80x80.png') }}"
                                    alt="{{ $user->name }}"
                                    class="img-thumb"
                                >
                            </div>

                            @error('avatar')
                                <span class="text-danger d-block mt-2">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>

                    {{-- Thông tin user --}}
                    <div class="card mt-3">
                        <div class="card-header">
                            <h4 class="card-title">Thông tin User</h4>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-lg-4">
                                    <label class="form-label">Tên</label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}">
                                    @error('name')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                                    @error('email')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-lg-4">
                                    <label class="form-label">Password mới</label>
                                    <input type="password" name="password" class="form-control" placeholder="Bỏ trống nếu không đổi">
                                    @error('password')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="col-lg-6">
                                    <label class="form-label">Role</label>
                                    <select name="role_id" class="form-control">
                                        <option value="">-- Chọn role --</option>
                                        @foreach($roles as $r)
                                            @php
                                                $isAdminRole = strtolower($r->name) === 'admin';
                                                $isSelectedCurrentAdmin = $currentRoleId == $r->id && strtolower($currentRoleName) === 'admin';

                                                // Nếu đã có admin rồi thì khóa option admin
                                                // nhưng vẫn cho selected nếu chính user hiện tại đang là admin
                                                $disableAdminOption = !empty($hasAdmin) && $isAdminRole && !$isSelectedCurrentAdmin;
                                            @endphp

                                            <option
                                                value="{{ $r->id }}"
                                                {{ old('role_id', $currentRoleId) == $r->id ? 'selected' : '' }}
                                                {{ $disableAdminOption ? 'disabled' : '' }}
                                            >
                                                {{ $r->name }} {{ $disableAdminOption ? '(đã tồn tại)' : '' }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <div class="role-note mt-1">
                                        Chỉ được tồn tại 1 tài khoản admin trong hệ thống.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Nút hành động --}}
                    <div class="card mt-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-primary">Lưu</button>
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
