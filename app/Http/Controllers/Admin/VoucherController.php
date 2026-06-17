<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Vouchers\IndexVouchersRequest;
use App\Http\Requests\Admin\Vouchers\StoreVoucherRequest;
use App\Http\Requests\Admin\Vouchers\UpdateVoucherRequest;
use App\Models\Voucher;
use App\Services\Admin\Vouchers\AdminVoucherQueryService;
use App\Services\Admin\Vouchers\AdminVoucherService;
use RuntimeException;

class VoucherController extends Controller
{
    public function __construct(
        protected AdminVoucherQueryService $queries,
        protected AdminVoucherService $vouchers
    ) {}

    public function index(IndexVouchersRequest $request)
    {
        $this->authorize('viewAny', Voucher::class);

        return view('admin.vouchers.index', $this->queries->indexData($request->filters()));
    }

    public function create()
    {
        $this->authorize('create', Voucher::class);

        return view('admin.vouchers.create', $this->queries->formData());
    }

    public function store(StoreVoucherRequest $request)
    {
        $this->authorize('create', Voucher::class);

        try {
            $this->vouchers->create($request->validated());

            return redirect()->route('vouchers.index')->with('success', 'Đã tạo voucher thành công');
        } catch (RuntimeException $e) {
            return back()->withErrors(['end_date' => $e->getMessage()])->withInput();
        }
    }

    public function show(Voucher $voucher)
    {
        $this->authorize('view', $voucher);

        return view('admin.vouchers.edit', array_merge(compact('voucher'), $this->queries->formData()));
    }

    public function edit(Voucher $voucher)
    {
        $this->authorize('update', $voucher);

        return view('admin.vouchers.edit', array_merge(compact('voucher'), $this->queries->formData()));
    }

    public function update(UpdateVoucherRequest $request, Voucher $voucher)
    {
        $this->authorize('update', $voucher);

        try {
            $this->vouchers->update($voucher, $request->validated());

            return redirect()->route('vouchers.index')->with('success', 'Đã cập nhật voucher');
        } catch (RuntimeException $e) {
            return back()->withErrors(['end_date' => $e->getMessage()])->withInput();
        }
    }

    public function destroy(Voucher $voucher)
    {
        $this->authorize('delete', $voucher);

        try {
            $this->vouchers->softDelete($voucher);

            return redirect()->route('vouchers.index')->with('success', 'Đã xóa voucher');
        } catch (RuntimeException $e) {
            return redirect()->route('vouchers.index')->with('error', $e->getMessage());
        }
    }

    public function trashed()
    {
        $this->authorize('viewAny', Voucher::class);

        return view('admin.vouchers.trashed', $this->queries->trashedData());
    }

    public function restore($id)
    {
        $this->authorize('restore', Voucher::class);

        $this->vouchers->restore((int) $id);

        return redirect()->route('vouchers.trashed')->with('success', 'Đã khôi phục voucher');
    }

    public function forceDelete($id)
    {
        $this->authorize('forceDelete', Voucher::class);

        $this->vouchers->forceDelete((int) $id);

        return redirect()->route('vouchers.trashed')->with('success', 'Đã xóa vĩnh viễn voucher');
    }
}
