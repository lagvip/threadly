@extends('admin.layouts.layout')

@section('content')
<div class="container-xxl">

    {{-- Filter role --}}
    <div class="mb-3 d-flex gap-2">
        <a href="{{ route('users.list') }}"
           class="btn btn-sm {{ empty($role) ? 'btn-primary' : 'btn-outline-primary' }}">
            Tất cả
        </a>

        <a href="{{ route('users.list', ['role' => 'admin']) }}"
           class="btn btn-sm {{ ($role ?? '') === 'admin' ? 'btn-primary' : 'btn-outline-primary' }}">
            Admin
        </a>

        <a href="{{ route('users.list', ['role' => 'staff']) }}"
           class="btn btn-sm {{ ($role ?? '') === 'staff' ? 'btn-primary' : 'btn-outline-primary' }}">
            Staff
        </a>

        <a href="{{ route('users.list', ['role' => 'customer']) }}"
           class="btn btn-sm {{ ($role ?? '') === 'customer' ? 'btn-primary' : 'btn-outline-primary' }}">
            Customer
        </a>
    </div>

    <div class="card">
        <div class="card-header d-flex align-items-center">
            <h4 class="card-title mb-0">Danh sách Users</h4>

            <div class="ms-auto d-flex align-items-center gap-2">
                <a href="{{ route('users.add') }}" class="btn btn-sm btn-primary">
                    Thêm User
                </a>

                <form action="{{ route('users.search') }}" method="GET" class="d-flex align-items-center gap-2">
                    @if(!empty($role))
                        <input type="hidden" name="role" value="{{ $role }}">
                    @endif

                    <input name="keyword" type="search" class="form-control"
                        placeholder="Tìm theo tên hoặc email..."
                        value="{{ $keyword ?? '' }}" style="min-width: 260px;">

                    <button class="btn btn-sm btn-outline-primary" type="submit">Tìm</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 table-hover table-centered">
                <thead class="bg-light-subtle">
                    <tr>
                        <th>ID</th>
                        <th>Tên</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Ngày tạo</th>
                        <th style="width: 160px;">Hành động</th>
                    </tr>
                </thead>

                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>#{{ $user->id }}</td>
                            <td class="fw-medium">{{ $user->name }}</td>
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
                                    <a href="{{ route('users.detail', $user->id) }}" class="btn btn-light btn-sm">
                                        Xem
                                    </a>
                                    <a href="{{ route('users.edit', $user->id) }}" class="btn btn-soft-primary btn-sm">
                                        Sửa
                                    </a>
                                    <form action="{{ route('users.delete', $user->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button onclick="return confirm('Bạn chắc chắn muốn xóa user này?')"
                                                type="submit"
                                                class="btn btn-soft-danger btn-sm">
                                            Xóa
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Không có dữ liệu</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="card-footer border-top">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
