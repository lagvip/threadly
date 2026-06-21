<?php

namespace App\Services\Client\Orders;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderRefundStatus;
use App\Enums\OrderStatus;
use App\Enums\RefundRequestStatus;
use App\Enums\RefundRequestType;
use App\Events\Sales\OrderStatusChanged;
use App\Models\Order;
use App\Services\Inventory\OrderInventoryService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class ClientOrderWorkflowService
{
    public function __construct(
        protected OrderInventoryService $inventory,
        protected OrderRepositoryInterface $orders,
        protected RefundRequestRepositoryInterface $refundRequests,
    ) {}

    public function confirmReceived(int $id, int $userId): void
    {
        DB::transaction(function () use ($id, $userId) {
            $order = $this->orders->lockForUser($id, $userId);

            if (! $order->can_confirm_received) {
                throw new RuntimeException('Đơn hàng chưa đủ điều kiện để xác nhận đã nhận hàng.');
            }

            $this->orders->update($order, [
                'customer_confirmed_at' => now(),
            ]);

            OrderStatusChanged::dispatch(
                (int) $order->id,
                (string) $order->order_status,
                'Khách hàng xác nhận đã nhận hàng.',
                $userId
            );
        });
    }

    public function cancel(int $id, int $userId, string $reason): string
    {
        $actionType = 'none';

        DB::transaction(function () use ($id, $userId, $reason, &$actionType) {
            $order = $this->orders->lockForUserCancellation($id, $userId);

            if (! $order->can_cancel) {
                throw new RuntimeException('Đơn hàng này không thể hủy ở trạng thái hiện tại.');
            }

            $oldStatus = $order->order_status;
            $actionType = $order->cancel_action_type;

            match ($actionType) {
                'direct' => $this->cancelDirectly($order, $oldStatus, $reason, $userId),
                'paid_vnpay_refund' => $this->cancelPaidVnpayOrder($order, $oldStatus, $reason, $userId),
                'request' => $this->requestCancellation($order, $oldStatus, $reason, $userId),
                default => throw new RuntimeException('Trạng thái hủy đơn không hợp lệ.'),
            };
        });

        return $actionType;
    }

    public function cancelSuccessMessage(string $actionType): string
    {
        return match ($actionType) {
            'request' => 'Đã gửi yêu cầu hủy đơn. Admin sẽ kiểm tra và xử lý tiếp.',
            'paid_vnpay_refund' => 'Đã hủy đơn và tạo yêu cầu hoàn tiền demo. Admin sẽ duyệt hoàn tiền vào ví của bạn.',
            default => 'Đã hủy đơn hàng thành công.',
        };
    }

    protected function cancelDirectly(Order $order, string $oldStatus, string $reason, int $userId): void
    {
        $this->orders->update($order, [
            'previous_status' => $oldStatus,
            'order_status' => OrderStatus::Cancelled->value,
            'payment_status' => OrderPaymentStatus::Cancelled->value,
            'cancel_reason' => $reason,
        ]);

        $this->inventory->releaseCancelledOrder($order);

        $this->log($order, OrderStatus::Cancelled->value, 'Khách hàng hủy đơn: '.$reason, $userId);
    }

    protected function cancelPaidVnpayOrder(Order $order, string $oldStatus, string $reason, int $userId): void
    {
        if ($order->hasPendingRefundRequest()) {
            throw new RuntimeException('Đơn hàng đã có yêu cầu hoàn tiền đang chờ xử lý.');
        }

        $refundAmount = (float) $order->refundable_amount;

        if ($refundAmount <= 0) {
            throw new RuntimeException('Đơn hàng không còn số tiền có thể hoàn.');
        }

        $this->orders->update($order, [
            'previous_status' => $oldStatus,
            'order_status' => OrderStatus::Cancelled->value,
            'refund_status' => OrderRefundStatus::Requested->value,
            'last_refund_requested_at' => now(),
            'cancel_reason' => $reason,
        ]);

        $this->inventory->releaseCancelledOrder($order);

        $this->refundRequests->create([
            'order_id' => $order->id,
            'user_id' => $userId,
            'type' => RefundRequestType::Full->value,
            'requested_amount' => $refundAmount,
            'reason' => 'Khách hủy đơn VNPay đã thanh toán: '.$reason,
            'status' => RefundRequestStatus::Pending->value,
        ]);

        $this->log($order, OrderStatus::Cancelled->value, 'Khách hàng hủy đơn VNPay đã thanh toán, tạo yêu cầu hoàn tiền demo: '.$reason, $userId);
    }

    protected function requestCancellation(Order $order, string $oldStatus, string $reason, int $userId): void
    {
        $this->orders->update($order, [
            'previous_status' => $oldStatus,
            'order_status' => OrderStatus::WaitingForCancellation->value,
            'cancel_reason' => $reason,
        ]);

        $this->log($order, OrderStatus::WaitingForCancellation->value, 'Khách hàng gửi yêu cầu hủy: '.$reason, $userId);
    }

    protected function log(Order $order, string $status, string $note, int $userId): void
    {
        OrderStatusChanged::dispatch((int) $order->id, $status, $note, $userId);
    }
}
