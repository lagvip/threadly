<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // ============================
    // HÀM PHỤ: lấy role admin theo slug
    // ============================
    private function getAdminRole()
    {
        return Role::where('slug', 'admin')->first();
    }

    // ============================
    // HÀM PHỤ: kiểm tra role_id có phải admin không
    // ============================
    private function isAdminRoleId($roleId)
    {
        $adminRole = $this->getAdminRole();

        if (!$adminRole) {
            return false;
        }

        return (int) $adminRole->id === (int) $roleId;
    }

    // ============================
    // HÀM PHỤ: đếm số user đang có role admin
    // ============================
    private function countAdminUsers()
    {
        return User::whereHas('roles', function ($q) {
            $q->where('slug', 'admin');
        })->count();
    }

    // ============================
    // HÀM PHỤ: kiểm tra 1 user có phải admin không
    // ============================
    private function isAdminUser(User $user)
    {
        return $user->hasRole('admin');
    }

    // ============================
    // DANH SÁCH USER (lọc theo role)
    // ============================
    public function index(Request $request)
    {
        $role = $request->get('role');

        $roles = Role::orderBy('name')->get();

        $users = User::with('roles')
            ->withCount(['allOrders as orders_count'])
            ->when($role, function ($q) use ($role) {
                $q->whereHas('roles', function ($r) use ($role) {
                    $r->where('slug', $role);
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->appends(['role' => $role]);

        return view('admin.users.index', compact('users', 'role', 'roles'));
    }

    // ============================
    // THÙNG RÁC USER
    // ============================
    public function trash()
    {
        $users = User::onlyTrashed()
            ->with('roles')
            ->withCount(['allOrders as orders_count'])
            ->orderByDesc('deleted_at')
            ->paginate(10);

        return view('admin.users.trash', compact('users'));
    }

    // ============================
    // CHI TIẾT USER
    // ============================
    public function show($id)
    {
        $user = User::with('roles')->findOrFail($id);
        return view('admin.users.detail', compact('user'));
    }

    // ============================
    // FORM THÊM USER
    // ============================
    public function create()
    {
        $roles = Role::orderBy('name')->get();
        $hasAdmin = $this->countAdminUsers() >= 1;

        if ($hasAdmin) {
            $roles = $roles->reject(function ($role) {
                return $role->slug === 'admin';
            });
        }

        return view('admin.users.add', compact('roles', 'hasAdmin'));
    }

    // ============================
    // LƯU USER MỚI
    // ============================
    public function store(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role_id'  => 'required|exists:roles,id',
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'     => 'Tên không được để trống.',
            'email.required'    => 'Email không được để trống.',
            'email.email'       => 'Email không đúng định dạng.',
            'email.unique'      => 'Email đã tồn tại.',
            'password.required' => 'Mật khẩu không được để trống.',
            'password.min'      => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'role_id.required'  => 'Vui lòng chọn role.',
            'role_id.exists'    => 'Role không hợp lệ.',
            'avatar.image'      => 'File tải lên phải là hình ảnh.',
            'avatar.mimes'      => 'Ảnh chỉ chấp nhận định dạng jpg, jpeg, png, webp.',
            'avatar.max'        => 'Kích thước ảnh không được vượt quá 2MB.',
        ]);

        if ($this->isAdminRoleId($request->role_id) && $this->countAdminUsers() >= 1) {
            return back()
                ->withInput()
                ->withErrors([
                    'role_id' => 'Hệ thống chỉ cho phép tồn tại 1 tài khoản Admin duy nhất.'
                ]);
        }

        $data = [
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => bcrypt($request->password),
        ];

        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('users', 'public');
        }

        $user = User::create($data);

        $user->roles()->sync([$request->role_id]);

        return redirect()->route('users.list')->with('success', 'Thêm User thành công');
    }

    // ============================
    // FORM SỬA USER
    // ============================
    public function edit($id)
    {
        $user  = User::with('roles')->findOrFail($id);
        $roles = Role::orderBy('name')->get();

        $hasAdmin = User::whereHas('roles', function ($q) {
            $q->where('slug', 'admin');
        })->where('id', '!=', $user->id)->exists();

        if ($this->countAdminUsers() >= 1 && !$this->isAdminUser($user)) {
            $roles = $roles->reject(function ($role) {
                return $role->slug === 'admin';
            });
        }

        return view('admin.users.edit', compact('user', 'roles', 'hasAdmin'));
    }

    // ============================
    // CẬP NHẬT USER
    // ============================
    public function update(Request $request, $id)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'role_id'  => 'required|exists:roles,id',
            'avatar'   => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ], [
            'name.required'    => 'Tên không được để trống.',
            'email.required'   => 'Email không được để trống.',
            'email.email'      => 'Email không đúng định dạng.',
            'email.unique'     => 'Email đã tồn tại.',
            'password.min'     => 'Mật khẩu phải có ít nhất 6 ký tự.',
            'role_id.required' => 'Vui lòng chọn role.',
            'role_id.exists'   => 'Role không hợp lệ.',
            'avatar.image'     => 'File tải lên phải là hình ảnh.',
            'avatar.mimes'     => 'Ảnh chỉ chấp nhận định dạng jpg, jpeg, png, webp.',
            'avatar.max'       => 'Kích thước ảnh không được vượt quá 2MB.',
        ]);

        $user = User::with('roles')->findOrFail($id);

        $isCurrentAdmin = $this->isAdminUser($user);
        $isNewAdminRole = $this->isAdminRoleId($request->role_id);
        $adminCount = $this->countAdminUsers();

        if (!$isCurrentAdmin && $isNewAdminRole && $adminCount >= 1) {
            return back()
                ->withInput()
                ->withErrors([
                    'role_id' => 'Hệ thống chỉ cho phép tồn tại 1 tài khoản Admin duy nhất.'
                ]);
        }

        if ($isCurrentAdmin && !$isNewAdminRole && $adminCount <= 1) {
            return back()
                ->withInput()
                ->withErrors([
                    'role_id' => 'Không thể đổi role của Admin duy nhất sang quyền khác.'
                ]);
        }

        $data = [
            'name'  => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            $data['avatar'] = $request->file('avatar')->store('users', 'public');
        }

        $user->update($data);

        $user->roles()->sync([$request->role_id]);

        return redirect()->route('users.list')->with('success', 'Cập nhật User thành công');
    }

    // ============================
    // XÓA MỀM USER
    // CHỈ XÓA KHI KHÔNG CÒN ĐƠN HÀNG NÀO
    // ============================
    public function destroy($id)
    {
        $user = User::with('roles')->findOrFail($id);

        if ($user->allOrders()->exists()) {
            return redirect()
                ->route('users.list')
                ->with('error', 'User này vẫn còn đơn hàng, không thể xóa.');
        }

        // Xóa mềm: KHÔNG xóa avatar, KHÔNG detach role
        $user->delete();

        return redirect()->route('users.list')->with('success', 'Đã chuyển User vào thùng rác');
    }

    // ============================
    // KHÔI PHỤC USER
    // ============================
    public function restore($id)
    {
        $user = User::onlyTrashed()
            ->with('roles')
            ->findOrFail($id);

        $user->restore();

        return redirect()->route('users.trash')->with('success', 'Khôi phục User thành công');
    }

    // ============================
    // XÓA VĨNH VIỄN USER
    // CHỈ XÓA KHI KHÔNG CÒN ĐƠN HÀNG NÀO
    // ============================
    public function forceDelete($id)
    {
        $user = User::onlyTrashed()
            ->with('roles')
            ->findOrFail($id);

        if ($user->allOrders()->exists()) {
            return redirect()
                ->route('users.trash')
                ->with('error', 'User này vẫn còn đơn hàng, không thể xóa vĩnh viễn.');
        }

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->roles()->detach();
        $user->forceDelete();

        return redirect()->route('users.trash')->with('success', 'Xóa vĩnh viễn User thành công');
    }

    // ============================
    // TÌM KIẾM USER (giữ lại bộ lọc role)
    // ============================
    public function search(Request $request)
    {
        $keyword = $request->keyword;
        $role = $request->get('role');

        $roles = Role::orderBy('name')->get();

        $users = User::with('roles')
            ->withCount(['allOrders as orders_count'])
            ->when($role, function ($q) use ($role) {
                $q->whereHas('roles', function ($r) use ($role) {
                    $r->where('slug', $role);
                });
            })
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($qq) use ($keyword) {
                    $qq->where('name', 'LIKE', "%{$keyword}%")
                        ->orWhere('email', 'LIKE', "%{$keyword}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->appends([
                'keyword' => $keyword,
                'role'    => $role
            ]);

        return view('admin.users.index', compact('users', 'keyword', 'role', 'roles'));
    }

    // ============================
    // GÁN ROLE CHO USER
    // ============================
    public function assignRole(Request $request, $id)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ], [
            'role_id.required' => 'Vui lòng chọn role.',
            'role_id.exists'   => 'Role không hợp lệ.'
        ]);

        $user = User::with('roles')->findOrFail($id);

        $isCurrentAdmin = $this->isAdminUser($user);
        $isNewAdminRole = $this->isAdminRoleId($request->role_id);
        $adminCount = $this->countAdminUsers();

        if (!$isCurrentAdmin && $isNewAdminRole && $adminCount >= 1) {
            return back()->withErrors([
                'role_id' => 'Hệ thống chỉ cho phép tồn tại 1 tài khoản Admin duy nhất.'
            ]);
        }

        if ($isCurrentAdmin && !$isNewAdminRole && $adminCount <= 1) {
            return back()->withErrors([
                'role_id' => 'Không thể đổi role của Admin duy nhất sang quyền khác.'
            ]);
        }

        $user->roles()->sync([$request->role_id]);

        return back()->with('success', 'Cập nhật quyền thành công!');
    }
    public function ban(Request $request, $id)
    {
        $request->validate([
            'ban_reason_option' => 'required|string',
            'ban_reason_custom' => 'nullable|string|max:1000',
        ], [
            'ban_reason_option.required' => 'Vui lòng chọn lý do ban.',
            'ban_reason_custom.max' => 'Lý do tự nhập không được quá 1000 ký tự.',
        ]);

        $user = User::findOrFail($id);

        if ($this->isAdminUser($user)) {
            return redirect()
                ->route('users.list')
                ->with('error', 'Không thể ban tài khoản Admin.');
        }

        $reason = $request->ban_reason_option;

        if ($reason === 'custom') {
            $reason = trim((string) $request->ban_reason_custom);

            if ($reason === '') {
                return back()
                    ->withErrors(['ban_reason_custom' => 'Vui lòng nhập lý do ban.'])
                    ->withInput();
            }
        }

        $user->update([
            'status' => User::STATUS_BANNED,
            'ban_reason' => $reason,
            'banned_at' => now(),
            'banned_by' => Auth::id(),
        ]);

        return redirect()
            ->route('users.list')
            ->with('success', 'Đã ban user thành công.');
    }

    public function unban($id)
    {
        $user = User::findOrFail($id);

        $user->update([
            'status' => User::STATUS_ACTIVE,
            'ban_reason' => null,
            'banned_at' => null,
            'banned_by' => null,
        ]);

        return redirect()
            ->route('users.list')
            ->with('success', 'Đã bỏ ban user thành công.');
    }
}
