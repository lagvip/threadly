<?php

namespace App\Services\Admin\Orders;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\Sales\OrderStatusChanged;
use App\Models\Order;
use App\Services\Inventory\OrderInventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AdminOrderLifecycleService
{
    public function __construct(
        protected OrderInventoryService $inventory,
        protected OrderRepositoryInterface $orders,
    ) {}

    public function updateStatus(Order $order, string $newStatus, ?string $note, int $adminId): void
    {
        $currentStatus = $order->order_status;
        $currentEnum = OrderStatus::from($currentStatus);
        $newEnum = OrderStatus::from($newStatus);

        if ($currentEnum->isTerminal()) {
            throw new RuntimeException('Đơn hàng đã ở trạng thái kết thúc, không thể cập nhật thêm.');
        }

        if (
            $currentStatus === OrderStatus::WaitingForCancellation->value ||
            $newStatus === OrderStatus::WaitingForCancellation->value
        ) {
            throw new RuntimeException('Trạng thái chờ duyệt hủy không còn được sử dụng.');
        }

        if ($order->payment_status === OrderPaymentStatus::Failed->value && $newStatus !== OrderStatus::Cancelled->value) {
            throw new RuntimeException('Đơn hàng thanh toán thất bại chỉ có thể hủy.');
        }

        if ($newStatus === OrderStatus::Cancelled->value) {
            $this->cancel($order, $note ?: 'Admin hủy đơn.', $adminId);

            return;
        }

        if (! $currentEnum->canTransitionTo($newEnum)) {
            throw new RuntimeException('Chỉ có thể cập nhật trạng thái lần lượt theo đúng quy trình.');
        }

        $payload = [
            'order_status' => $newStatus,
        ];

        if (
            $newStatus === OrderStatus::Delivered->value &&
            $order->payment_method === PaymentMethod::Cod->value &&
            in_array($order->payment_status, [
                OrderPaymentStatus::Unpaid->value,
                OrderPaymentStatus::Pending->value,
            ], true)
        ) {
            $payload['payment_status'] = OrderPaymentStatus::Paid->value;
        }

        $this->orders->update($order, $payload);

        OrderStatusChanged::dispatch((int) $order->id, $newStatus, $note, $adminId);
    }

    public function softDelete(Order $order): void
    {
        if ($order->order_status !== OrderStatus::Cancelled->value) {
            throw new RuntimeException('Chỉ có thể xóa đơn hàng đã hủy.');
        }

        $this->orders->delete($order);
    }

    public function restore(array $ids): void
    {
        $this->orders->restoreManyWithTrashed($ids);
    }

    public function forceDelete(array $ids): void
    {
        try {
            $this->orders->forceDeleteManyWithTrashed($ids);
        } catch (\Throwable $e) {
            Log::error('Xóa vĩnh viễn đơn hàng thất bại: '.$e->getMessage());
            throw new RuntimeException('Có lỗi xảy ra khi xóa vĩnh viễn.');
        }
    }

    protected function cancel(Order $order, string $note, int $adminId): void
    {
        if ($order->payment_status === OrderPaymentStatus::Paid->value) {
            throw new RuntimeException('Đơn hàng đã thanh toán không thể hủy.');
        }

        if (! in_array($order->order_status, [
            OrderStatus::Pending->value,
            OrderStatus::Processing->value,
        ], true)) {
            throw new RuntimeException('Chỉ có thể hủy khi đơn đang chờ xử lý hoặc đang xử lý.');
        }

        DB::transaction(function () use ($order) {
            $this->orders->update($order, [
                'order_status' => OrderStatus::Cancelled->value,
            ]);

            $this->inventory->releaseCancelledOrder($order);
        });

        OrderStatusChanged::dispatch((int) $order->id, OrderStatus::Cancelled->value, $note, $adminId);
    }
}
