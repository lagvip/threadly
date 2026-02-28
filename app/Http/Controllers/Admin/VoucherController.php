<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /**
     * Hiển thị danh sách voucher
     */
    public function index()
    {
        $vouchers = Voucher::orderBy('id','desc')->get();
        return view('admin.vouchers.index', compact('vouchers'));
    }

    /**
     * Form tạo voucher
     */
    public function create()
    {
        return view('admin.vouchers.create');
    }

    /**
     * Lưu voucher mới
     */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:vouchers,code',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'quantity' => 'required|integer|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0'
        ]);

        Voucher::create($request->all());

        return redirect()->route('vouchers.index')
            ->with('success','Đã tạo voucher thành công');
    }

    /**
     * Form chỉnh sửa
     */
    public function edit(Voucher $voucher)
    {
        return view('admin.vouchers.edit', compact('voucher'));
    }

    /**
     * Cập nhật voucher
     */
    public function update(Request $request, Voucher $voucher)
    {
        $request->validate([
            'code' => 'required|unique:vouchers,code,'.$voucher->id,
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:1',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'quantity' => 'required|integer|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0'
        ]);

        $voucher->update($request->all());

        return redirect()->route('vouchers.index')
            ->with('success','Đã cập nhật voucher');
    }

    /**
     * Xóa voucher
     */
    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        return redirect()->route('vouchers.index')
            ->with('success','Đã xóa voucher');
    }
}
