<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use App\Models\OrderStatusLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{

    public function index(Request $request)
    {
        $query = Order::with('user');

        if ($request->filled('order_code')) {
            $query->where('order_code', 'like', '%' . $request->order_code . '%');
        }

        if ($request->filled('customer')) {
            $query->whereHas('user', function ($subQuery) use ($request) {
                $subQuery->where('email', 'like', '%' . $request->customer . '%');
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        $orders = $query->latest()->paginate(10);

        $orderCancel     = Order::where('order_status', OrderStatus::Cancelled->value)->count();
        $orderDelivering = Order::where('order_status', OrderStatus::Shipped->value)->count();
        $pendingPayment  = Order::where('payment_status', 'unpaid')->count();
        $orderDelivered  = Order::where('order_status', OrderStatus::Delivered->value)->count();

        return view('admin.orders.index', compact(
            'orders',
            'orderCancel',
            'orderDelivering',
            'pendingPayment',
            'orderDelivered'
        ));
    }

    public function show(Order $order)
    {
        $order->load([
            'user',
            'details.variant.product',
            'details.variant.size',
            'details.variant.color',
        ]);

        return view('admin.orders.details', compact('order'));
    }

    public function edit(Order $order)
    {
        return view('admin.orders.edit', compact('order'));
    }


    public function updateStatus(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'order_status' => ['required', Rule::in(OrderStatus::values())],
            'note' => 'nullable|string|max:1000',
        ]);

        $newStatus = $request->order_status;
        $currentStatus = $order->order_status;

        if ($order->payment_status === 'failed' && $newStatus !== OrderStatus::Cancelled->value) {
            return back()->with('error', 'Đơn hàng thanh toán thất bại chỉ có thể hủy.');
        }

        if ($newStatus === OrderStatus::Cancelled->value) {

            if ($order->payment_status === 'paid') {
                return back()->with('error', 'Không thể hủy đơn hàng đã thanh toán.');
            }

            $order->order_status = $newStatus;

        } else {

            $currentLevel = $this->statusLevel($currentStatus);
            $newLevel = $this->statusLevel($newStatus);

            if ($newLevel - $currentLevel !== 1) {
                return back()->with('error', 'Chỉ có thể cập nhật trạng thái lần lượt theo từng bước.');
            }

            if ($newStatus === OrderStatus::Delivered->value && $order->payment_status === 'unpaid') {
                $order->payment_status = 'paid';
            }

            $order->order_status = $newStatus;
        }

        $order->save();

        OrderStatusLog::create([
            'order_id'   => $order->id,
            'status'     => $newStatus,
            'note'       => $request->note,
            'changed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }


    public function destroy(Order $order)
    {
        if ($order->order_status !== OrderStatus::Cancelled->value) {
            return back()->with('error', 'Chỉ có thể xóa đơn hàng đã huỷ.');
        }

        $order->delete();

        return back()->with('success', 'Đơn hàng đã được xóa.');
    }


    private function statusLevel($status): int
    {
        $value = is_object($status) ? $status->value : $status;

        return match ($value) {

            OrderStatus::Pending->value    => 1,
            OrderStatus::Processing->value => 2,
            OrderStatus::Shipped->value    => 3,
            OrderStatus::Delivered->value  => 4,
            OrderStatus::Cancelled->value  => 4,
            default => 0,
        };
    }


    public function trash()
    {
        $orders = Order::onlyTrashed()
            ->with('user')
            ->latest()
            ->paginate(10);

        return view('admin.orders.trash', compact('orders'));
    }


    public function restore(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Chưa chọn đơn hàng nào để khôi phục.');
        }

        Order::withTrashed()->whereIn('id', $ids)->restore();

        return back()->with('success', 'Khôi phục đơn hàng thành công.');
    }


    public function forceDelete(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Chưa chọn đơn hàng nào để xoá vĩnh viễn.');
        }

        try {

            Order::withTrashed()
                ->whereIn('id', $ids)
                ->forceDelete();

            return back()->with('success', 'Đã xoá vĩnh viễn các đơn hàng đã chọn.');

        } catch (\Exception $e) {

            Log::error('Xoá vĩnh viễn đơn hàng thất bại: ' . $e->getMessage());

            return back()->with('error', 'Có lỗi xảy ra khi xoá vĩnh viễn.');
        }
    }

}
