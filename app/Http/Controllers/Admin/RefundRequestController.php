<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Refunds\ApproveRefundRequest;
use App\Http\Requests\Admin\Refunds\IndexRefundRequestsRequest;
use App\Http\Requests\Admin\Refunds\RejectRefundRequest;
use App\Http\Requests\Admin\Refunds\RestockRefundRequest;
use App\Models\RefundRequest;
use App\Services\Admin\Refunds\AdminRefundQueryService;
use App\Services\Admin\Refunds\AdminRefundWorkflowService;
use Illuminate\Support\Facades\Auth;

class RefundRequestController extends Controller
{
    public function __construct(
        protected AdminRefundQueryService $queries,
        protected AdminRefundWorkflowService $refunds,
    ) {}

    public function index(IndexRefundRequestsRequest $request)
    {
        $this->authorize('viewAny', RefundRequest::class);

        return view('admin.refunds.index', $this->queries->paginated($request->validated()));
    }

    public function show(RefundRequest $refundRequest)
    {
        $this->authorize('view', $refundRequest);

        return view('admin.refunds.show', $this->queries->showData($refundRequest));
    }

    public function approve(
        ApproveRefundRequest $request,
        RefundRequest $refundRequest
    ) {
        $this->authorize('approve', $refundRequest);

        try {
            $this->refunds->approve($refundRequest, Auth::id(), $request->input('admin_note'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.refunds.show', $refundRequest->id)
            ->with('success', 'Đã duyệt hoàn tiền và cộng tiền vào ví demo của khách hàng.');
    }

    public function restock(
        RestockRefundRequest $request,
        RefundRequest $refundRequest
    ) {
        $this->authorize('restock', $refundRequest);

        try {
            $this->refunds->restock($refundRequest, Auth::id(), $request->input('restock_note'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('success', 'Đã nhập lại kho các sản phẩm trong yêu cầu hoàn.');
    }

    public function reject(
        RejectRefundRequest $request,
        RefundRequest $refundRequest
    ) {
        $this->authorize('reject', $refundRequest);

        try {
            $this->refunds->reject($refundRequest, Auth::id(), (string) $request->input('admin_note'));
        } catch (\RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()
            ->route('admin.refunds.show', $refundRequest->id)
            ->with('success', 'Đã từ chối yêu cầu hoàn tiền.');
    }
}
