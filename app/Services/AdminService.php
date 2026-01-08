<?php

namespace App\Services;

use App\Models\Role;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Illuminate\Http\Request;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AdminService
{
    public function getAdmins($perPage = 10)
    {
        return User::whereHas('roles', function ($query) {
            $query->whereIn('slug', ['super-admin', 'admin']);
        })->paginate($perPage);
    }

    public function getAdminById($id)
    {
        return User::with('roles')->findOrFail($id);
    }

    public function updateAdmin($id, array $data)
    {
        $user = User::findOrFail($id);

        $user->update([
            'email' => $data['email'] ?? $user->email,
            'first_name' => $data['first_name'] ?? $user->first_name,
            'last_name' => $data['last_name'] ?? $user->last_name,
        ]);

        if (isset($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }

        return $user;
    }

    public function getAllRoles()
    {
        return Role::all();
    }

    public function createAdmin(array $data)
    {
        Log::info('Creating admin with data: ', $data);

        // Đăng ký người dùng qua Sentinel
        $user = Sentinel::registerAndActivate([
            'first_name' => $data['first_name'],
            'last_name'  => $data['last_name'],
            'email'      => $data['email'],
            'password'   => $data['password'],
        ]);

        // Cập nhật trạng thái
        $user->status = 1;
        $user->save();

        // Gắn role
        if (!empty($data['role'])) {
            $role = Role::where('slug', $data['role'])->first();
            if ($role) {
                $user->roles()->attach($role->id);
                Log::info('Attached role: ' . $data['role'] . ' to user: ' . $user->email);
            } else {
                Log::error('Role không tồn tại: ' . $data['role']);
            }
        }

        return $user;
    }
    public function deleteUser($id)
{
    try {
        $user = \Cartalyst\Sentinel\Laravel\Facades\Sentinel::findById($id);

        if (!$user) {
            return redirect()->back()->withErrors(['user' => 'Không tìm thấy người dùng.']);
        }

        $user->delete();

        return redirect()->route('admin.auth.listAdmin')->with('success', 'Xóa người dùng thành công!');
    } catch (\Exception $e) {
        Log::error('Lỗi khi xóa người dùng', ['message' => $e->getMessage()]);
        return redirect()->back()->withErrors(['error' => 'Có lỗi xảy ra khi xóa người dùng.']);
    }
}
public function toggleStatus($id, $status)
{
    try {
        $user = User::findOrFail($id);
        $user->status = $status ? 1 : 0; // Chuyển đổi boolean thành 1 hoặc 0
        $user->save();

        Log::info('Cập nhật trạng thái người dùng: ', ['id' => $id, 'status' => $user->status]);

        return $user;
    } catch (Exception $e) {
        Log::error('Lỗi khi cập nhật trạng thái: ', ['id' => $id, 'message' => $e->getMessage()]);
        throw $e;
    }
}
}