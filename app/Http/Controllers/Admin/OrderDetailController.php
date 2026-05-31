<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderDetails\StoreOrderDetailRequest;
use App\Services\Admin\OrderDetails\AdminOrderDetailService;

class OrderDetailController extends Controller
{
    public function __construct(protected AdminOrderDetailService $orderDetails)
    {
    }

    public function index()
    {
        return view('admin.orders_details.index', $this->orderDetails->indexData());
    }

    public function store(StoreOrderDetailRequest $request)
    {
        $this->orderDetails->create($request->validated());

        return back()->with('success', 'Thêm chi tiết đơn hàng thành công');
    }

    public function destroy($id)
    {
        $this->orderDetails->delete((int) $id);

        return back()->with('success', 'Đã xoá chi tiết đơn hàng');
    }
}
