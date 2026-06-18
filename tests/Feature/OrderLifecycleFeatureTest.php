<?php

namespace Tests\Feature;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\Sales\OrderStatusChanged;
use App\Models\Order;
use App\Services\Client\Orders\ClientOrderWorkflowService;
use App\Services\Inventory\OrderInventoryService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderLifecycleFeatureTest extends TestCase
{
    public function test_customer_can_cancel_pending_unpaid_cod_order_directly(): void
    {
        Event::fake([OrderStatusChanged::class]);
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $order = $this->order([
            'payment_method' => PaymentMethod::Cod->value,
            'payment_status' => OrderPaymentStatus::Unpaid->value,
            'order_status' => OrderStatus::Pending->value,
        ]);

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())->method('lockForUserCancellation')->with(10, 5)->willReturn($order);
        $orders->expects($this->once())
            ->method('update')
            ->with($order, $this->callback(fn (array $data) => $data['order_status'] === OrderStatus::Cancelled->value
                && $data['payment_status'] === OrderPaymentStatus::Cancelled->value
                && $data['previous_status'] === OrderStatus::Pending->value
                && $data['cancel_reason'] === 'Đổi ý'));

        $inventory = $this->createMock(OrderInventoryService::class);
        $inventory->expects($this->once())->method('releaseCancelledOrder')->with($order);

        $result = $this->service($orders, $inventory)->cancel(10, 5, 'Đổi ý');

        $this->assertSame('direct', $result);
        Event::assertDispatched(OrderStatusChanged::class);
    }

    public function test_customer_can_confirm_received_only_for_delivered_paid_order(): void
    {
        Event::fake([OrderStatusChanged::class]);
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $order = $this->order([
            'payment_method' => PaymentMethod::Cod->value,
            'payment_status' => OrderPaymentStatus::Paid->value,
            'order_status' => OrderStatus::Delivered->value,
        ]);

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())->method('findForUser')->with(10, 5)->willReturn($order);
        $orders->expects($this->once())
            ->method('update')
            ->with($order, $this->callback(fn (array $data) => array_key_exists('customer_confirmed_at', $data)));

        $this->service($orders)->confirmReceived(10, 5);

        Event::assertDispatched(OrderStatusChanged::class);
    }

    protected function service(
        OrderRepositoryInterface $orders,
        ?OrderInventoryService $inventory = null,
        ?RefundRequestRepositoryInterface $refundRequests = null,
    ): ClientOrderWorkflowService {
        return new ClientOrderWorkflowService(
            $inventory ?? $this->createMock(OrderInventoryService::class),
            $orders,
            $refundRequests ?? $this->createMock(RefundRequestRepositoryInterface::class),
        );
    }

    protected function order(array $attributes): Order
    {
        $order = new Order(array_merge([
            'user_id' => 5,
            'order_code' => 'OD001',
            'total_price' => 10000,
        ], $attributes));
        $order->id = 10;
        $order->exists = true;

        return $order;
    }
}
