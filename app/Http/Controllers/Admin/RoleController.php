<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Roles\StoreRoleRequest;
use App\Http\Requests\Admin\Roles\UpdateRoleRequest;
use App\Services\Admin\Roles\AdminRoleService;
use RuntimeException;

class RoleController extends Controller
{
    public function __construct(protected AdminRoleService $roles)
    {
    }

    public function index()
    {
        return view('admin.roles.list', $this->roles->indexData());
    }

    public function trash()
    {
        return view('admin.roles.trash', $this->roles->trashData());
    }

    public function show($id)
    {
        return view('admin.roles.detail', ['role' => $this->roles->find((int) $id)]);
    }

    public function create()
    {
        return view('admin.roles.add');
    }

    public function store(StoreRoleRequest $request)
    {
        $this->roles->create($request->validated());

        return redirect()->route('roles.list')->with('success', 'Thêm role thành công');
    }

    public function edit($id)
    {
        return view('admin.roles.edit', ['role' => $this->roles->find((int) $id)]);
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        $this->roles->update((int) $id, $request->validated());

        return redirect()->route('roles.list')->with('success', 'Cập nhật role thành công');
    }

    public function destroy($id)
    {
        try {
            $this->roles->softDelete((int) $id);

            return redirect()->route('roles.list')->with('success', 'Đã chuyển role vào thùng rác');
        } catch (RuntimeException $e) {
            return redirect()->route('roles.list')->with('error', $e->getMessage());
        }
    }

    public function restore($id)
    {
        $this->roles->restore((int) $id);

        return redirect()->route('roles.trash')->with('success', 'Khôi phục role thành công');
    }

    public function forceDelete($id)
    {
        try {
            $this->roles->forceDelete((int) $id);

            return redirect()->route('roles.trash')->with('success', 'Xóa vĩnh viễn role thành công');
        } catch (RuntimeException $e) {
            return redirect()->route('roles.trash')->with('error', $e->getMessage());
        }
    }
}
