<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Users\AssignRoleRequest;
use App\Http\Requests\Admin\Users\BanUserRequest;
use App\Http\Requests\Admin\Users\IndexUsersRequest;
use App\Http\Requests\Admin\Users\StoreUserRequest;
use App\Http\Requests\Admin\Users\UpdateUserRequest;
use App\Models\User;
use App\Services\Admin\Users\AdminUserQueryService;
use App\Services\Admin\Users\AdminUserService;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function __construct(
        protected AdminUserQueryService $queries,
        protected AdminUserService $users,
    ) {}

    public function index(IndexUsersRequest $request)
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', $this->queries->indexData($request->validated()));
    }

    public function trash()
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.trash', $this->queries->trashData());
    }

    public function show($id)
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.detail', $this->queries->detailData((int) $id));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        return view('admin.users.add', $this->queries->createData());
    }

    public function store(StoreUserRequest $request)
    {
        $this->authorize('create', User::class);

        try {
            $this->users->create($request->validated(), $request->file('avatar'));
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['role_id' => $e->getMessage()]);
        }

        return redirect()->route('users.list')->with('success', 'Thêm User thành công');
    }

    public function edit($id)
    {
        $this->authorize('updateAny', User::class);

        return view('admin.users.edit', $this->queries->editData((int) $id));
    }

    public function update(UpdateUserRequest $request, $id)
    {
        $this->authorize('updateAny', User::class);

        try {
            $this->users->update((int) $id, $request->validated(), $request->file('avatar'));
        } catch (\RuntimeException $e) {
            return back()->withInput()->withErrors(['role_id' => $e->getMessage()]);
        }

        return redirect()->route('users.list')->with('success', 'Cập nhật User thành công');
    }

    public function destroy($id)
    {
        $this->authorize('deleteAny', User::class);

        try {
            $this->users->softDelete((int) $id);
        } catch (\RuntimeException $e) {
            return redirect()->route('users.list')->with('error', $e->getMessage());
        }

        return redirect()->route('users.list')->with('success', 'Đã chuyển User vào thùng rác');
    }

    public function restore($id)
    {
        $this->authorize('restore', User::class);

        $this->users->restore((int) $id);

        return redirect()->route('users.trash')->with('success', 'Khôi phục User thành công');
    }

    public function forceDelete($id)
    {
        $this->authorize('forceDelete', User::class);

        try {
            $this->users->forceDelete((int) $id);
        } catch (\RuntimeException $e) {
            return redirect()->route('users.trash')->with('error', $e->getMessage());
        }

        return redirect()->route('users.trash')->with('success', 'Xóa vĩnh viễn User thành công');
    }

    public function search(IndexUsersRequest $request)
    {
        $this->authorize('viewAny', User::class);

        return view('admin.users.index', $this->queries->indexData($request->validated()));
    }

    public function assignRole(AssignRoleRequest $request, $id)
    {
        $this->authorize('assignRole', User::class);

        try {
            $this->users->assignRole((int) $id, (int) $request->role_id);
        } catch (\RuntimeException $e) {
            return back()->withErrors(['role_id' => $e->getMessage()]);
        }

        return back()->with('success', 'Cập nhật quyền thành công!');
    }

    public function ban(BanUserRequest $request, $id)
    {
        $this->authorize('ban', User::class);

        try {
            $this->users->ban(
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

    public function unban($id)
    {
        $this->authorize('ban', User::class);

        $this->users->unban((int) $id);

        return redirect()->route('users.list')->with('success', 'Đã bỏ ban user thành công.');
    }
}
