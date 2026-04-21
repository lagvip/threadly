<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Illuminate\Http\Request;

class VoucherController extends Controller
{
   
    public function index(Request $request)
    {
        $search = $request->get('search');
        $type = $request->get('type');
        $status = $request->get('status');
        
        $query = Voucher::orderBy('id','desc');
        
        if ($search) {
            $query->where('code', 'like', '%' . $search . '%');
        }
        
        if ($type && $type !== '') {
            $query->where('type', $type);
        }
        
        if ($status && $status !== '') {
            $query->where('status', $status);
        }
        
        $vouchers = $query->paginate(10)->appends(request()->query());
        return view('admin.vouchers.index', compact('vouchers', 'search', 'type', 'status'));
    }

   
    public function create()
    {
        return view('admin.vouchers.create');
    }

  
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
            'max_discount' => 'nullable|numeric|min:0',
            'max_uses_per_user' => 'required|integer|min:1',
            'max_uses_per_order' => 'required|integer|min:1'
        ]);

       
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
            'max_uses_per_user' => $request->max_uses_per_user,
            'max_uses_per_order' => $request->max_uses_per_order,
            'status' => 'active'
        ]);

        return redirect()->route('vouchers.index')
            ->with('success','Đã tạo voucher thành công');
    }

   
    public function edit(Voucher $voucher)
    {
        return view('admin.vouchers.edit', compact('voucher'));
    }

 
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
            'max_discount' => 'nullable|numeric|min:0',
            'max_uses_per_user' => 'required|integer|min:1',
            'max_uses_per_order' => 'required|integer|min:1'
        ]);

       
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
            'max_uses_per_user' => $request->max_uses_per_user,
            'max_uses_per_order' => $request->max_uses_per_order,
        ]);

        return redirect()->route('vouchers.index')
            ->with('success','Đã cập nhật voucher');
    }

   
    public function destroy(Voucher $voucher)
    {
        if ($voucher->isInUse()) {
            return redirect()->route('vouchers.index')
                ->with('error','Không thể xóa voucher đang áp dụng');
        }

        $voucher->delete();

        return redirect()->route('vouchers.index')
            ->with('success','Đã xóa voucher');
    }

    
    public function trashed()
    {
        $vouchers = Voucher::onlyTrashed()->paginate(10);
        return view('admin.vouchers.trashed', compact('vouchers'));
    }

    
    public function restore($id)
    {
        $voucher = Voucher::withTrashed()->findOrFail($id);
        $voucher->restore();

        return redirect()->route('vouchers.trashed')
            ->with('success','Đã khôi phục voucher');
    }

   
    public function forceDelete($id)
    {
        $voucher = Voucher::withTrashed()->findOrFail($id);
        $voucher->forceDelete();

        return redirect()->route('vouchers.trashed')
            ->with('success','Đã xóa vĩnh viễn voucher');
    }
}