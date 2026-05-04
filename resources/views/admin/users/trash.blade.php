@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Thùng rác user</h4>
            <a href="{{ route('users.list') }}" class="btn btn-secondary btn-sm">Quay lại danh sách</a>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table align-middle mb-0 table-hover table-centered">
                    <thead class="bg-light-subtle">
                        <tr>
                            <th>ID</th>
                            <th>Ảnh</th>
                            <th>Tên</th>
                            <th>Email</th>
                            <th>Vai trò</th>
                            <th>Số đơn</th>
                            <th>Ngày xóa</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                            <tr>
                                <td>{{ $user->id }}</td>
                                <td>
                                    <img
                                        src="{{ $user->avatar ? asset('storage/' . $user->avatar) : asset('images/placeholder-80x80.png') }}"
                                        alt="{{ $user->name }}"
                                        width="50"
                                        height="50"
                                        class="rounded border"
                                        style="object-fit:cover;"
                                    >
                                </td>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email }}</td>
                                <td>
                                    @php $role = $user->roles->first(); @endphp
                                    @if($role)
                                        <span class="badge bg-primary">{{ $role->name }}</span>
                                    @else
                                        <span class="badge bg-secondary">Chưa có role</span>
                                    @endif
                                </td>
                                <td>{{ $user->orders_count ?? 0 }}</td>
                                <td>{{ $user->deleted_at ? $user->deleted_at->format('d/m/Y H:i') : '' }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('users.restore', $user->id) }}"
                                           class="btn btn-success btn-sm"
                                           onclick="return confirm('Khôi phục người dùng này?')">
                                            Khôi phục
                                        </a>

                                        @if(($user->orders_count ?? 0) == 0)
                                            <form action="{{ route('users.forceDelete', $user->id) }}" method="POST"
                                                  onsubmit="return confirm('Xóa vĩnh viễn người dùng này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    Xóa vĩnh viễn
                                                </button>
                                            </form>
                                        @else
                                            <button type="button"
                                                    class="btn btn-secondary btn-sm"
                                                    disabled
                                                    title="Người dùng còn đơn hàng nên không thể xóa vĩnh viễn">
                                                Xóa vĩnh viễn
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">Không có user nào trong thùng rác</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection
