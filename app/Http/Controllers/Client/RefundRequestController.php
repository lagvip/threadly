<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Http\Requests\Client\Refunds\StoreRefundRequest;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Services\Client\Refunds\ClientRefundPageService;
use App\Services\Client\Refunds\ClientRefundRequestService;
use Illuminate\Support\Facades\Auth;
use RuntimeException;

class RefundRequestController extends Controller
{
    public function __construct(
        protected ClientRefundPageService $pages,
        protected ClientRefundRequestService $refunds
    ) {}

    public function create(Order $order)
    {
        $this->authorize('createForOrder', [RefundRequest::class, $order]);

        try {
            return view('client.refunds.create', $this->pages->createData($order, (int) Auth::id()));
        } catch (RuntimeException $e) {
            return redirect()
                ->route('client.orders.index')
                ->with('error', $e->getMessage());
        }
    }

    public function store(StoreRefundRequest $request, Order $order)
    {
        $this->authorize('createForOrder', [RefundRequest::class, $order]);

        try {
            $this->refunds->submit($request->validated(), $request->file('evidences', []), $order, (int) Auth::id());
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('error', $e->getMessage() ?: 'Gửi yêu cầu hoàn tiền thất bại.');
        }

        return redirect()
            ->route('client.orders.index')
            ->with('success', 'Đã gửi yêu cầu hoàn tiền. Admin sẽ kiểm tra bằng chứng và xử lý.');
    }

    public function wallet()
    {
        return view('client.wallet.index', $this->pages->walletData((int) Auth::id()));
    }
}
