<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
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

                if (!in_array($order->payment_method, [Order::PAYMENT_METHOD_VNPAY, Order::PAYMENT_METHOD_COD], true)) {
                    throw new \RuntimeException('Phương thức thanh toán của đơn hàng không hỗ trợ hoàn tiền demo.');
                }

                if ($order->payment_status !== Order::PAYMENT_PAID || $order->order_status !== Order::STATUS_DELIVERED) {
                    throw new \RuntimeException('Chỉ duyệt hoàn tiền cho đơn đã giao thành công và đã thanh toán.');
                }

                if ($order->ghn_order_code && $order->ghn_status !== 'delivered') {
                    throw new \RuntimeException('Đơn có vận đơn GHN nhưng GHN chưa xác nhận giao thành công.');
                }

                if (WalletTransaction::where('refund_request_id', $refundRequest->id)
                    ->where('type', WalletTransaction::TYPE_REFUND_CREDIT)
                    ->exists()) {
                    throw new \RuntimeException('Yêu cầu hoàn tiền này đã được cộng ví trước đó.');
                }

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
                    'description' => 'Hoàn tiền demo vào ví cho đơn #' . $order->order_code,
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


    public function restock(Request $request, RefundRequest $refundRequest)
    {
        $request->validate([
            'restock_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::transaction(function () use ($request, $refundRequest) {
                $refundRequest = RefundRequest::with(['items.orderDetail'])
                    ->whereKey($refundRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($refundRequest->status !== RefundRequest::STATUS_APPROVED) {
                    throw new \RuntimeException('Chỉ được nhập kho sau khi yêu cầu hoàn tiền đã được duyệt.');
                }

                if ($refundRequest->restocked_at) {
                    throw new \RuntimeException('Yêu cầu hoàn này đã được nhập lại kho trước đó.');
                }

                if ($refundRequest->items->isEmpty()) {
                    throw new \RuntimeException('Yêu cầu hoàn không có dòng sản phẩm để nhập kho.');
                }

                foreach ($refundRequest->items as $item) {
                    $restockedQuantity = (int) ($item->restocked_quantity ?? 0);
                    $quantityToRestock = max((int) $item->quantity - $restockedQuantity, 0);

                    if ($quantityToRestock <= 0) {
                        continue;
                    }

                    $detail = $item->orderDetail;

                    if (!$detail || !$detail->variant_id) {
                        throw new \RuntimeException('Không tìm thấy biến thể sản phẩm để nhập lại kho cho dòng hoàn: ' . $item->product_name_snapshot);
                    }

                    $variant = ProductVariant::whereKey($detail->variant_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$variant) {
                        throw new \RuntimeException('Biến thể sản phẩm không còn tồn tại: ' . $item->product_name_snapshot);
                    }

                    $variant->increment('quantity', $quantityToRestock);

                    $item->update([
                        'restocked_quantity' => $restockedQuantity + $quantityToRestock,
                        'restocked_at' => now(),
                    ]);
                }

                $refundRequest->update([
                    'restocked_at' => now(),
                    'restocked_by' => Auth::id(),
                    'restock_note' => trim((string) $request->restock_note) ?: null,
                ]);
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Nhập lại kho thất bại.');
        }

        return back()->with('success', 'Đã nhập lại kho các sản phẩm trong yêu cầu hoàn.');
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
