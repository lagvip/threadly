<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Orders\BulkOrderIdsRequest;
use App\Http\Requests\Admin\Orders\IndexOrdersRequest;
use App\Http\Requests\Admin\Orders\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\Admin\Orders\AdminOrderGhnService;
use App\Services\Admin\Orders\AdminOrderLifecycleService;
use App\Services\Admin\Orders\AdminOrderQueryService;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index(IndexOrdersRequest $request, AdminOrderQueryService $orders)
    {
        $this->authorize('viewAny', Order::class);

        return view('admin.orders.index', $orders->indexData($request->validated()));
    }

    public function show(Order $order, AdminOrderQueryService $orders)
    {
        $this->authorize('view', $order);

        return view('admin.orders.details', [
            'order' => $orders->loadForShow($order),
        ]);
    }

    public function edit(Order $order)
    {
        // Không cho sửa trạng thái thủ công bằng form edit cũ vì trạng thái giao hàng đồng bộ từ GHN.
        return redirect()
            ->route('orders.show', $order)
            ->with('error', 'Không còn cập nhật trạng thái đơn hàng thủ công. Trạng thái giao hàng được đồng bộ từ GHN.');
    }

    public function updateStatus(UpdateOrderStatusRequest $request, $id, AdminOrderLifecycleService $orders, AdminOrderQueryService $queries)
    {
        $this->authorize('updateAny', Order::class);

        try {
            $orders->updateStatus(
                $queries->findForStatusUpdate((int) $id),
                $request->string('order_status')->toString(),
                $request->input('note'),
                Auth::id()
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }

    public function print($id, AdminOrderQueryService $orders)
    {
        $this->authorize('viewAny', Order::class);

        return view('admin.orders.invoice', [
            'order' => $orders->loadForShow($orders->findForStatusUpdate((int) $id)),
        ]);
    }

    public function createGhnOrder(Order $order, AdminOrderGhnService $ghnOrders)
    {
        $this->authorize('manageGhn', $order);

        try {
            return back()->with('success', $ghnOrders->create($order));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function syncGhnOrder(Order $order, AdminOrderGhnService $ghnOrders)
    {
        $this->authorize('manageGhn', $order);

        try {
            return back()->with('success', $ghnOrders->sync($order, Auth::id()));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancelGhnOrder(Order $order, AdminOrderGhnService $ghnOrders)
    {
        $this->authorize('manageGhn', $order);

        try {
            return back()->with('success', $ghnOrders->cancel($order, Auth::id()));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function printGhnOrder(Order $order, AdminOrderGhnService $ghnOrders)
    {
        $this->authorize('manageGhn', $order);

        try {
            return redirect()->away($ghnOrders->printUrl($order));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function simulateGhnStatus(Order $order, string $status, AdminOrderGhnService $ghnOrders)
    {
        abort_unless(app()->environment('local'), 403, 'Chỉ được giả lập GHN ở môi trường local.');
        $this->authorize('manageGhn', $order);

        try {
            return back()->with('success', $ghnOrders->simulate($order, $status, Auth::id()));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Order $order, AdminOrderLifecycleService $orders)
    {
        $this->authorize('delete', $order);

        try {
            $orders->softDelete($order);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đơn hàng đã được xóa.');
    }

    public function trash(AdminOrderQueryService $orders)
    {
        $this->authorize('viewAny', Order::class);

        return view('admin.orders.trash', $orders->trashData());
    }

    public function restore(BulkOrderIdsRequest $request, AdminOrderLifecycleService $orders)
    {
        $this->authorize('restore', Order::class);

        $orders->restore($request->input('ids', []));

        return back()->with('success', 'Khôi phục đơn hàng thành công.');
    }

    public function forceDelete(BulkOrderIdsRequest $request, AdminOrderLifecycleService $orders)
    {
        $this->authorize('forceDelete', Order::class);

        try {
            $orders->forceDelete($request->input('ids', []));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã xóa vĩnh viễn các đơn hàng đã chọn.');
    }
}
