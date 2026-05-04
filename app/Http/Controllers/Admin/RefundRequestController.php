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
        // Lấy danh sách yêu cầu hoàn tiền, load kèm order, user, bằng chứng và sản phẩm hoàn.
        $query = RefundRequest::with(['order', 'user', 'evidences', 'items'])
            ->latest('id');

        // Lọc theo trạng thái hoàn tiền nếu admin chọn.
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Tìm kiếm theo mã đơn, email hoặc tên khách hàng.
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

        // Phân trang và giữ lại query string khi chuyển trang.
        $refundRequests = $query->paginate(10)->withQueryString();

        // Đếm nhanh số yêu cầu theo từng trạng thái.
        $counts = [
            'pending' => RefundRequest::where('status', RefundRequest::STATUS_PENDING)->count(),
            'approved' => RefundRequest::where('status', RefundRequest::STATUS_APPROVED)->count(),
            'rejected' => RefundRequest::where('status', RefundRequest::STATUS_REJECTED)->count(),
        ];

        // Trả dữ liệu sang trang danh sách hoàn tiền admin.
        return view('admin.refunds.index', compact('refundRequests', 'counts'));
    }

    public function show(RefundRequest $refundRequest)
    {
        // Load đầy đủ dữ liệu để admin xem chi tiết yêu cầu hoàn tiền.
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

        // Trả sang view chi tiết yêu cầu hoàn tiền.
        return view('admin.refunds.show', compact('refundRequest'));
    }

    public function approve(Request $request, RefundRequest $refundRequest)
    {
        // Chỉ duyệt yêu cầu đang pending, tránh duyệt lại yêu cầu đã xử lý.
        if ($refundRequest->status !== RefundRequest::STATUS_PENDING) {
            return back()->with('error', 'Yêu cầu hoàn tiền này đã được xử lý trước đó.');
        }

        // Admin có thể nhập ghi chú khi duyệt.
        $request->validate([
            'admin_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            // Dùng transaction vì duyệt hoàn tiền gồm nhiều bước: lock request, lock order, cộng ví, tạo giao dịch, update order.
            DB::transaction(function () use ($request, $refundRequest) {
                // Lock yêu cầu hoàn tiền để tránh 2 admin duyệt cùng lúc.
                $refundRequest = RefundRequest::with('items')
                    ->whereKey($refundRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Kiểm tra lại trạng thái sau khi lock.
                if ($refundRequest->status !== RefundRequest::STATUS_PENDING) {
                    throw new \RuntimeException('Yêu cầu hoàn tiền này đã được xử lý trước đó.');
                }

                // Lock đơn hàng liên quan để cập nhật số tiền hoàn an toàn.
                $order = Order::whereKey($refundRequest->order_id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Chỉ hỗ trợ hoàn tiền demo cho COD và VNPay.
                if (!in_array($order->payment_method, [Order::PAYMENT_METHOD_VNPAY, Order::PAYMENT_METHOD_COD], true)) {
                    throw new \RuntimeException('Phương thức thanh toán của đơn hàng không hỗ trợ hoàn tiền demo.');
                }

                // Case 1: đơn đã giao thành công và đã thanh toán.
                $isDeliveredRefund = $order->payment_status === Order::PAYMENT_PAID
                    && $order->order_status === Order::STATUS_DELIVERED;

                // Case 2: đơn VNPay đã paid nhưng đã hủy trước khi tạo vận đơn GHN.
                $isPaidVnpayCancelledRefund = $order->payment_method === Order::PAYMENT_METHOD_VNPAY
                    && $order->payment_status === Order::PAYMENT_PAID
                    && $order->order_status === Order::STATUS_CANCELLED
                    && ($order->refund_status ?? Order::REFUND_NONE) === Order::REFUND_REQUESTED
                    && empty($order->ghn_order_code);

                // Chỉ duyệt nếu thuộc 1 trong 2 case hợp lệ.
                if (!$isDeliveredRefund && !$isPaidVnpayCancelledRefund) {
                    throw new \RuntimeException('Chỉ duyệt hoàn tiền cho đơn đã giao thành công hoặc đơn VNPay đã thanh toán nhưng hủy trước khi xử lý.');
                }

                // Nếu đơn có GHN thì GHN phải xác nhận delivered mới cho hoàn sau giao.
                if ($isDeliveredRefund && $order->ghn_order_code && $order->ghn_status !== 'delivered') {
                    throw new \RuntimeException('Đơn có vận đơn GHN nhưng GHN chưa xác nhận giao thành công.');
                }

                // Chặn cộng ví trùng cho cùng refund request.
                if (WalletTransaction::where('refund_request_id', $refundRequest->id)
                    ->where('type', WalletTransaction::TYPE_REFUND_CREDIT)
                    ->exists()) {
                    throw new \RuntimeException('Yêu cầu hoàn tiền này đã được cộng ví trước đó.');
                }

                // Số tiền duyệt là số tiền khách yêu cầu.
                $approvedAmount = (float) $refundRequest->requested_amount;
                $maxAmount = (float) $order->refundable_amount;

                // Không cho hoàn quá số tiền còn có thể hoàn của đơn.
                if ($approvedAmount <= 0 || $approvedAmount > $maxAmount) {
                    throw new \RuntimeException('Số tiền duyệt hoàn không được vượt quá số tiền còn lại có thể hoàn.');
                }

                // Hoàn một phần bắt buộc phải có item được chọn.
                if ($refundRequest->type === RefundRequest::TYPE_PARTIAL && $refundRequest->items->isEmpty()) {
                    throw new \RuntimeException('Yêu cầu hoàn theo sản phẩm không có sản phẩm nào được chọn.');
                }

                // Lấy hoặc tạo ví cho khách hàng.
                $wallet = Wallet::firstOrCreate(
                    ['user_id' => $refundRequest->user_id],
                    ['balance' => 0]
                );

                // Lock ví để cộng tiền an toàn.
                $wallet = Wallet::whereKey($wallet->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $balanceBefore = (float) $wallet->balance;
                $balanceAfter = $balanceBefore + $approvedAmount;

                // Cộng tiền hoàn vào ví.
                $wallet->update([
                    'balance' => $balanceAfter,
                ]);

                // Tạo lịch sử giao dịch ví.
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

                // Cập nhật tổng tiền đã hoàn của order.
                $newRefundedAmount = (float) $order->refunded_amount + $approvedAmount;

                // Kiểm tra đơn đã hoàn đủ tiền hàng chưa.
                $isFullyRefunded = $newRefundedAmount >= ((float) $order->refundable_product_amount - 0.01);

                // Cập nhật trạng thái hoàn tiền của đơn.
                $order->update([
                    'refunded_amount' => $newRefundedAmount,
                    'refund_status' => $isFullyRefunded ? Order::REFUND_REFUNDED : Order::REFUND_PARTIALLY_REFUNDED,
                    'last_refunded_at' => now(),
                ]);

                // Cập nhật yêu cầu hoàn tiền sang approved.
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
        // Admin có thể nhập ghi chú khi nhập lại kho.
        $request->validate([
            'restock_note' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            // Dùng transaction vì nhập kho cập nhật cả variant, item hoàn và refund request.
            DB::transaction(function () use ($request, $refundRequest) {
                // Lock refund request để tránh nhập kho trùng.
                $refundRequest = RefundRequest::with(['items.orderDetail'])
                    ->whereKey($refundRequest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Chỉ nhập lại kho sau khi yêu cầu hoàn đã được duyệt.
                if ($refundRequest->status !== RefundRequest::STATUS_APPROVED) {
                    throw new \RuntimeException('Chỉ được nhập kho sau khi yêu cầu hoàn tiền đã được duyệt.');
                }

                // Nếu đã nhập kho rồi thì không cho nhập lại lần nữa.
                if ($refundRequest->restocked_at) {
                    throw new \RuntimeException('Yêu cầu hoàn này đã được nhập lại kho trước đó.');
                }

                // Phải có item hoàn thì mới biết cần nhập kho sản phẩm nào.
                if ($refundRequest->items->isEmpty()) {
                    throw new \RuntimeException('Yêu cầu hoàn không có dòng sản phẩm để nhập kho.');
                }

                // Duyệt từng sản phẩm trong yêu cầu hoàn để cộng lại tồn kho.
                foreach ($refundRequest->items as $item) {
                    $restockedQuantity = (int) ($item->restocked_quantity ?? 0);

                    // Chỉ nhập phần chưa từng được nhập kho.
                    $quantityToRestock = max((int) $item->quantity - $restockedQuantity, 0);

                    if ($quantityToRestock <= 0) {
                        continue;
                    }

                    $detail = $item->orderDetail;

                    // Phải tìm được order detail và variant_id để nhập kho đúng biến thể.
                    if (!$detail || !$detail->variant_id) {
                        throw new \RuntimeException('Không tìm thấy biến thể sản phẩm để nhập lại kho cho dòng hoàn: ' . $item->product_name_snapshot);
                    }

                    // Lock biến thể để cộng kho an toàn.
                    $variant = ProductVariant::whereKey($detail->variant_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$variant) {
                        throw new \RuntimeException('Biến thể sản phẩm không còn tồn tại: ' . $item->product_name_snapshot);
                    }

                    // Cộng lại tồn kho cho biến thể.
                    $variant->increment('quantity', $quantityToRestock);

                    // Đánh dấu item đã nhập kho số lượng này.
                    $item->update([
                        'restocked_quantity' => $restockedQuantity + $quantityToRestock,
                        'restocked_at' => now(),
                    ]);
                }

                // Đánh dấu toàn bộ yêu cầu hoàn đã được nhập lại kho.
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
        // Chỉ từ chối yêu cầu còn pending.
        if ($refundRequest->status !== RefundRequest::STATUS_PENDING) {
            return back()->with('error', 'Yêu cầu hoàn tiền này đã được xử lý trước đó.');
        }

        // Từ chối bắt buộc nhập lý do.
        $request->validate([
            'admin_note' => ['required', 'string', 'max:2000'],
        ], [
            'admin_note.required' => 'Vui lòng nhập lý do từ chối hoàn tiền.',
        ]);

        // Dùng transaction để update refund request và order cùng lúc.
        DB::transaction(function () use ($request, $refundRequest) {
            // Lock refund request và order để tránh xử lý trùng.
            $refundRequest = RefundRequest::whereKey($refundRequest->id)
                ->lockForUpdate()
                ->firstOrFail();

            $order = Order::whereKey($refundRequest->order_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Cập nhật yêu cầu hoàn tiền sang rejected.
            $refundRequest->update([
                'status' => RefundRequest::STATUS_REJECTED,
                'admin_id' => Auth::id(),
                'admin_note' => trim((string) $request->admin_note) ?: null,
                'rejected_at' => now(),
            ]);

            // Nếu đơn đã từng hoàn một phần thì giữ partially_refunded, nếu chưa hoàn gì thì rejected.
            $nextStatus = ((float) $order->refunded_amount) > 0
                ? Order::REFUND_PARTIALLY_REFUNDED
                : Order::REFUND_REJECTED;

            // Cập nhật trạng thái hoàn tiền của đơn.
            $order->update([
                'refund_status' => $nextStatus,
            ]);
        });

        return redirect()
            ->route('admin.refunds.show', $refundRequest->id)
            ->with('success', 'Đã từ chối yêu cầu hoàn tiền.');
    }
}
