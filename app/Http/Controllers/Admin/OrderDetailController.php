<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderDetails\StoreOrderDetailRequest;
use App\Models\OrderDetail;
use App\Services\Admin\OrderDetails\AdminOrderDetailService;

class OrderDetailController extends Controller
{
    public function __construct(protected AdminOrderDetailService $orderDetails) {}

    public function index()
    {
        $this->authorize('viewAny', OrderDetail::class);

        return view('admin.orders_details.index', $this->orderDetails->indexData());
    }

    public function store(StoreOrderDetailRequest $request)
    {
        $this->authorize('create', OrderDetail::class);

        $this->orderDetails->create($request->validated());

        return back()->with('success', 'Thêm chi tiết đơn hàng thành công');
    }

    public function destroy($id)
    {
        $this->authorize('deleteAny', OrderDetail::class);

        $this->orderDetails->delete((int) $id);

        return back()->with('success', 'Đã xoá chi tiết đơn hàng');
    }
}
