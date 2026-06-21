<?php

namespace Tests\Unit\Checkout;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\Sales\OrderStatusChanged;
use App\Models\Order;
use App\Services\Checkout\ExpirePendingVnpayOrdersService;
use App\Services\Inventory\OrderInventoryService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class ExpirePendingVnpayOrdersServiceTest extends TestCase
{
    public function test_expired_pending_order_releases_reservations_and_is_cancelled_once(): void
    {
        Event::fake([OrderStatusChanged::class]);

        $order = new Order([
            'payment_method' => PaymentMethod::Vnpay->value,
            'payment_status' => OrderPaymentStatus::Pending->value,
            'order_status' => OrderStatus::Pending->value,
            'payment_expire_date' => now()->subMinute()->format('YmdHis'),
        ]);
        $order->id = 10;
        $order->exists = true;

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())
            ->method('pendingVnpayExpirationCandidateIds')
            ->willReturn([10]);
        $orders->expects($this->once())->method('lockById')->with(10)->willReturn($order);
        $orders->expects($this->once())
            ->method('update')
            ->with($order, $this->callback(fn (array $data) => $data['payment_status'] === OrderPaymentStatus::Expired->value
                && $data['order_status'] === OrderStatus::Cancelled->value))
            ->willReturn(true);

        $inventory = $this->createMock(OrderInventoryService::class);
        $inventory->expects($this->once())->method('releaseCancelledOrder')->with($order);

        $count = (new ExpirePendingVnpayOrdersService($orders, $inventory))->execute(100);

        $this->assertSame(1, $count);
        Event::assertDispatched(OrderStatusChanged::class);
    }
}
