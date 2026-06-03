<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Orders\CancelClientOrderRequest;
use App\Http\Requests\Client\Orders\IndexClientOrdersRequest;
use App\Http\Requests\Client\Orders\SubmitOrderReviewRequest;
use App\Services\Client\Orders\ClientOrderQueryService;
use App\Services\Client\Orders\ClientOrderReviewService;
use App\Services\Client\Orders\ClientOrderWorkflowService;
use Illuminate\Support\Facades\Auth;

class ClientOrderController extends Controller
{
    public function __construct(
        protected ClientOrderQueryService $queries,
        protected ClientOrderWorkflowService $workflow,
        protected ClientOrderReviewService $reviews
    ) {
    }

    public function index(IndexClientOrdersRequest $request)
    {
        return view('client.orders.index', $this->queries->indexData((int) Auth::id(), $request->filters()));
    }

    public function show($id)
    {
        return view('client.orders.show', $this->queries->showData((int) $id, (int) Auth::id()));
    }

    public function confirmReceived($id)
    {
        try {
            $this->workflow->confirmReceived((int) $id, (int) Auth::id());

            return back()->with('success', 'Bạn đã xác nhận nhận hàng thành công. Cảm ơn bạn đã mua hàng.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Xác nhận nhận hàng thất bại.');
        }
    }

    public function submitReview(SubmitOrderReviewRequest $request, $id, $detailId)
    {
        try {
            $orderId = $this->reviews->submit((int) $id, (int) $detailId, (int) Auth::id(), $request->validated());

            return redirect()
                ->to(route('client.orders.show', $orderId) . '#review-section')
                ->with('success', 'Đã lưu bình luận sản phẩm thành công.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function cancel(CancelClientOrderRequest $request, $id)
    {
        try {
            $actionType = $this->workflow->cancel((int) $id, (int) Auth::id(), $request->reason());

            return back()->with('success', $this->workflow->cancelSuccessMessage($actionType));
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Hủy đơn thất bại.');
        }
    }
}
