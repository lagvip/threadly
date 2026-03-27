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
    public function index()
    {
        $orders = Order::with([
                'details.variant',
                'details.product',
                'reviews',
            ])
            ->where('user_id', Auth::id())
            ->latest('id')
            ->paginate(10);

        return view('client.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with([
                'details.variant.color',
                'details.variant.size',
                'details.product',
                'reviews',
            ])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $reviewItems = $order->details
            ->filter(function ($item) {
                return !empty($item->product_id) && !is_null($item->product);
            })
            ->unique('product_id')
            ->values()
            ->map(function ($item) use ($order) {
                $item->existing_review = $order->reviews->firstWhere('product_id', $item->product_id);
                return $item;
            });

        return view('client.orders.show', compact('order', 'reviewItems'));
    }

    public function submitReview(Request $request, $id, $productId)
    {
        $order = Order::with(['details.product'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        if (! $order->can_review) {
            return back()->with('error', 'Chỉ có thể bình luận khi đơn đã giao và đã thanh toán thành công.');
        }

        $detail = $order->details->firstWhere('product_id', (int) $productId);

        if (! $detail || ! $detail->product) {
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
                'product_id' => $detail->product_id,
            ],
            [
                'rating' => (int) $validated['rating'],
                'comment' => trim((string) $validated['comment']),
            ]
        );

        return redirect()
            ->to(route('client.orders.show', $order->id) . '#review-section')
            ->with('success', 'Đã lưu bình luận sản phẩm thành công.');
    }

    public function cancel(Request $request, $id)
    {
        $order = Order::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'cancel_reason' => ['required', 'string', 'max:1000'],
        ], [
            'cancel_reason.required' => 'Vui lòng chọn lý do hủy đơn.',
        ]);

        if (! $order->can_cancel) {
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
