<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;

class UserController extends Controller
{
    // ============================
    // LIST USERS (filter by role)
    // ============================
    // /users
    // /users?role=admin|staff|customer
    public function index(Request $request)
    {
        $role = $request->get('role'); // admin|staff|customer|null

        $users = User::with('roles')
            ->when($role, function ($q) use ($role) {
                $q->whereHas('roles', function ($r) use ($role) {
                    $r->where('name', $role);
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->appends(['role' => $role]);

        return view('admin.users.index', compact('users', 'role'));
    }

    // ============================
    // DETAIL USER
    // ============================
    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);
        return view('admin.users.detail', compact('user'));
    }

    // ============================
    // ADD USER
    // ============================
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        return view('admin.users.add', compact('roles'));
    }

    // ============================
    // STORE USER
    // ============================
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id'  => 'required|exists:roles,id',
        ]);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ]);

        // Mỗi user 1 role (theo logic update đang dùng sync)
        $user->roles()->sync([$request->role_id]);

        return redirect()->route('users.list')->with('success', 'Thêm User thành công');
    }

    // ============================
    // EDIT USER
    // ============================
    public function edit($id)
    {
        $user  = User::with('roles')->findOrFail($id);
        $roles = Role::orderBy('name')->get();

        return view('admin.users.edit', compact('user', 'roles'));
    }

    // ============================
    // UPDATE USER
    // ============================
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'role_id'  => 'required|exists:roles,id',
        ]);

        $user = User::findOrFail($id);

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        // cập nhật role (1 role/user)
        $user->roles()->sync([$request->role_id]);

        return redirect()->route('users.list')->with('success', 'Cập nhật User thành công');
    }

    // ============================
    // DELETE USER
    // ============================
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        // detach role trước rồi xóa
        $user->roles()->detach();
        $user->delete();

        return redirect()->route('users.list')->with('success', 'Xóa User thành công');
    }

    // ============================
    // SEARCH USER (keep role filter)
    // ============================
    public function search(Request $request)
    {
        $keyword = $request->keyword;
        $role = $request->get('role'); // admin|staff|customer|null

        $users = User::with('roles')
            ->when($role, function ($q) use ($role) {
                $q->whereHas('roles', function ($r) use ($role) {
                    $r->where('name', $role);
                });
            })
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($qq) use ($keyword) {
                    $qq->where('name', 'LIKE', "%$keyword%")
                       ->orWhere('email', 'LIKE', "%$keyword%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->appends(['keyword' => $keyword, 'role' => $role]);

        return view('admin.users.index', compact('users', 'keyword', 'role'));
    }

    // ============================
    // ASSIGN ROLE (POST)
    // ============================
    public function assignRole(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $user = User::findOrFail($id);
        $user->roles()->sync([$request->role_id]);

        return back()->with('success', 'Cập nhật quyền thành công!');
    }
}
