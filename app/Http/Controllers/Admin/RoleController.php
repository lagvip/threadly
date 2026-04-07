<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount(['usersWithTrashed as users_count'])
            ->latest()
            ->paginate(10);

        return view('admin.roles.list', compact('roles'));
    }

    public function trash()
    {
        $roles = Role::onlyTrashed()
            ->withCount(['usersWithTrashed as users_count'])
            ->latest()
            ->paginate(10);

        return view('admin.roles.trash', compact('roles'));
    }

    public function show($id)
    {
        $role = Role::findOrFail($id);
        return view('admin.roles.detail', compact('role'));
    }

    public function create()
    {
        return view('admin.roles.add');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug',
        ], [
            'name.required' => 'Tên role không được để trống',
            'slug.unique' => 'Slug đã tồn tại',
        ]);

        $slug = $request->slug ?: Str::slug($request->name);

        Role::create([
            'name' => $request->name,
            'slug' => $slug,
            'permissions' => $request->permissions ?? null,
        ]);

        return redirect()->route('roles.list')->with('success', 'Thêm role thành công');
    }

    public function edit($id)
    {
        $role = Role::findOrFail($id);
        return view('admin.roles.edit', compact('role'));
    }

    public function update(Request $request, $id)
    {
        $role = Role::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:roles,slug,' . $id,
        ], [
            'name.required' => 'Tên role không được để trống',
            'slug.unique' => 'Slug đã tồn tại',
        ]);

        $slug = $request->slug ?: Str::slug($request->name);

        $role->update([
            'name' => $request->name,
            'slug' => $slug,
            'permissions' => $request->permissions ?? $role->permissions,
        ]);

        return redirect()->route('roles.list')->with('success', 'Cập nhật role thành công');
    }

    public function destroy($id)
    {
        $role = Role::withCount(['usersWithTrashed as users_count'])->findOrFail($id);

        if ($role->users_count > 0) {
            return redirect()
                ->route('roles.list')
                ->with('error', 'Role này vẫn còn user, không thể xóa.');
        }

        $role->delete();

        return redirect()->route('roles.list')->with('success', 'Đã chuyển role vào thùng rác');
    }

    public function restore($id)
    {
        $role = Role::onlyTrashed()->findOrFail($id);
        $role->restore();

        return redirect()->route('roles.trash')->with('success', 'Khôi phục role thành công');
    }

    public function forceDelete($id)
    {
        $role = Role::onlyTrashed()
            ->withCount(['usersWithTrashed as users_count'])
            ->findOrFail($id);

        if ($role->users_count > 0) {
            return redirect()
                ->route('roles.trash')
                ->with('error', 'Role này vẫn còn user, không thể xóa vĩnh viễn.');
        }

        $role->forceDelete();

        return redirect()->route('roles.trash')->with('success', 'Xóa vĩnh viễn role thành công');
    }
}
