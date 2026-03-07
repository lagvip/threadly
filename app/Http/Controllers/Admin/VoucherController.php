<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
    /* ================= DANH SÁCH ================= */
    public function index()
    {
        $vouchers = Voucher::orderBy('id','desc')->get();
        return view('admin.vouchers.index', compact('vouchers'));
    }

    /* ================= FORM THÊM ================= */
    public function create()
    {
        return view('admin.vouchers.create');
    }

    /* ================= LƯU MỚI ================= */
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:vouchers,code',
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:1',
            'start_date' => 'required',
            'end_date' => 'required|after:start_date',
            'quantity' => 'required|integer|min:1',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0'
        ]);

        // Kiểm tra validation thêm cho % discount
        if ($request->type == 'percent' && $request->value > 100) {
            return back()->withErrors(['value' => 'Phần trăm giảm không được vượt quá 100%'])->withInput();
        }

        Voucher::create([
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'max_discount' => $request->max_discount,
            'min_order_value' => $request->min_order_value,
            'start_date' => str_replace('T', ' ', $request->start_date),
            'end_date' => str_replace('T', ' ', $request->end_date),
            'quantity' => $request->quantity,
            'status' => 'active'
        ]);

        return redirect()->route('vouchers.index')
            ->with('success','Đã tạo voucher thành công');
    }

    /* ================= FORM SỬA ================= */
    public function edit(Voucher $voucher)
    {
        return view('admin.vouchers.edit', compact('voucher'));
    }

    /* ================= CẬP NHẬT ================= */
    public function update(Request $request, Voucher $voucher)
    {
        $request->validate([
            'code' => 'required|unique:vouchers,code,'.$voucher->id,
            'type' => 'required|in:percent,fixed',
            'value' => 'required|numeric|min:1',
            'start_date' => 'required',
            'end_date' => 'required|after:start_date',
            'quantity' => 'required|integer|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0'
        ]);

        // Kiểm tra validation thêm cho % discount
        if ($request->type == 'percent' && $request->value > 100) {
            return back()->withErrors(['value' => 'Phần trăm giảm không được vượt quá 100%'])->withInput();
        }

        $voucher->update([
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'max_discount' => $request->max_discount,
            'min_order_value' => $request->min_order_value,
            'start_date' => str_replace('T', ' ', $request->start_date),
            'end_date' => str_replace('T', ' ', $request->end_date),
            'quantity' => $request->quantity,
        ]);

        return redirect()->route('vouchers.index')
            ->with('success','Đã cập nhật voucher');
    }

    /* ================= XOÁ ================= */
    public function destroy(Voucher $voucher)
    {
        $voucher->delete();

        return redirect()->route('vouchers.index')
            ->with('success','Đã xóa voucher');
    }
}