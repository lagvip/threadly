<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Services\GhnService;
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
        return redirect()
            ->route('orders.show', $order)
            ->with('error', 'Không còn cập nhật trạng thái đơn hàng thủ công. Trạng thái giao hàng được đồng bộ từ GHN.');
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

    public function createGhnOrder(Order $order, GhnService $ghnService)
    {
        try {
            $ghnService->createOrder($order);

            return back()->with('success', 'Đã tạo vận đơn GHN thành công. Mã vận đơn: ' . $order->fresh()->ghn_order_code);
        } catch (\Throwable $e) {
            Log::error('Create GHN order failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
            ]);

            return back()->with('error', $e->getMessage() ?: 'Tạo vận đơn GHN thất bại.');
        }
    }

    public function syncGhnOrder(Order $order, GhnService $ghnService)
    {
        if (empty($order->ghn_order_code)) {
            return back()->with('error', 'Đơn này chưa có mã vận đơn GHN để đồng bộ.');
        }

        try {
            $response = $ghnService->getOrderInfo($order->ghn_order_code);
            $ghnService->syncOrderFromGhnInfo($order, $response, Auth::id(), 'Admin đồng bộ GHN');

            return back()->with('success', 'Đã đồng bộ trạng thái GHN thành công.');
        } catch (\Throwable $e) {
            Log::error('Sync GHN order failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'ghn_order_code' => $order->ghn_order_code,
            ]);

            return back()->with('error', $e->getMessage() ?: 'Đồng bộ GHN thất bại.');
        }
    }

    public function cancelGhnOrder(Order $order, GhnService $ghnService)
    {
        if (empty($order->ghn_order_code)) {
            return back()->with('error', 'Đơn này chưa có mã vận đơn GHN để hủy.');
        }

        if ($order->order_status === OrderStatus::Delivered->value) {
            return back()->with('error', 'Đơn đã giao thành công, không thể hủy vận đơn GHN.');
        }

        try {
            $response = $ghnService->cancelOrder($order->ghn_order_code);
            $result = collect(data_get($response, 'data', []))->firstWhere('order_code', $order->ghn_order_code);

            if ($result && isset($result['result']) && !$result['result']) {
                return back()->with('error', $result['message'] ?? 'GHN không cho hủy vận đơn này.');
            }

            $oldStatus = $order->order_status;

            $order->update([
                'ghn_status' => 'cancel',
                'ghn_status_name' => 'Đã hủy trên GHN',
                'ghn_raw_response' => $response,
                'ghn_synced_at' => now(),
                'order_status' => OrderStatus::Cancelled->value,
                'payment_status' => $order->payment_method === Order::PAYMENT_METHOD_COD && $order->payment_status !== Order::PAYMENT_PAID
                    ? Order::PAYMENT_CANCELLED
                    : $order->payment_status,
            ]);

            if ($oldStatus !== OrderStatus::Cancelled->value) {
                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => OrderStatus::Cancelled->value,
                    'note' => 'Admin hủy vận đơn GHN: ' . $order->ghn_order_code,
                    'changed_by' => Auth::id(),
                ]);
            }

            return back()->with('success', 'Đã gửi yêu cầu hủy vận đơn GHN thành công.');
        } catch (\Throwable $e) {
            Log::error('Cancel GHN order failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'ghn_order_code' => $order->ghn_order_code,
            ]);

            return back()->with('error', $e->getMessage() ?: 'Hủy vận đơn GHN thất bại.');
        }
    }

    public function printGhnOrder(Order $order, GhnService $ghnService)
    {
        if (empty($order->ghn_order_code)) {
            return back()->with('error', 'Đơn này chưa có mã vận đơn GHN để in.');
        }

        try {
            return redirect()->away($ghnService->printOrderUrl($order->ghn_order_code));
        } catch (\Throwable $e) {
            Log::error('Print GHN order failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'ghn_order_code' => $order->ghn_order_code,
            ]);

            return back()->with('error', $e->getMessage() ?: 'Không in được vận đơn GHN.');
        }
    }
    public function simulateGhnStatus(Order $order, string $status, GhnService $ghnService)
    {
        abort_unless(app()->environment('local'), 403, 'Chỉ được giả lập GHN ở môi trường local.');

        if (empty($order->ghn_order_code)) {
            return back()->with('error', 'Đơn này chưa có mã vận đơn GHN, không thể giả lập trạng thái.');
        }

        $currentStatus = $order->ghn_status ?: 'ready_to_pick';

        $allowedTransitions = [
            'ready_to_pick' => [
                'picking',
                'picked',
                'cancel',
            ],

            'picking' => [
                'picked',
                'cancel',
            ],

            'money_collect_picking' => [
                'picked',
                'cancel',
            ],

            'picked' => [
                'storing',
                'delivering',
                'cancel',
                'lost',
                'damage',
            ],

            'storing' => [
                'transporting',
                'sorting',
                'delivering',
                'lost',
                'damage',
            ],

            'transporting' => [
                'sorting',
                'delivering',
                'lost',
                'damage',
            ],

            'sorting' => [
                'delivering',
                'lost',
                'damage',
            ],

            'delivering' => [
                'delivered',
                'delivery_fail',
                'waiting_to_return',
                'lost',
                'damage',
            ],

            'money_collect_delivering' => [
                'delivered',
                'delivery_fail',
                'waiting_to_return',
            ],

            'delivery_fail' => [
                'delivering',
                'waiting_to_return',
                'cancel',
            ],

            'waiting_to_return' => [
                'return',
                'return_transporting',
                'returning',
            ],

            'return' => [
                'return_transporting',
                'return_sorting',
                'returning',
            ],

            'return_transporting' => [
                'return_sorting',
                'returning',
            ],

            'return_sorting' => [
                'returning',
            ],

            'returning' => [
                'returned',
                'return_fail',
            ],

            'return_fail' => [
                'returning',
                'returned',
            ],

            'delivered' => [],
            'cancel' => [],
            'returned' => [],
            'lost' => [],
            'damage' => [],
            'exception' => [],
        ];

        $allowedNextStatuses = $allowedTransitions[$currentStatus] ?? [
            'picked',
            'delivering',
            'delivery_fail',
            'delivered',
            'cancel',
            'lost',
            'damage',
        ];

        if (!in_array($status, $allowedNextStatuses, true)) {
            return back()->with(
                'error',
                'Không thể giả lập từ trạng thái "' .
                $ghnService->statusName($currentStatus) .
                '" sang "' .
                $ghnService->statusName($status) .
                '".'
            );
        }

        try {
            $fakeGhnResponse = [
                'code' => 200,
                'message' => 'Local simulated GHN status',
                'data' => [
                    'order_code' => $order->ghn_order_code,
                    'client_order_code' => $order->ghn_client_order_code,
                    'status' => $status,
                    'leadtime' => now()->addDays(2)->toISOString(),
                ],
            ];

            $ghnService->syncOrderFromGhnInfo(
                $order,
                $fakeGhnResponse,
                Auth::id(),
                'Giả lập GHN local'
            );

            return back()->with(
                'success',
                'Đã giả lập trạng thái GHN: ' .
                $ghnService->statusGroup($status) .
                ' - ' .
                $ghnService->statusName($status)
            );
        } catch (\Throwable $e) {
            Log::error('Simulate GHN status failed: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'ghn_order_code' => $order->ghn_order_code,
                'from_status' => $currentStatus,
                'to_status' => $status,
            ]);

            return back()->with('error', $e->getMessage() ?: 'Giả lập trạng thái GHN thất bại.');
        }
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
