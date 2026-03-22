<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderDetail;

class OrderDetailController extends Controller
{
       public function index()
    {
        $orderDetails = OrderDetail::with('order', 'variant.product', 'variant.color', 'variant.size')->get();
        return view('admin.orders_details.index', compact('orderDetails'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'id_order' => 'required|exists:orders,id',
            'id_variant' => 'required|exists:product_variants,id',
            'variant_data' => 'required|array',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
        ]);

        $data['total'] = $data['quantity'] * $data['unit_price'];

        OrderDetail::create($data);

        return back()->with('success', 'Thêm chi tiết đơn hàng thành công');
    }

    public function destroy($id)
    {
        OrderDetail::findOrFail($id)->delete();
        return back()->with('success', 'Đã xoá chi tiết đơn hàng');
    }
}
