<?php

namespace App\Services\Admin\Orders;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\GhnOrderStatus;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\Sales\OrderStatusChanged;
use App\Models\Order;
use App\Services\Integrations\Ghn\GhnService;
use App\Services\Inventory\OrderInventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class AdminOrderGhnService
{
    public function __construct(
        protected GhnService $ghn,
        protected OrderInventoryService $inventory,
        protected OrderRepositoryInterface $orders,
    ) {}

    public function create(Order $order): string
    {
        try {
            $this->ghn->createOrder($order);

            return 'Đã tạo vận đơn GHN thành công. Mã vận đơn: '.$order->fresh()->ghn_order_code;
        } catch (\Throwable $e) {
            Log::error('Create GHN order failed: '.$e->getMessage(), $this->logContext($order));
            throw new RuntimeException($e->getMessage() ?: 'Tạo vận đơn GHN thất bại.');
        }
    }

    public function sync(Order $order, int $adminId): string
    {
        if (empty($order->ghn_order_code)) {
            throw new RuntimeException('Đơn này chưa có mã vận đơn GHN để đồng bộ.');
        }

        try {
            $response = $this->ghn->getOrderInfo($order->ghn_order_code);

            DB::transaction(function () use ($order, $response, $adminId) {
                $order = $this->orders->lockById((int) $order->id);
                $this->ghn->syncOrderFromGhnInfo($order, $response, $adminId, 'Admin đồng bộ GHN');
            });

            return 'Đã đồng bộ trạng thái GHN thành công.';
        } catch (\Throwable $e) {
            Log::error('Sync GHN order failed: '.$e->getMessage(), $this->logContext($order));
            throw new RuntimeException($e->getMessage() ?: 'Đồng bộ GHN thất bại.');
        }
    }

    public function cancel(Order $order, int $adminId): string
    {
        if (empty($order->ghn_order_code)) {
            throw new RuntimeException('Đơn này chưa có mã vận đơn GHN để hủy.');
        }

        if ($order->order_status === OrderStatus::Delivered->value) {
            throw new RuntimeException('Đơn đã giao thành công, không thể hủy vận đơn GHN.');
        }

        if ($order->payment_status === OrderPaymentStatus::Paid->value) {
            throw new RuntimeException('Đơn đã thanh toán không nên hủy vận đơn trực tiếp. Hãy xử lý hoàn tiền/hoàn hàng riêng để tránh lệch tiền và tồn kho.');
        }

        try {
            $response = $this->ghn->cancelOrder($order->ghn_order_code);
            $result = collect(data_get($response, 'data', []))->firstWhere('order_code', $order->ghn_order_code);

            if ($result && isset($result['result']) && ! $result['result']) {
                throw new RuntimeException($result['message'] ?? 'GHN không cho hủy vận đơn này.');
            }

            $oldStatus = $order->order_status;

            DB::transaction(function () use ($order, $response) {
                $this->orders->update($order, [
                    'ghn_status' => GhnOrderStatus::Cancel->value,
                    'ghn_status_name' => 'Đã hủy trên GHN',
                    'ghn_raw_response' => $response,
                    'ghn_synced_at' => now(),
                    'order_status' => OrderStatus::Cancelled->value,
                    'payment_status' => $order->payment_method === PaymentMethod::Cod->value && $order->payment_status !== OrderPaymentStatus::Paid->value
                        ? OrderPaymentStatus::Cancelled->value
                        : $order->payment_status,
                ]);

                $this->inventory->releaseCancelledOrder($order);
            });

            if ($oldStatus !== OrderStatus::Cancelled->value) {
                OrderStatusChanged::dispatch(
                    (int) $order->id,
                    OrderStatus::Cancelled->value,
                    'Admin hủy vận đơn GHN: '.$order->ghn_order_code,
                    $adminId
                );
            }

            return 'Đã gửi yêu cầu hủy vận đơn GHN thành công.';
        } catch (RuntimeException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Cancel GHN order failed: '.$e->getMessage(), $this->logContext($order));
            throw new RuntimeException($e->getMessage() ?: 'Hủy vận đơn GHN thất bại.');
        }
    }

    public function printUrl(Order $order): string
    {
        if (empty($order->ghn_order_code)) {
            throw new RuntimeException('Đơn này chưa có mã vận đơn GHN để in.');
        }

        try {
            return $this->ghn->printOrderUrl($order->ghn_order_code);
        } catch (\Throwable $e) {
            Log::error('Print GHN order failed: '.$e->getMessage(), $this->logContext($order));
            throw new RuntimeException($e->getMessage() ?: 'Không in được vận đơn GHN.');
        }
    }

    public function simulate(Order $order, string $status, int $adminId): string
    {
        if (empty($order->ghn_order_code)) {
            throw new RuntimeException('Đơn này chưa có mã vận đơn GHN, không thể giả lập trạng thái.');
        }

        $currentStatus = $order->ghn_status ?: GhnOrderStatus::ReadyToPick->value;
        $this->assertSafeSimulation($currentStatus, $status);

        try {
            $fakeGhnResponse = [
                'code' => 200,
                'message' => 'Local simulated GHN status',
                'data' => [
                    'order_code' => $order->ghn_order_code,
                    'client_order_code' => $order->ghn_client_order_code,
                    'status' => $status,
                    'leadtime' => now()->addDays(2)->toISOString(),
                ],
            ];

            $this->ghn->syncOrderFromGhnInfo($order, $fakeGhnResponse, $adminId, 'Giả lập GHN local');

            return 'Đã giả lập trạng thái GHN: '.$this->ghn->statusGroup($status).' - '.$this->ghn->statusName($status);
        } catch (\Throwable $e) {
            Log::error('Simulate GHN status failed: '.$e->getMessage(), array_merge($this->logContext($order), [
                'from_status' => $currentStatus,
                'to_status' => $status,
            ]));

            throw new RuntimeException($e->getMessage() ?: 'Giả lập trạng thái GHN thất bại.');
        }
    }

    protected function assertSafeSimulation(string $currentStatus, string $status): void
    {
        if (! in_array($status, GhnOrderStatus::safeSimulationValues(), true)) {
            throw new RuntimeException('Đã tắt giả lập trạng thái GHN gây lệch nghiệp vụ như hủy, hoàn hàng, thất lạc hoặc hư hỏng.');
        }

        $allowedTransitions = GhnOrderStatus::allowedTransitions();
        $allowedNextStatuses = $allowedTransitions[$currentStatus] ?? [
            GhnOrderStatus::Picked->value,
            GhnOrderStatus::Delivering->value,
            GhnOrderStatus::DeliveryFail->value,
            GhnOrderStatus::Delivered->value,
            GhnOrderStatus::Cancel->value,
            GhnOrderStatus::Lost->value,
            GhnOrderStatus::Damage->value,
        ];

        if (! in_array($status, $allowedNextStatuses, true)) {
            throw new RuntimeException(
                'Không thể giả lập từ trạng thái "'.
                $this->ghn->statusName($currentStatus).
                '" sang "'.
                $this->ghn->statusName($status).
                '".'
            );
        }
    }

    protected function logContext(Order $order): array
    {
        return [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'ghn_order_code' => $order->ghn_order_code,
        ];
    }
}
