<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\Wallet;
use App\Models\WalletTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RefundRequestController extends Controller
{
    public function index(Request $request)
    {
        $query = RefundRequest::with(['order', 'user', 'evidences', 'items'])
            ->latest('id');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('keyword')) {
            $keyword = trim((string) $request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->whereHas('order', function ($orderQuery) use ($keyword) {
                    $orderQuery->where('order_code', 'like', '%' . $keyword . '%');
                })->orWhereHas('user', function ($userQuery) use ($keyword) {
                    $userQuery->where('email', 'like', '%' . $keyword . '%')
                        ->orWhere('name', 'like', '%' . $keyword . '%');
                });
            });
        }

        $refundRequests = $query->paginate(10)->withQueryString();

        $counts = [
            'pending' => RefundRequest::where('status', RefundRequest::STATUS_PENDING)->count(),
            'approved' => RefundRequest::where('status', RefundRequest::STATUS_APPROVED)->count(),
            'rejected' => RefundRequest::where('status', RefundRequest::STATUS_REJECTED)->count(),
        ];

        return view('admin.refunds.index', compact('refundRequests', 'counts'));
    }

    public function show(RefundRequest $refundRequest)
    {
        $refundRequest->load([
            'order.details.variant.product',
            'order.details.variant.color',
            'order.details.variant.size',
            'user',
            'admin',
            'evidences',
            'items.orderDetail',
            'walletTransactions',
        ]);

        return view('admin.refunds.show', compact('refundRequest'));
    }

    public function approve(Request $request, RefundRequest $refundRequest)
    {
        if ($refundRequest->status !== RefundRequest::STATUS_PENDING) {
            return back()->with('error', 'Yêu cầu hoàn tiền này đã được xử lý trước đó.');
        }

        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::transaction(function () use ($request, $refundRequest) {
                $refundRequest = RefundRequest::with('items')
                    ->whereKey($refundRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($refundRequest->status !== RefundRequest::STATUS_PENDING) {
                    throw new \RuntimeException('Yêu cầu hoàn tiền này đã được xử lý trước đó.');
                }

                $order = Order::whereKey($refundRequest->order_id)->lockForUpdate()->firstOrFail();
                $approvedAmount = (float) $refundRequest->requested_amount;
                $maxAmount = (float) $order->refundable_amount;

                if ($approvedAmount <= 0 || $approvedAmount > $maxAmount) {
                    throw new \RuntimeException('Số tiền duyệt hoàn không được vượt quá số tiền còn lại có thể hoàn.');
                }

                if ($refundRequest->type === RefundRequest::TYPE_PARTIAL && $refundRequest->items->isEmpty()) {
                    throw new \RuntimeException('Yêu cầu hoàn theo sản phẩm không có sản phẩm nào được chọn.');
                }

                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $refundRequest->user_id],
                    ['balance' => 0]
                );

                $wallet = Wallet::whereKey($wallet->id)->lockForUpdate()->firstOrFail();
                $balanceBefore = (float) $wallet->balance;
                $balanceAfter = $balanceBefore + $approvedAmount;

                $wallet->update([
                    'balance' => $balanceAfter,
                ]);

                WalletTransaction::create([
                    'wallet_id' => $wallet->id,
                    'user_id' => $refundRequest->user_id,
                    'order_id' => $order->id,
                    'refund_request_id' => $refundRequest->id,
                    'type' => WalletTransaction::TYPE_REFUND_CREDIT,
                    'amount' => $approvedAmount,
                    'balance_before' => $balanceBefore,
                    'balance_after' => $balanceAfter,
                    'description' => 'Hoàn tiền demo cho đơn #' . $order->order_code,
                ]);

                $newRefundedAmount = (float) $order->refunded_amount + $approvedAmount;
                $isFullyRefunded = $newRefundedAmount >= ((float) $order->refundable_product_amount - 0.01);

                $order->update([
                    'refunded_amount' => $newRefundedAmount,
                    'refund_status' => $isFullyRefunded ? Order::REFUND_REFUNDED : Order::REFUND_PARTIALLY_REFUNDED,
                    'last_refunded_at' => now(),
                ]);

                $refundRequest->update([
                    'approved_amount' => $approvedAmount,
                    'status' => RefundRequest::STATUS_APPROVED,
                    'admin_id' => Auth::id(),
                    'admin_note' => trim((string) $request->admin_note) ?: null,
                    'approved_at' => now(),
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Duyệt hoàn tiền thất bại.');
        }

        return redirect()
            ->route('admin.refunds.show', $refundRequest->id)
            ->with('success', 'Đã duyệt hoàn tiền và cộng tiền vào ví demo của khách hàng.');
    }

    public function reject(Request $request, RefundRequest $refundRequest)
    {
        if ($refundRequest->status !== RefundRequest::STATUS_PENDING) {
            return back()->with('error', 'Yêu cầu hoàn tiền này đã được xử lý trước đó.');
        }

        $request->validate([
            'admin_note' => ['required', 'string', 'max:2000'],
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối hoàn tiền.',
        ]);

        DB::transaction(function () use ($request, $refundRequest) {
            $refundRequest = RefundRequest::whereKey($refundRequest->id)->lockForUpdate()->firstOrFail();
            $order = Order::whereKey($refundRequest->order_id)->lockForUpdate()->firstOrFail();

            $refundRequest->update([
                'status' => RefundRequest::STATUS_REJECTED,
                'admin_id' => Auth::id(),
                'admin_note' => trim((string) $request->admin_note) ?: null,
                'rejected_at' => now(),
            ]);

            $nextStatus = ((float) $order->refunded_amount) > 0
                ? Order::REFUND_PARTIALLY_REFUNDED
                : Order::REFUND_REJECTED;

            $order->update([
                'refund_status' => $nextStatus,
            ]);
        });

        return redirect()
            ->route('admin.refunds.show', $refundRequest->id)
            ->with('success', 'Đã từ chối yêu cầu hoàn tiền.');
    }
}
