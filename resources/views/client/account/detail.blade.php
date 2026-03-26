@extends('client.account._layout')

@section('account_content')
@php
    $avatarUrl = null;

    if (!empty($user->avatar)) {
        $avatarUrl = \Illuminate\Support\Str::startsWith($user->avatar, ['http://', 'https://'])
            ? $user->avatar
            : asset('storage/' . $user->avatar);
    }
@endphp

<style>
    .account-detail-card {
        border: 0;
        border-radius: 18px;
        box-shadow: 0 6px 24px rgba(15, 23, 42, .06);
        overflow: hidden;
    }

    .account-avatar-box {
        border-left: 1px solid #eef2f7;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
        background: #fff;
    }

    .account-avatar {
        width: 120px;
        height: 120px;
        border-radius: 999px;
        object-fit: cover;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        display: block;
        margin: 0 auto 14px;
    }

    .account-avatar-fallback {
        width: 120px;
        height: 120px;
        border-radius: 999px;
        background: #eef2f7;
        border: 1px solid #e2e8f0;
        color: #334155;
        font-size: 38px;
        font-weight: 700;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 14px;
    }

    .account-btn-primary {
        background: #ee4d2d !important;
        border: 1px solid #ee4d2d !important;
        color: #fff !important;
    }

    .account-btn-primary:hover {
        background: #d83f21 !important;
        border-color: #d83f21 !important;
        color: #fff !important;
    }

    .account-btn-secondary {
        background: #f8fafc !important;
        border: 1px solid #dbe2ea !important;
        color: #334155 !important;
    }

    .account-btn-secondary:hover {
        background: #eef2f7 !important;
        border-color: #cfd8e3 !important;
        color: #0f172a !important;
    }

    @media (max-width: 991.98px) {
        .account-avatar-box {
            border-left: 0;
            border-top: 1px solid #eef2f7;
        }
    }
</style>

<div class="card account-detail-card">
    <div class="card-body p-0">
        <div class="row g-0">
            <div class="col-lg-8">
                <div class="p-4">
                    <h4 class="mb-1">Thông tin chi tiết</h4>
                    <p class="text-muted mb-4">
                        Email để readonly, số điện thoại lấy từ địa chỉ mặc định hiện tại.
                    </p>

                    <form action="{{ route('client.account.update') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Họ và tên</label>
                                <input type="text"
                                       name="name"
                                       class="form-control @error('name') is-invalid @enderror"
                                       value="{{ old('name', $user->name) }}"
                                       placeholder="Nhập họ tên">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email"
                                       class="form-control"
                                       value="{{ $user->email }}"
                                       readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Số điện thoại mặc định</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ $defaultAddress->phone_number ?? 'Chưa có địa chỉ mặc định' }}"
                                       readonly>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Số lượng địa chỉ</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ $addressCount }}"
                                       readonly>
                            </div>

                            <div class="col-12">
                                <label class="form-label">Địa chỉ mặc định</label>
                                <textarea class="form-control" rows="3" readonly>{{ $defaultAddress?->full_address ?? 'Chưa có địa chỉ mặc định' }}</textarea>
                            </div>

                            <div class="col-12">
                                <div class="mt-3 d-flex flex-wrap gap-2">
                                    <button type="submit" class="btn account-btn-primary rounded-pill px-4">
                                        Lưu thay đổi
                                    </button>

                                    <a href="{{ route('client.addresses.index') }}"
                                       class="btn account-btn-secondary rounded-pill px-4">
                                        Quản lý địa chỉ
                                    </a>
                                </div>
                            </div>
                        </div>

                        {{-- input file đặt trong form để submit được --}}
                        <input type="file"
                               id="avatar"
                               name="avatar"
                               accept="image/*"
                               class="d-none"
                               onchange="previewAvatar(event)">
                    </form>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="account-avatar-box">
                    <div class="text-center w-100">
                        @if($avatarUrl)
                            <img src="{{ $avatarUrl }}"
                                 alt="{{ $user->name }}"
                                 class="account-avatar"
                                 id="avatarPreview">
                        @else
                            <img src="https://placehold.co/120x120?text=Avatar"
                                 alt="{{ $user->name }}"
                                 class="account-avatar"
                                 id="avatarPreview">
                        @endif

                        <div class="fw-semibold mb-1">{{ $user->name }}</div>
                        <div class="text-muted small mb-3">{{ $user->email }}</div>

                        <label for="avatar" class="btn account-btn-secondary rounded-pill px-4">
                            Chọn ảnh mới
                        </label>

                        <div class="text-muted small mt-2">
                            JPG, PNG, WEBP. Tối đa 2MB.
                        </div>

                        @error('avatar')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewAvatar(event) {
    const file = event.target.files[0];
    if (!file) return;

    const preview = document.getElementById('avatarPreview');
    preview.src = URL.createObjectURL(file);
}
</script>
@endsection
