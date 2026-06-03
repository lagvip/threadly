<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\AssignRoleRequest;
use App\Http\Requests\Admin\Users\BanUserRequest;
use App\Http\Requests\Admin\Users\IndexUsersRequest;
use App\Http\Requests\Admin\Users\StoreUserRequest;
use App\Http\Requests\Admin\Users\UpdateUserRequest;
use App\Services\Admin\Users\AdminUserQueryService;
use App\Services\Admin\Users\AdminUserService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function index(IndexUsersRequest $request, AdminUserQueryService $users)
    {
        return view('admin.users.index', $users->indexData($request->validated()));
    }

    public function trash(AdminUserQueryService $users)
    {
        return view('admin.users.trash', $users->trashData());
    }

    public function show($id, AdminUserQueryService $users)
    {
        return view('admin.users.detail', $users->detailData((int) $id));
    }

    public function create(AdminUserQueryService $users)
    {
        return view('admin.users.add', $users->createData());
    }

    public function store(StoreUserRequest $request, AdminUserService $users)
    {
        try {
            $users->create($request->validated(), $request->file('avatar'));
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['role_id' => $e->getMessage()]);
        }

        return redirect()->route('users.list')->with('success', 'Thêm User thành công');
    }

    public function edit($id, AdminUserQueryService $users)
    {
        return view('admin.users.edit', $users->editData((int) $id));
    }

    public function update(UpdateUserRequest $request, $id, AdminUserService $users)
    {
        try {
            $users->update((int) $id, $request->validated(), $request->file('avatar'));
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['role_id' => $e->getMessage()]);
        }

        return redirect()->route('users.list')->with('success', 'Cập nhật User thành công');
    }

    public function destroy($id, AdminUserService $users)
    {
        try {
            $users->softDelete((int) $id);
        } catch (\RuntimeException $e) {
            return redirect()->route('users.list')->with('error', $e->getMessage());
        }

        return redirect()->route('users.list')->with('success', 'Đã chuyển User vào thùng rác');
    }

    public function restore($id, AdminUserService $users)
    {
        $users->restore((int) $id);

        return redirect()->route('users.trash')->with('success', 'Khôi phục User thành công');
    }

    public function forceDelete($id, AdminUserService $users)
    {
        try {
            $users->forceDelete((int) $id);
        } catch (\RuntimeException $e) {
            return redirect()->route('users.trash')->with('error', $e->getMessage());
        }

        return redirect()->route('users.trash')->with('success', 'Xóa vĩnh viễn User thành công');
    }

    public function search(IndexUsersRequest $request, AdminUserQueryService $users)
    {
        return view('admin.users.index', $users->indexData($request->validated()));
    }

    public function assignRole(AssignRoleRequest $request, $id, AdminUserService $users)
    {
        try {
            $users->assignRole((int) $id, (int) $request->role_id);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['role_id' => $e->getMessage()]);
        }

        return back()->with('success', 'Cập nhật quyền thành công!');
    }

    public function ban(BanUserRequest $request, $id, AdminUserService $users)
    {
        try {
            $users->ban(
                (int) $id,
                (string) $request->ban_reason_option,
                $request->input('ban_reason_custom'),
                Auth::id()
            );
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['ban_reason_custom' => $e->getMessage()]);
        }

        return redirect()->route('users.list')->with('success', 'Đã ban user thành công.');
    }

    public function unban($id, AdminUserService $users)
    {
        $users->unban((int) $id);

        return redirect()->route('users.list')->with('success', 'Đã bỏ ban user thành công.');
    }
}
