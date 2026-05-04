<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $defaultAddress = Address::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->first();

        $recentOrders = Order::where('user_id', $user->id)
            ->latest('id')
            ->take(5)
            ->get();

        // Thống kê số lượng đơn hàng của user theo từng trạng thái để hiển thị tổng quan.
        $stats = [
            'total_orders' => Order::where('user_id', $user->id)->count(),
            'pending_orders' => Order::where('user_id', $user->id)
                ->whereIn('order_status', ['pending', 'processing', 'waiting_for_cancellation'])
                ->count(),
            'delivered_orders' => Order::where('user_id', $user->id)
                ->where('order_status', 'delivered')
                ->count(),
            'cancelled_orders' => Order::where('user_id', $user->id)
                ->where('order_status', 'cancelled')
                ->count(),
        ];

        return view('client.account.index', compact(
            'user',
            'defaultAddress',
            'recentOrders',
            'stats'
        ));
    }

    public function detail()
    {
        $user = Auth::user();

        $defaultAddress = Address::where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->first();

        // Đếm tổng số địa chỉ mà user đã lưu.
        $addressCount = Address::where('user_id', $user->id)->count();

        return view('client.account.detail', compact(
            'user',
            'defaultAddress',
            'addressCount'
        ));
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [
            'name.required' => 'Vui lòng nhập họ tên.',
            'avatar.image' => 'File tải lên phải là hình ảnh.',
            'avatar.mimes' => 'Avatar chỉ chấp nhận JPG, JPEG, PNG hoặc WEBP.',
            'avatar.max' => 'Avatar tối đa 2MB.',
        ]);

        $user->name = $data['name'];

        if ($request->hasFile('avatar')) {
            if (!empty($user->avatar) && !str_starts_with($user->avatar, 'http')) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
            }

            $user->avatar = $request->file('avatar')->store('users', 'public');
        }

        $user->save();

        return redirect()
            ->route('client.account.detail')
            ->with('success', 'Cập nhật hồ sơ thành công.');
    }
}
