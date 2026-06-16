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
    public function __construct(
        protected AdminOrderQueryService $queries,
        protected AdminOrderLifecycleService $orders,
        protected AdminOrderGhnService $ghnOrders,
    ) {}

    public function index(IndexOrdersRequest $request)
    {
        $this->authorize('viewAny', Order::class);

        return view('admin.orders.index', $this->queries->indexData($request->validated()));
    }

    public function show(Order $order)
    {
        $this->authorize('view', $order);

        return view('admin.orders.details', $this->queries->showData($order));
    }

    public function edit(Order $order)
    {
        // Không cho sửa trạng thái thủ công bằng form edit cũ vì trạng thái giao hàng đồng bộ từ GHN.
        return redirect()
            ->route('orders.show', $order)
            ->with('error', 'Không còn cập nhật trạng thái đơn hàng thủ công. Trạng thái giao hàng được đồng bộ từ GHN.');
    }

    public function updateStatus(UpdateOrderStatusRequest $request, $id)
    {
        $this->authorize('updateAny', Order::class);

        try {
            $this->orders->updateStatus(
                $this->queries->findForStatusUpdate((int) $id),
                $request->string('order_status')->toString(),
                $request->input('note'),
                Auth::id()
            );
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Cập nhật trạng thái đơn hàng thành công.');
    }

    public function print($id)
    {
        $this->authorize('viewAny', Order::class);

        return view('admin.orders.invoice', [
            'order' => $this->queries->loadForShow($this->queries->findForStatusUpdate((int) $id)),
        ]);
    }

    public function createGhnOrder(Order $order)
    {
        $this->authorize('manageGhn', $order);

        try {
            return back()->with('success', $this->ghnOrders->create($order));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function syncGhnOrder(Order $order)
    {
        $this->authorize('manageGhn', $order);

        try {
            return back()->with('success', $this->ghnOrders->sync($order, Auth::id()));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancelGhnOrder(Order $order)
    {
        $this->authorize('manageGhn', $order);

        try {
            return back()->with('success', $this->ghnOrders->cancel($order, Auth::id()));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function printGhnOrder(Order $order)
    {
        $this->authorize('manageGhn', $order);

        try {
            return redirect()->away($this->ghnOrders->printUrl($order));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function simulateGhnStatus(Order $order, string $status)
    {
        abort_unless(app()->environment('local'), 403, 'Chỉ được giả lập GHN ở môi trường local.');
        $this->authorize('manageGhn', $order);

        try {
            return back()->with('success', $this->ghnOrders->simulate($order, $status, Auth::id()));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function destroy(Order $order)
    {
        $this->authorize('delete', $order);

        try {
            $this->orders->softDelete($order);
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đơn hàng đã được xóa.');
    }

    public function trash()
    {
        $this->authorize('viewAny', Order::class);

        return view('admin.orders.trash', $this->queries->trashData());
    }

    public function restore(BulkOrderIdsRequest $request)
    {
        $this->authorize('restore', Order::class);

        $this->orders->restore($request->input('ids', []));

        return back()->with('success', 'Khôi phục đơn hàng thành công.');
    }

    public function forceDelete(BulkOrderIdsRequest $request)
    {
        $this->authorize('forceDelete', Order::class);

        try {
            $this->orders->forceDelete($request->input('ids', []));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã xóa vĩnh viễn các đơn hàng đã chọn.');
    }
}
