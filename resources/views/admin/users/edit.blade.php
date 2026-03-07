@extends('admin.layouts.layout')

@section('content')
<div class="container-xxl">
    <form action="{{ route('users.update', $user->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Cập nhật User</h4>
            </div>

            <div class="card-body">
                <div class="row">
                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Tên</label>
                        <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}">
                        @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}">
                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Password mới (bỏ trống nếu không đổi)</label>
                        <input type="password" name="password" class="form-control">
                        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-lg-6 mb-3">
                        <label class="form-label">Role</label>
                        @php
                            $currentRoleId = optional($user->roles->first())->id;
                        @endphp
                        <select name="role_id" class="form-select">
                            <option value="">-- Chọn role --</option>
                            @foreach($roles as $r)
                                <option value="{{ $r->id }}" {{ old('role_id', $currentRoleId) == $r->id ? 'selected' : '' }}>
                                    {{ $r->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('role_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
            </div>
        </div>

        <div class="p-3 bg-light mb-3 rounded">
            <div class="row justify-content-end g-2">
                <div class="col-lg-2">
                    <button type="submit" class="btn btn-outline-secondary w-100">Lưu</button>
                </div>
                <div class="col-lg-2">
                    <a href="{{ route('users.list') }}" class="btn btn-primary w-100">Hủy</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
