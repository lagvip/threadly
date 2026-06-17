<?php

namespace Tests\Unit\Orders;

use App\Contracts\Repositories\OrderStatusLogRepositoryInterface;
use App\Enums\OrderStatus;
use App\Events\Sales\OrderStatusChanged;
use App\Listeners\Sales\CreateOrderStatusLog;
use Tests\TestCase;

class CreateOrderStatusLogTest extends TestCase
{
    public function test_listener_creates_order_status_log_from_event(): void
    {
        $logs = $this->createMock(OrderStatusLogRepositoryInterface::class);
        $logs->expects($this->once())
            ->method('create')
            ->with([
                'order_id' => 10,
                'status' => OrderStatus::Processing->value,
                'note' => 'Admin updated order.',
                'changed_by' => 5,
            ]);

        (new CreateOrderStatusLog($logs))->handle(
            new OrderStatusChanged(10, OrderStatus::Processing->value, 'Admin updated order.', 5)
        );
    }
}
