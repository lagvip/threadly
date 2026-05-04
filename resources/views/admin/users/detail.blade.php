@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-xl-12">
            @php
                $avatar = $user->avatar;
                $isBanned = (int)($user->status ?? 1) === 0;

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
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="card-title mb-0">Thông tin người dùng</h4>

                    <div class="d-flex gap-2 flex-wrap">
                        @if(!$isBanned)
                            <button type="button"
                                    class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#banModal{{ $user->id }}">
                                Chặn tài khoản
                            </button>
                        @else
                            <form action="{{ route('users.unban', $user->id) }}" method="POST" class="m-0">
                                @csrf
                                @method('PATCH')
                                <button type="submit"
                                        onclick="return confirm('Bỏ chặn người dùng này?')"
                                        class="btn btn-success btn-sm">
                                    Bỏ chặn
                                </button>
                            </form>
                        @endif

                        <a href="{{ route('users.list') }}" class="btn btn-secondary btn-sm">Quay lại</a>
                    </div>
                </div>

                <div class="card-body">
                    {{-- ID + Tên --}}
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">ID</label>
                                <input type="text" class="form-control" value="{{ $user->id }}" readonly>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Tên</label>
                                <input type="text" class="form-control" value="{{ $user->name }}" readonly>
                            </div>
                        </div>
                    </div>

                    {{-- Email + Vai trò --}}
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="text" class="form-control" value="{{ $user->email }}" readonly>
                            </div>
                        </div>

                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Vai trò</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ $user->roles->count() ? $user->roles->pluck('name')->join(', ') : 'Chưa có role' }}"
                                       readonly>
                            </div>
                        </div>
                    </div>

                    {{-- Trạng thái + ngày tạo --}}
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="mb-3">
                                <label class="form-label">Trạng thái</label>
                                <input type="text"
                                       class="form-control"
                                       value="{{ $isBanned ? 'Bị chặn' : 'Hoạt động' }}"
                                       readonly>
                            </div>
                        </div>

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

                    {{-- Thông tin ban --}}
                    @if($isBanned)
                        <div class="row">
                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Lý do chặn</label>
                                    <textarea class="form-control" rows="4" readonly>{{ $user->ban_reason ?? 'Không có lý do' }}</textarea>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="mb-3">
                                    <label class="form-label">Thời gian chặn</label>
                                    <input type="text"
                                           class="form-control"
                                           value="{{ $user->banned_at ?? 'Chưa có' }}"
                                           readonly>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Người chặn</label>
                                    <input type="text"
                                           class="form-control"
                                           value="{{ optional($user->bannedBy)->name ?? 'Không xác định' }}"
                                           readonly>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="p-3 bg-light mb-3 rounded">
                        <div class="row justify-content-end g-2">
                            <div class="col-auto">
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">Sửa user</a>
                            </div>
                            <div class="col-auto">
                                <a href="{{ route('users.list') }}" class="btn btn-secondary">Quay lại</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Modal ban --}}
            <div class="modal fade" id="banModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <form action="{{ route('users.ban', $user->id) }}" method="POST" class="modal-content">
                        @csrf
                        @method('PATCH')

                        <div class="modal-header">
                            <h5 class="modal-title">Chặn user: {{ $user->name }}</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Chọn lý do chặn</label>
                                <select name="ban_reason_option"
                                        class="form-control"
                                        onchange="toggleCustomReason(this, 'custom_reason_{{ $user->id }}')">
                                    <option value="">-- Chọn lý do --</option>
                                    <option value="Spam / lạm dụng">Spam / lạm dụng</option>
                                    <option value="Gian lận đơn hàng">Gian lận đơn hàng</option>
                                    <option value="Vi phạm nội quy">Vi phạm nội quy</option>
                                    <option value="custom">Khác</option>
                                </select>
                            </div>

                            <div class="mb-3 d-none" id="custom_reason_{{ $user->id }}">
                                <label class="form-label">Nhập lý do khác</label>
                                <textarea name="ban_reason_custom" class="form-control" rows="3"
                                          placeholder="Nhập lý do chặn..."></textarea>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="submit" class="btn btn-warning">Xác nhận chặn</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Hủy</button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
function toggleCustomReason(select, targetId) {
    const box = document.getElementById(targetId);
    if (!box) return;

    if (select.value === 'custom') {
        box.classList.remove('d-none');
    } else {
        box.classList.add('d-none');
    }
}
</script>
@endsection
