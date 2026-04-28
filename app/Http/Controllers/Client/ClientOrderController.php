<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusLog;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ClientOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with([
                'details.variant',
                'details.product',
                'reviews.variant.color',
                'reviews.variant.size',
                'refundRequests.admin',
            ])
            ->where('user_id', Auth::id());

        if ($request->filled('order_code')) {
            $keyword = trim((string) $request->order_code);

            $query->where('order_code', 'like', '%' . $keyword . '%');
        }

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

        DB::transaction(function () use ($order) {
            $order->update([
                'customer_confirmed_at' => now(),
            ]);

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
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        if ($order->payment_method === 'vnpay' && $order->payment_status === 'paid') {
            return back()->with('error', 'Đơn hàng đã thanh toán bằng VNPay nên không thể hủy.');
        }

        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:1000'],
        ], [
            'cancel_reason.required' => 'Vui lòng chọn lý do hủy đơn.',
        ]);

        if (!$order->can_cancel) {
            return back()->with('error', 'Đơn hàng này không thể hủy ở trạng thái hiện tại.');
        }

        $reason = trim((string) $request->cancel_reason);
        $oldStatus = $order->order_status;
        $actionType = $order->cancel_action_type;

        DB::transaction(function () use ($order, $reason, $oldStatus, $actionType) {
            if ($actionType === 'direct') {
                $order->update([
                    'previous_status' => $oldStatus,
                    'order_status' => 'cancelled',
                    'payment_status' => 'cancelled',
                    'cancel_reason' => $reason,
                ]);

                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => 'cancelled',
                    'note' => 'Khách hàng hủy đơn: ' . $reason,
                    'changed_by' => Auth::id(),
                ]);

                return;
            }

            if ($actionType === 'request') {
                $order->update([
                    'previous_status' => $oldStatus,
                    'order_status' => 'waiting_for_cancellation',
                    'cancel_reason' => $reason,
                ]);

                OrderStatusLog::create([
                    'order_id' => $order->id,
                    'status' => 'waiting_for_cancellation',
                    'note' => 'Khách hàng gửi yêu cầu hủy: ' . $reason,
                    'changed_by' => Auth::id(),
                ]);

                return;
            }

            throw new \RuntimeException('Trạng thái hủy đơn không hợp lệ.');
        });

        if ($actionType === 'request') {
            return back()->with('success', 'Đã gửi yêu cầu hủy đơn. Admin sẽ kiểm tra và xử lý tiếp.');
        }

        return back()->with('success', 'Đã hủy đơn hàng thành công.');
    }
}
