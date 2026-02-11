<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Shipping;

class ShippingController extends Controller
{
    // 1. Hiển thị danh sách
    public function index()
    {
        // Lấy danh sách mới nhất
        $shippings = Shipping::orderBy('created_at', 'desc')->get();
        
        // Trả về view kèm biến $shippings
        // Biến $shippingEdit = null để view biết là đang ở chế độ Thêm mới
        return view('admin.shipping.index', [
            'shippings' => $shippings,
            'shippingEdit' => null 
        ]);
    }

    // 2. Thêm mới
    public function store(Request $request)
    {
        $request->validate([
            'provider_name' => 'required|string|max:255',
            'price_inner' => 'required|numeric|min:0', // Validate giá nội thành
            'price_outer' => 'required|numeric|min:0', // Validate giá ngoại thành
        ]);

        // Mặc định status là 1 nếu không chọn
        $data = $request->all();
        $data['status'] = $request->input('status', 1);

        Shipping::create($data);

        return redirect()->route('admin.shipping.index')->with('success', 'Thêm mới thành công!');
    }

    // 3. Lấy thông tin để sửa (Hiển thị lại form với dữ liệu cũ)
    public function edit($id)
    {
        $shippings = Shipping::orderBy('created_at', 'desc')->get();
        $shippingEdit = Shipping::findOrFail($id); // Tìm bản ghi cần sửa

        // Vẫn trả về view index nhưng có thêm biến $shippingEdit
        return view('admin.shipping.index', compact('shippings', 'shippingEdit'));
    }

    // 4. Cập nhật dữ liệu
    public function update(Request $request, $id)
    {
        $request->validate([
            'provider_name' => 'required|string|max:255',
            'price_inner' => 'required|numeric|min:0',
            'price_outer' => 'required|numeric|min:0',
        ]);

        $shipping = Shipping::findOrFail($id);
        $shipping->update($request->all());

        return redirect()->route('admin.shipping.index')->with('success', 'Cập nhật thành công!');
    }

    // 5. Xóa
    public function destroy($id)
    {
        Shipping::destroy($id);
        return redirect()->back()->with('success', 'Đã xóa vận chuyển thành công!');
    }
}

