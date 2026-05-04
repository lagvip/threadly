@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Thùng rác role</h4>
            <a href="{{ route('roles.list') }}" class="btn btn-secondary btn-sm">Quay lại danh sách</a>
        </div>

        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">{{ session('error') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered align-middle">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tên role</th>
                            <th>Đường dẫn</th>
                            <th>Số user</th>
                            <th>Ngày xoá</th>
                            <th>Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->id }}</td>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->slug }}</td>
                                <td>{{ $role->users_count ?? 0 }}</td>
                                <td>{{ $role->deleted_at }}</td>
                                <td class="d-flex gap-2">
                                    <a href="{{ route('roles.restore', $role->id) }}"
                                       class="btn btn-success btn-sm"
                                       onclick="return confirm('Khôi phục vai trò này?')">
                                        Khôi phục
                                    </a>

                                    @if(($role->users_count ?? 0) == 0)
                                        <form action="{{ route('roles.forceDelete', $role->id) }}" method="POST"
                                              onsubmit="return confirm('Xóa vĩnh viễn vai trò này?')">
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
                                                title="Vai trò còn người dùng nên không thể xóa vĩnh viễn">
                                            Xóa vĩnh viễn
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Không có role nào trong thùng rác</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $roles->links() }}
        </div>
    </div>
</div>
@endsection
