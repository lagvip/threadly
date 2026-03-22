@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-12">
            @php
                $avatar = $user->avatar;

                if (!empty($avatar) && filter_var($avatar, FILTER_VALIDATE_URL)) {
                    $avatarUrl = $avatar;
                } elseif (!empty($avatar)) {
                    $avatarUrl = asset('storage/' . $avatar);
                } else {
                    $avatarUrl = asset('admin/assets/images/users/avatar-1.jpg');
                }
            @endphp

            {{-- Ảnh đại diện --}}
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Ảnh đại diện</h4>
                </div>
                <div class="card-body">
                    <div class="fallback">
                        <img src="{{ $avatarUrl }}"
                             alt="{{ $user->name }}"
                             width="100"
                             class="mt-2 img-thumbnail"
                             onerror="this.onerror=null;this.src='{{ asset('admin/assets/images/users/avatar-1.jpg') }}';">
                    </div>
                </div>
            </div>

            {{-- Thông tin user --}}
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">Thông tin User</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        {{-- ID --}}
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">ID</label>
                                <input type="text" class="form-control" value="{{ $user->id }}" readonly>
                            </div>
                        </div>

                        {{-- Tên --}}
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Tên</label>
                                <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Email --}}
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" class="form-control" value="{{ $user->email }}" readonly>
                            </div>
                        </div>

                        {{-- Role --}}
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Role</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ $user->roles->count() ? $user->roles->pluck('name')->join(', ') : 'Chưa có role' }}"
                                       readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        {{-- Trạng thái --}}
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ $user->status ?? 'Chưa cập nhật' }}"
                                       readonly>
                            </div>
                        </div>

                        {{-- Ngày tạo --}}
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Ngày tạo</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ $user->created_at }}"
                                       readonly>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-light mb-3 rounded">
                        <div class="row justify-content-end g-2">
                            <div class="col-lg-1">
                                <a href="{{ route('users.list') }}" class="btn btn-primary w-100">Quay lại</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
