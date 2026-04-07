<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user');

        if ($request->filled('order_code')) {
            $query->where('order_code', 'like', '%' . $request->order_code . '%');
        }

        if ($request->filled('customer')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->customer . '%')
                    ->orWhere('name', 'like', '%' . $request->customer . '%')
                    ->orWhereHas('user', function ($subQuery) use ($request) {
                        $subQuery->where('email', 'like', '%' . $request->customer . '%')
                            ->orWhere('name', 'like', '%' . $request->customer . '%');
                    });
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        $orders = $query->latest()->paginate(10);

        $orderCancel = Order::where('order_status', OrderStatus::Cancelled->value)->count();
        $orderDelivering = Order::where('order_status', OrderStatus::Shipped->value)->count();
        $pendingPayment = Order::whereIn('payment_status', ['unpaid', 'pending'])->count();
        $orderDelivered = Order::where('order_status', OrderStatus::Delivered->value)->count();

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
            'voucher',
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

        $newStatus = $request->string('order_status')->toString();
        $currentStatus = $order->order_status;
        $currentEnum = OrderStatus::from($currentStatus);
        $newEnum = OrderStatus::from($newStatus);

        // Đơn đã kết thúc thì không cho đổi tiếp
        if ($currentEnum->isTerminal()) {
            return back()->with('error', 'Đơn hàng đã ở trạng thái kết thúc, không thể cập nhật thêm.');
        }

        // Không xử lý flow chờ duyệt hủy cũ nữa
        if (
            $currentStatus === OrderStatus::WaitingForCancellation->value ||
            $newStatus === OrderStatus::WaitingForCancellation->value
        ) {
            return back()->with('error', 'Trạng thái chờ duyệt hủy không còn được sử dụng.');
        }

        // Thanh toán failed chỉ cho hủy
        if ($order->payment_status === 'failed' && $newStatus !== OrderStatus::Cancelled->value) {
            return back()->with('error', 'Đơn hàng thanh toán thất bại chỉ có thể hủy.');
        }

        // Hủy trực tiếp
        if ($newStatus === OrderStatus::Cancelled->value) {
            if ($order->payment_status === 'paid') {
                return back()->with('error', 'Đơn hàng đã thanh toán không thể hủy.');
            }

            if (!in_array($currentStatus, [
                OrderStatus::Pending->value,
                OrderStatus::Processing->value,
            ], true)) {
                return back()->with('error', 'Chỉ có thể hủy khi đơn đang chờ xử lý hoặc đang xử lý.');
            }

            $order->order_status = OrderStatus::Cancelled->value;
            $order->save();

            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => OrderStatus::Cancelled->value,
                'note' => $request->note ?: 'Admin hủy đơn.',
                'changed_by' => Auth::id(),
            ]);

            return back()->with('success', 'Đã hủy đơn hàng.');
        }

        // Chuyển trạng thái theo luồng chuẩn
        if (!$currentEnum->canTransitionTo($newEnum)) {
            return back()->with('error', 'Chỉ có thể cập nhật trạng thái lần lượt theo đúng quy trình.');
        }

        // COD khi giao thành công thì coi như đã thanh toán
        if (
            $newStatus === OrderStatus::Delivered->value &&
            $order->payment_method === 'cod' &&
            in_array($order->payment_status, ['unpaid', 'pending'], true)
        ) {
            $order->payment_status = 'paid';
        }

        $order->order_status = $newStatus;
        $order->save();

        OrderStatusLog::create([
            'order_id' => $order->id,
            'status' => $newStatus,
            'note' => $request->note,
            'changed_by' => Auth::id(),
        ]);

        return back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }

    public function destroy(Order $order)
    {
        if ($order->order_status !== OrderStatus::Cancelled->value) {
            return back()->with('error', 'Chỉ có thể xóa đơn hàng đã hủy.');
        }

        $order->delete();

        return back()->with('success', 'Đơn hàng đã được xóa.');
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
            return back()->with('error', 'Chưa chọn đơn hàng nào để xóa vĩnh viễn.');
        }

        try {
            Order::withTrashed()
                ->whereIn('id', $ids)
                ->forceDelete();

            return back()->with('success', 'Đã xóa vĩnh viễn các đơn hàng đã chọn.');
        } catch (\Exception $e) {
            Log::error('Xóa vĩnh viễn đơn hàng thất bại: ' . $e->getMessage());

            return back()->with('error', 'Có lỗi xảy ra khi xóa vĩnh viễn.');
        }
    }
}
