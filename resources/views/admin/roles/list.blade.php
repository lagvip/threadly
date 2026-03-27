@extends('admin.layouts.layout')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h4 class="card-title">Danh sách role</h4>
            <div class="d-flex gap-2">
                <a href="{{ route('roles.add') }}" class="btn btn-primary btn-sm">Thêm role</a>
                <a href="{{ route('roles.trash') }}" class="btn btn-soft-danger btn-sm">Thùng rác</a>
            </div>
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
                            <th>Tên role</th>
                            <th>Slug</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($roles as $role)
                            <tr>
                                <td>{{ $role->id }}</td>
                                <td>{{ $role->name }}</td>
                                <td>{{ $role->slug }}</td>
                                <td>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('roles.detail', $role->id) }}"
                                            class="btn btn-light btn-sm">
                                            <iconify-icon icon="solar:eye-broken"
                                                class="align-middle fs-18"></iconify-icon>
                                        </a>

                                        <a href="{{ route('roles.edit', $role->id) }}"
                                            class="btn btn-soft-primary btn-sm">
                                            <iconify-icon icon="solar:pen-2-broken"
                                                class="align-middle fs-18"></iconify-icon>
                                        </a>

                                        @if($role->slug !== 'admin')
                                            <form action="{{ route('roles.delete', $role->id) }}"
                                                method="POST"
                                                onsubmit="return confirm('Xóa role này?')"
                                                class="m-0">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-soft-danger btn-sm">
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
                                <td colspan="4" class="text-center">Không có dữ liệu</td>
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
