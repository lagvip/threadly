<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\IndexInventoryReceiptsRequest;
use App\Http\Requests\Admin\Inventory\StoreInventoryReceiptRequest;
use App\Models\InventoryReceipt;
use App\Services\Admin\Inventory\AdminInventoryReceiptQueryService;
use App\Services\Admin\Inventory\AdminInventoryReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class InventoryReceiptController extends Controller
{
    public function __construct(
        protected AdminInventoryReceiptQueryService $queries,
        protected AdminInventoryReceiptService $receipts
    ) {
    }

    public function index(IndexInventoryReceiptsRequest $request)
    {
        return view('admin.inventory.receipts.index', $this->queries->indexData($request->filters()));
    }

    public function create()
    {
        return view('admin.inventory.receipts.create', $this->queries->createData());
    }

    public function store(StoreInventoryReceiptRequest $request)
    {
        try {
            $receipt = $this->receipts->create(
                $request->validated(),
                (int) Auth::id(),
                $request->shouldPostNow()
            );

            return redirect()
                ->route('admin.inventory.receipts.show', $receipt->id)
                ->with('success', $request->shouldPostNow() ? 'Đã tạo và xác nhận phiếu nhập.' : 'Đã lưu phiếu nhập nháp.');
        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function show(InventoryReceipt $receipt)
    {
        return view('admin.inventory.receipts.show', $this->queries->showData($receipt));
    }

    public function post(InventoryReceipt $receipt)
    {
        try {
            $this->receipts->post($receipt, (int) Auth::id());

            return back()->with('success', 'Đã xác nhận nhập kho.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(InventoryReceipt $receipt)
    {
        try {
            $this->receipts->cancel($receipt, (int) Auth::id());

            return back()->with('success', 'Đã hủy phiếu nhập.');
        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function movements(IndexInventoryReceiptsRequest $request)
    {
        return view('admin.inventory.movements.index', $this->queries->movementsData($request->filters()));
    }

    public function searchProducts(Request $request)
    {
        return response()->json(
            $this->queries->productSearchData((string) $request->query('keyword', ''))
        );
    }

    public function productVariants(Request $request)
    {
        return response()->json(
            $this->queries->productVariantData((int) $request->query('product_id'))
        );
    }
}
