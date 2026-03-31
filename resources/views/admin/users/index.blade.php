@extends('admin.layouts.layout')

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
                    <h4 class="card-title flex-grow-1 mb-0">Danh sách Users</h4>
                    <a href="{{ route('users.trash') }}" class="btn btn-soft-danger btn-sm">Thùng rác</a>
                    <a href="{{ route('users.add') }}" class="btn btn-sm btn-primary">
                        Thêm User
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

                <div>
                    <div class="table-responsive">
                        <table class="table align-middle mb-0 table-hover table-centered">
                            <thead class="bg-light-subtle">
                                <tr>
                                    <th style="width: 20px;">
                                        <div class="form-check ms-1">
                                            <input type="checkbox" class="form-check-input" id="checkAll">
                                            <label class="form-check-label" for="checkAll"></label>
                                        </div>
                                    </th>
                                    <th>STT</th>
                                    <th>Người dùng</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Ngày tạo</th>
                                    <th>Action</th>
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
                                    @endphp

                                    <tr>
                                        <td>
                                            <div class="form-check ms-1">
                                                <input type="checkbox" class="form-check-input row-check"
                                                       value="{{ $user->id }}">
                                                <label class="form-check-label"></label>
                                            </div>
                                        </td>

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

                                        <td>{{ $user->created_at }}</td>

                                        <td>
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

                                                @if(!$user->isAdmin())
                                                    <form action="{{ route('users.delete', $user->id) }}" method="POST" class="m-0">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button onclick="return confirm('Bạn chắc chắn muốn xóa user này?')"
                                                                type="submit"
                                                                class="btn btn-soft-danger btn-sm">
                                                            <iconify-icon icon="solar:trash-bin-minimalistic-2-broken"
                                                                class="align-middle fs-18"></iconify-icon>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">Không có dữ liệu</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="p-3">
                    {{ $users->links() }}
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const checkAll = document.getElementById('checkAll');
    const rowChecks = () => Array.from(document.querySelectorAll('.row-check'));

    if (!checkAll) return;

    checkAll.addEventListener('change', function () {
        rowChecks().forEach(cb => cb.checked = checkAll.checked);
        checkAll.indeterminate = false;
    });

    document.addEventListener('change', function (e) {
        if (!e.target.classList.contains('row-check')) return;

        const all = rowChecks();
        const checkedCount = all.filter(cb => cb.checked).length;

        checkAll.checked = checkedCount === all.length && all.length > 0;
        checkAll.indeterminate = checkedCount > 0 && checkedCount < all.length;
    });
});
</script>
@endsection
