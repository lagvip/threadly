<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\OrderStatusLog;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Services\OrderInventoryService;

class ClientOrderController extends Controller
{
    public function index(Request $request)
    {
        // Lấy danh sách đơn hàng của user hiện tại, kèm dữ liệu cần hiển thị như sản phẩm, biến thể, review và refund.
        $query = Order::with([
                'details.variant',
                'details.product',
                'reviews.variant.color',
                'reviews.variant.size',
                'refundRequests.admin',
            ])
            ->where('user_id', Auth::id());

        // Lọc theo order_code.
        if ($request->filled('order_code')) {
            $keyword = trim((string) $request->order_code);

            $query->where('order_code', 'like', '%' . $keyword . '%');
        }

        // Lọc theo tên, email, số điện thoại hoặc địa chỉ.
        if ($request->filled('customer')) {
            $keyword = trim((string) $request->customer);

            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', '%' . $keyword . '%')
                    ->orWhere('email', 'like', '%' . $keyword . '%')
                    ->orWhere('phone', 'like', '%' . $keyword . '%')
                    ->orWhere('address', 'like', '%' . $keyword . '%');
            });
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        if ($request->filled('order_status')) {
            $query->where('order_status', $request->order_status);
        }

        $orders = $query
            ->latest('id')
            ->paginate(10)
            ->withQueryString();

