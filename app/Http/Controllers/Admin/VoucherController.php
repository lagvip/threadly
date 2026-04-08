<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Voucher;
use Carbon\Carbon;
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
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'quantity' => 'nullable|integer|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'max_uses_per_user' => 'required|integer|min:1',
            'max_uses_per_order' => 'required|integer|min:1'
        ]);

        if ($request->filled('start_date') && $request->filled('end_date') && Carbon::parse($request->end_date)->lte(Carbon::parse($request->start_date))) {
            return back()->withErrors(['end_date' => 'Ngày kết thúc phải sau ngày bắt đầu'])->withInput();
        }

       
        if ($request->type == 'percent' && $request->value > 100) {
            return back()->withErrors(['value' => 'Phần trăm giảm không được vượt quá 100%'])->withInput();
        }

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->format('Y-m-d H:i:s')
            : Carbon::now()->format('Y-m-d H:i:s');

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->format('Y-m-d H:i:s')
            : Carbon::now()->addYears(10)->format('Y-m-d H:i:s');

        $quantity = $request->filled('quantity') ? (int) $request->quantity : 0;

        Voucher::create([
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'max_discount' => $request->max_discount,
            'min_order_value' => $request->min_order_value,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'quantity' => $quantity,
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
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date',
            'quantity' => 'nullable|integer|min:0',
            'min_order_value' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'max_uses_per_user' => 'required|integer|min:1',
            'max_uses_per_order' => 'required|integer|min:1'
        ]);

        if ($request->filled('start_date') && $request->filled('end_date') && Carbon::parse($request->end_date)->lte(Carbon::parse($request->start_date))) {
            return back()->withErrors(['end_date' => 'Ngày kết thúc phải sau ngày bắt đầu'])->withInput();
        }

       
        if ($request->type == 'percent' && $request->value > 100) {
            return back()->withErrors(['value' => 'Phần trăm giảm không được vượt quá 100%'])->withInput();
        }

        $startDate = $request->filled('start_date')
            ? Carbon::parse($request->start_date)->format('Y-m-d H:i:s')
            : Carbon::now()->format('Y-m-d H:i:s');

        $endDate = $request->filled('end_date')
            ? Carbon::parse($request->end_date)->format('Y-m-d H:i:s')
            : Carbon::now()->addYears(10)->format('Y-m-d H:i:s');

        $quantity = $request->filled('quantity') ? (int) $request->quantity : 0;

        $voucher->update([
            'code' => $request->code,
            'type' => $request->type,
            'value' => $request->value,
            'max_discount' => $request->max_discount,
            'min_order_value' => $request->min_order_value,
            'start_date' => $startDate,
            'end_date' => $endDate,
            'quantity' => $quantity,
            'max_uses_per_user' => $request->max_uses_per_user,
            'max_uses_per_order' => $request->max_uses_per_order,
        ]);

        return redirect()->route('vouchers.index')
            ->with('success','Đã cập nhật voucher');
    }

   
    public function destroy(Voucher $voucher)
    {
        if ($voucher->hasAppliedOrders()) {
            return redirect()->route('vouchers.index')
                ->with('error','Không thể xóa voucher đang áp dụng cho đơn hàng');
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