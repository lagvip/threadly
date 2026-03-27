<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientOrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->latest('id')
            ->paginate(10);

        return view('client.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with([
                'details.variant.color',
                'details.variant.size',
                'details.product',
            ])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        return view('client.orders.show', compact('order'));
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:1000'],
        ], [
            'cancel_reason.required' => 'Vui lòng chọn lý do hủy đơn.',
        ]);

        if (! $order->can_cancel) {
            return back()->with('error', 'Đơn hàng này không thể hủy ở trạng thái hiện tại.');
        }

        $reason = trim((string) $request->cancel_reason);
        $oldStatus = $order->order_status;
        $actionType = $order->cancel_action_type;

        DB::transaction(function () use ($order, $reason, $oldStatus, $actionType) {
            if ($actionType === 'direct') {
                $order->update([
                    'previous_status' => $oldStatus,
                    'order_status'    => 'cancelled',
                    'payment_status'  => 'cancelled',
                    'cancel_reason'   => $reason,
                ]);

                OrderStatusLog::create([
                    'order_id'   => $order->id,
                    'status'     => 'cancelled',
                    'note'       => 'Khách hàng hủy đơn: ' . $reason,
                    'changed_by' => Auth::id(),
                ]);

                return;
            }

            if ($actionType === 'request') {
                $order->update([
                    'previous_status' => $oldStatus,
                    'order_status'    => 'waiting_for_cancellation',
                    'cancel_reason'   => $reason,
                ]);

                OrderStatusLog::create([
                    'order_id'   => $order->id,
                    'status'     => 'waiting_for_cancellation',
                    'note'       => 'Khách hàng gửi yêu cầu hủy: ' . $reason,
                    'changed_by' => Auth::id(),
                ]);

                return;
            }

            throw new \RuntimeException('Trạng thái hủy đơn không hợp lệ.');
        });

        if ($actionType === 'request') {
            return back()->with('success', 'Đã gửi yêu cầu hủy đơn. Admin sẽ kiểm tra và xử lý tiếp.');
        }

        return back()->with('success', 'Đã hủy đơn hàng thành công.');
    }
}
