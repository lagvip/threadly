@extends('admin.layouts.layout')

<style>
    .table td,
    .table th {
        vertical-align: middle;
    }

    .table-status-cell {
        min-width: 220px;
    }

    .table-status-switch {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        white-space: nowrap;
        margin: 0;
    }

    .table-status-switch .form-check {
        margin-bottom: 0;
    }

    .table-status-switch .form-check-input {
        width: 2.75rem;
        height: 1.35rem;
        cursor: pointer;
    }

    .table-status-text {
        font-size: 13px;
        font-weight: 600;
        color: #198754;
        white-space: nowrap;
        min-width: 88px;
        margin: 0;
    }

    .table-status-text.inactive {
        color: #dc3545;
    }

    .status-reason {
        margin-top: 6px;
        font-size: 12px;
        color: #6c757d;
        white-space: normal;
        line-height: 1.4;
        max-width: 220px;
    }

    .action-cell {
        min-width: 150px;
    }

    .filter-bar .form-control {
        min-width: 200px;
    }
</style>

@section('content')
<div class="container-fluid">

    <div class="row">
        <div class="col-xl-12">

            {{-- Filter role --}}
            <div class="mb-3 d-flex gap-2 flex-wrap">
                <a href="{{ route('users.list') }}"
                   class="btn btn-sm {{ empty($role) ? 'btn-primary' : 'btn-outline-primary' }}">
                    Tất cả
                </a>

                @foreach ($roles as $item)
                    <a href="{{ route('users.list', ['role' => $item->slug]) }}"
                       class="btn btn-sm {{ ($role ?? '') === $item->slug ? 'btn-primary' : 'btn-outline-primary' }}">
                        {{ $item->name }}
                    </a>
                @endforeach
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center gap-1 flex-wrap">
                    <h4 class="card-title flex-grow-1 mb-0">Danh sách người dùng</h4>

                    <div class="d-flex align-items-center gap-2 flex-wrap filter-bar">
                        <a href="{{ route('users.trash') }}" class="btn btn-soft-danger btn-sm">Thùng rác</a>

                        <a href="{{ route('users.add') }}" class="btn btn-sm btn-primary">
                            Thêm người dùng
                        </a>

                        <form action="{{ route('users.search') }}" method="GET" class="d-flex align-items-center gap-2 flex-wrap">
                            @if(!empty($role))
                                <input type="hidden" name="role" value="{{ $role }}">
                            @endif

                            <input name="keyword"
                                   type="search"
                                   class="form-control form-control-sm"
                                   placeholder="Tìm theo tên hoặc email..."
                                   value="{{ $keyword ?? '' }}"
                                   style="width: 250px;">

                            <button class="btn btn-sm btn-outline-primary" type="submit">Tìm</button>
                        </form>
                    </div>
                </div>

                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover table-centered">
                            <thead class="bg-light-subtle">
                                <tr>
                                    <th>STT</th>
                                    <th>Người dùng</th>
                                    <th>Email</th>
                                    <th>Vai trò</th>
                                    <th>Trạng thái</th>
                                    <th>Số đơn</th>
                                    <th>Ngày tạo</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($users as $user)
                                    @php
                                        $avatar = $user->avatar;

                                        if (!empty($avatar) && filter_var($avatar, FILTER_VALIDATE_URL)) {
                                            $avatarUrl = $avatar;
                                        } elseif (!empty($avatar)) {
                                            $avatarUrl = asset('storage/' . $avatar);
                                        } else {
                                            $avatarUrl = asset('admin/assets/images/users/avatar-1.jpg');
                                        }

                                        $isBanned = (int)($user->status ?? 1) === 0;
                                    @endphp

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>

                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded bg-light avatar-md d-flex align-items-center justify-content-center overflow-hidden">
                                                    <img src="{{ $avatarUrl }}"
                                                         alt="{{ $user->name }}"
                                                         class="avatar-md object-fit-cover"
                                                         onerror="this.onerror=null;this.src='{{ asset('admin/assets/images/users/avatar-1.jpg') }}';">
                                                </div>
                                                <div>
                                                    <span class="text-dark fw-medium fs-15">{{ $user->name }}</span>
                                                </div>
                                            </div>
                                        </td>

                                        <td>{{ $user->email }}</td>

                                        <td>
                                            @if($user->roles->count())
                                                {{ $user->roles->pluck('name')->join(', ') }}
                                            @else
                                                <span class="text-muted">Chưa có role</span>
                                            @endif
                                        </td>

                                        <td class="table-status-cell">
                                            <div class="table-status-switch">
                                                <form action="{{ route('users.unban', $user->id) }}"
                                                      method="POST"
                                                      class="d-none"
                                                      id="unban-form-{{ $user->id }}">
                                                    @csrf
                                                    @method('PATCH')
                                                </form>

                                                <div class="form-check form-switch">
                                                    <input class="form-check-input ban-toggle"
                                                           type="checkbox"
                                                           role="switch"
                                                           id="banToggle{{ $user->id }}"
                                                           data-user-id="{{ $user->id }}"
                                                           data-modal-target="banModal{{ $user->id }}"
                                                           {{ !$isBanned ? 'checked' : '' }}>
                                                </div>

                                                <span class="table-status-text {{ !$isBanned ? '' : 'inactive' }}"
                                                      id="banToggleLabel{{ $user->id }}">
                                                    {{ !$isBanned ? 'Hoạt động' : 'Bị chặn' }}
                                                </span>
                                            </div>

                                            @if($isBanned && !empty($user->ban_reason))
                                                <div class="status-reason">
                                                    {{ $user->ban_reason }}
                                                </div>
                                            @endif
                                        </td>

                                        <td>{{ $user->orders_count ?? 0 }}</td>

                                        <td>{{ $user->created_at }}</td>

                                        <td class="action-cell">
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('users.detail', $user->id) }}"
                                                   class="btn btn-light btn-sm">
                                                    <iconify-icon icon="solar:eye-broken"
                                                        class="align-middle fs-18"></iconify-icon>
                                                </a>

                                                <a href="{{ route('users.edit', $user->id) }}"
                                                   class="btn btn-soft-primary btn-sm">
                                                    <iconify-icon icon="solar:pen-2-broken"
                                                        class="align-middle fs-18"></iconify-icon>
                                                </a>

                                                @if(($user->orders_count ?? 0) == 0)
                                                    <form action="{{ route('users.delete', $user->id) }}" method="POST" class="m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button onclick="return confirm('Bạn chắc chắn muốn xóa người dùng này?')"
                                                                type="submit"
                                                                class="btn btn-soft-danger btn-sm">
                                                            <iconify-icon icon="solar:trash-bin-minimalistic-2-broken"
                                                                class="align-middle fs-18"></iconify-icon>
                                                        </button>
                                                    </form>
                                                @else
                                                    <button type="button"
                                                            class="btn btn-secondary btn-sm"
                                                            disabled
                                                            title="Người dùng còn đơn hàng nên không thể xóa">
                                                        <iconify-icon icon="solar:trash-bin-minimalistic-2-broken"
                                                            class="align-middle fs-18"></iconify-icon>
                                                    </button>
                                                @endif
                                            </div>

                                            {{-- Modal chặn --}}
                                            <div class="modal fade ban-modal" id="banModal{{ $user->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <form action="{{ route('users.ban', $user->id) }}" method="POST" class="modal-content ban-form">
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
                                                                <textarea name="ban_reason_custom"
                                                                          class="form-control"
                                                                          rows="3"
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
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">Không có dữ liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.ban-toggle').forEach(function (toggle) {
        updateToggleLabel(toggle);

        toggle.addEventListener('change', function () {
            const userId = this.dataset.userId;
            const modalId = this.dataset.modalTarget;

            if (this.checked) {
                const ok = confirm('Bỏ chặn người dùng này?');

                if (ok) {
                    document.getElementById('unban-form-' + userId).submit();
                } else {
                    this.checked = false;
                    updateToggleLabel(this);
                }

                return;
            }

            updateToggleLabel(this);

            if (typeof bootstrap === 'undefined') {
                alert('Không tìm thấy Bootstrap Modal để mở hộp thoại chặn.');
                this.checked = true;
                updateToggleLabel(this);
                return;
            }

            const modalEl = document.getElementById(modalId);
            if (!modalEl) {
                this.checked = true;
                updateToggleLabel(this);
                return;
            }

            modalEl.dataset.toggleId = this.id;

            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        });
    });

    document.querySelectorAll('.ban-modal').forEach(function (modalEl) {
        const form = modalEl.querySelector('.ban-form');

        if (form) {
            form.dataset.submitted = '0';

            form.addEventListener('submit', function () {
                this.dataset.submitted = '1';
            });
        }

        modalEl.addEventListener('hidden.bs.modal', function () {
            const toggleId = this.dataset.toggleId;
            const form = this.querySelector('.ban-form');

            if (toggleId && form && form.dataset.submitted !== '1') {
                const toggle = document.getElementById(toggleId);
                if (toggle) {
                    toggle.checked = true;
                    updateToggleLabel(toggle);
                }
            }

            if (form) {
                form.dataset.submitted = '0';

                const select = form.querySelector('select[name="ban_reason_option"]');
                const textarea = form.querySelector('textarea[name="ban_reason_custom"]');

                if (select) {
                    select.value = '';
                }

                if (textarea) {
                    textarea.value = '';
                }
            }

            const customBox = this.querySelector('[id^="custom_reason_"]');
            if (customBox) {
                customBox.classList.add('d-none');
            }

            delete this.dataset.toggleId;
        });
    });
});

function toggleCustomReason(select, targetId) {
    const box = document.getElementById(targetId);
    if (!box) return;

    if (select.value === 'custom') {
        box.classList.remove('d-none');
    } else {
        box.classList.add('d-none');
    }
}

function updateToggleLabel(toggle) {
    const label = document.getElementById('banToggleLabel' + toggle.dataset.userId);
    if (!label) return;

    if (toggle.checked) {
        label.textContent = 'Hoạt động';
        label.classList.remove('inactive');
    } else {
        label.textContent = 'Bị chặn';
        label.classList.add('inactive');
    }
}
</script>
@endsection