        return view('client.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with([
                'details.variant.color',
                'details.variant.size',
                'details.product',
                'reviews.variant.color',
                'reviews.variant.size',
                'refundRequests.admin',
                'refundRequests.items',
            ])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        // Chuẩn bị danh sách sản phẩm trong đơn có thể đánh giá, đồng thời gắn review cũ nếu đã từng đánh giá.
        $reviewItems = $order->details
            ->filter(function ($item) {
                return !empty($item->product_id) && !is_null($item->product);
            })
            ->values()
            ->map(function ($item) use ($order) {
                $item->existing_review = $order->reviews->firstWhere('order_detail_id', $item->id);
                return $item;
            });

        return view('client.orders.show', compact('order', 'reviewItems'));
    }

    public function confirmReceived($id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if (!$order->can_confirm_received) {
            return back()->with('error', 'Đơn hàng chưa đủ điều kiện để xác nhận đã nhận hàng.');
        }

        // Dùng transaction lỗi bước nào thì rollback toàn bộ.
        DB::transaction(function () use ($order) {
            $order->update([
                'customer_confirmed_at' => now(),
            ]);

            // Ghi lịch sử để biết ai đã xác nhận và xác nhận lúc đơn đang ở trạng thái nào.
            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => $order->order_status,
                'note' => 'Khách hàng xác nhận đã nhận hàng.',
                'changed_by' => Auth::id(),
            ]);
        });

        return back()->with('success', 'Bạn đã xác nhận nhận hàng thành công. Cảm ơn bạn đã mua hàng.');
    }

    public function submitReview(Request $request, $id, $detailId)
    {
        $order = Order::with(['details.product', 'details.variant.color', 'details.variant.size'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        if (!$order->can_review) {
            return back()->with('error', 'Chỉ có thể bình luận sau khi đơn đã giao, đã thanh toán và bạn đã xác nhận nhận hàng.');
        }

        $detail = $order->details->firstWhere('id', (int) $detailId);

        if (!$detail || !$detail->product) {
            return back()->with('error', 'Sản phẩm không thuộc đơn hàng này hoặc đã không còn tồn tại.');
        }

        // Validate số sao và nội dung bình luận trước khi lưu review.
        $validated = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'comment' => ['required', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Vui lòng chọn số sao đánh giá.',
            'rating.integer' => 'Số sao đánh giá không hợp lệ.',
            'rating.min' => 'Đánh giá tối thiểu là 1 sao.',
            'rating.max' => 'Đánh giá tối đa là 5 sao.',
            'comment.required' => 'Vui lòng nhập nội dung bình luận.',
            'comment.max' => 'Bình luận tối đa 1000 ký tự.',
        ]);

        Review::updateOrCreate(
            [
                'user_id' => Auth::id(),
                'order_id' => $order->id,
                'order_detail_id' => $detail->id,
            ],
            [
                'product_id' => $detail->product_id,
                'product_variant_id' => $detail->variant_id,
                'rating' => (int) $validated['rating'],
                'comment' => trim((string) $validated['comment']),

                // Lưu snapshot để sau này sản phẩm/màu/size đổi tên thì review vẫn giữ thông tin lúc mua.
                'product_name_snapshot' => $detail->product_name,
                'color_snapshot' => optional($detail->variant?->color)->name,
                'size_snapshot' => optional($detail->variant?->size)->name,
            ]
        );

        return redirect()
            ->to(route('client.orders.show', $order->id) . '#review-section')
            ->with('success', 'Đã lưu bình luận sản phẩm thành công.');
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:1000'],
        ], [
            'cancel_reason.required' => 'Vui lòng chọn lý do hủy đơn.',
        ]);

        $reason = trim((string) $request->cancel_reason);
        $actionType = 'none';

        try {
            DB::transaction(function () use ($id, $reason, &$actionType) {
                // Lấy đơn hàng của user hiện tại và khóa dòng order để tránh 2 request hủy cùng lúc.
                $order = Order::with(['refundRequests', 'details'])
                    ->where('user_id', Auth::id())
                    ->whereKey($id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (!$order->can_cancel) {
                    throw new \RuntimeException('Đơn hàng này không thể hủy ở trạng thái hiện tại.');
                }

                // Lưu trạng thái cũ để theo dõi trước khi hủy.
                $oldStatus = $order->order_status;
                $actionType = $order->cancel_action_type;

                if ($actionType === 'direct') {
                    $order->update([
                        'previous_status' => $oldStatus,
                        'order_status' => Order::STATUS_CANCELLED,
                        'payment_status' => Order::PAYMENT_CANCELLED,
                        'cancel_reason' => $reason,
                    ]);

                    // Hoàn lại tồn kho cho các sản phẩm trong đơn nếu trước đó đơn đã trừ kho.
                    app(OrderInventoryService::class)->releaseCancelledOrder($order);

                    // Ghi log lịch sử hủy đơn.
                    OrderStatusLog::create([
                        'order_id' => $order->id,
                        'status' => Order::STATUS_CANCELLED,
                        'note' => 'Khách hàng hủy đơn: ' . $reason,
                        'changed_by' => Auth::id(),
                    ]);

                    return;
                }

                if ($actionType === 'paid_vnpay_refund') {
                    // Trường hợp đơn VNPay đã thanh toán: hủy đơn sẽ tạo yêu cầu hoàn tiền.
                    if ($order->hasPendingRefundRequest()) {
                        throw new \RuntimeException('Đơn hàng đã có yêu cầu hoàn tiền đang chờ xử lý.');
                    }

                    // Lấy số tiền còn có thể hoàn.
                    $refundAmount = (float) $order->refundable_amount;

                    if ($refundAmount <= 0) {
                        throw new \RuntimeException('Đơn hàng không còn số tiền có thể hoàn.');
                    }

                    // Cập nhật đơn sang trạng thái đã hủy và đánh dấu đã gửi yêu cầu hoàn tiền.
                    $order->update([
                        'previous_status' => $oldStatus,
                        'order_status' => Order::STATUS_CANCELLED,
                        'refund_status' => Order::REFUND_REQUESTED,
                        'last_refund_requested_at' => now(),
                        'cancel_reason' => $reason,
                    ]);

                    // Hoàn lại tồn kho cho đơn bị hủy.
                    app(OrderInventoryService::class)->releaseCancelledOrder($order);

                    // Tạo yêu cầu hoàn tiền demo để admin xử lý.
                    RefundRequest::create([
                        'order_id' => $order->id,
                        'user_id' => Auth::id(),
                        'type' => RefundRequest::TYPE_FULL,
                        'requested_amount' => $refundAmount,
                        'reason' => 'Khách hủy đơn VNPay đã thanh toán: ' . $reason,
                        'status' => RefundRequest::STATUS_PENDING,
                    ]);

                    OrderStatusLog::create([
                        'order_id' => $order->id,
                        'status' => Order::STATUS_CANCELLED,
                        'note' => 'Khách hàng hủy đơn VNPay đã thanh toán, tạo yêu cầu hoàn tiền demo: ' . $reason,
                        'changed_by' => Auth::id(),
                    ]);

                    return;
                }

                if ($actionType === 'request') {
                    // Trường hợp không được hủy trực tiếp: khách chỉ gửi yêu cầu hủy để admin duyệt.
                    $order->update([
                        'previous_status' => $oldStatus,
                        'order_status' => Order::STATUS_WAITING_FOR_CANCELLATION,
                        'cancel_reason' => $reason,
                    ]);

                    OrderStatusLog::create([
                        'order_id' => $order->id,
                        'status' => Order::STATUS_WAITING_FOR_CANCELLATION,
                        'note' => 'Khách hàng gửi yêu cầu hủy: ' . $reason,
                        'changed_by' => Auth::id(),
                    ]);

                    return;
                }

                throw new \RuntimeException('Trạng thái hủy đơn không hợp lệ.');
            });
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage() ?: 'Hủy đơn thất bại.');
        }

        if ($actionType === 'request') {
            return back()->with('success', 'Đã gửi yêu cầu hủy đơn. Admin sẽ kiểm tra và xử lý tiếp.');
        }

        // Nếu là đơn VNPay đã thanh toán thì báo đã tạo yêu cầu hoàn tiền demo.
        if ($actionType === 'paid_vnpay_refund') {
            return back()->with('success', 'Đã hủy đơn và tạo yêu cầu hoàn tiền demo. Admin sẽ duyệt hoàn tiền vào ví của bạn.');
        }

        return back()->with('success', 'Đã hủy đơn hàng thành công.');
    }
}
