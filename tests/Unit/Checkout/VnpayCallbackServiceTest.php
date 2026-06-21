<?php

namespace Tests\Unit\Checkout;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTOs\Checkout\VnpayCallbackData;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\Sales\OrderPlaced;
use App\Models\Order;
use App\Services\Checkout\VnpayCallbackService;
use App\Services\Checkout\VnpayPaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class VnpayCallbackServiceTest extends TestCase
{
    public function test_handle_return_rejects_invalid_signature(): void
    {
        $vnpay = $this->createMock(VnpayPaymentService::class);
        $vnpay->expects($this->once())
            ->method('hasValidSignature')
            ->willReturn(false);

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->never())->method('findByCode');

        $result = $this->service($orders, $vnpay)->handleReturn($this->callbackData());

        $this->assertFalse($result['ok']);
        $this->assertSame('Chữ ký VNPay không hợp lệ.', $result['message']);
    }

    public function test_handle_return_reports_missing_order(): void
    {
        $vnpay = $this->createMock(VnpayPaymentService::class);
        $vnpay->expects($this->once())
            ->method('hasValidSignature')
            ->willReturn(true);

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())
            ->method('findByCode')
            ->with('OD001')
            ->willReturn(null);

        $result = $this->service($orders, $vnpay)->handleReturn($this->callbackData());

        $this->assertFalse($result['ok']);
        $this->assertSame('Không tìm thấy đơn hàng.', $result['message']);
    }

    public function test_handle_ipn_rejects_invalid_signature(): void
    {
        $vnpay = $this->createMock(VnpayPaymentService::class);
        $vnpay->expects($this->once())
            ->method('hasValidSignature')
            ->willReturn(false);

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->never())->method('findByCode');

        $result = $this->service($orders, $vnpay)->handleIpn($this->callbackData());

        $this->assertSame([
            'RspCode' => '97',
            'Message' => 'Invalid signature',
        ], $result);
    }

    public function test_successful_ipn_locks_order_and_processes_payment_once(): void
    {
        Event::fake([OrderPlaced::class]);
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());
        DB::shouldReceive('afterCommit')->once()->andReturnUsing(fn ($callback) => $callback());

        $order = $this->order(OrderPaymentStatus::Pending->value);
        $lockedOrder = $this->order(OrderPaymentStatus::Pending->value);

        $vnpay = $this->createMock(VnpayPaymentService::class);
        $vnpay->method('hasValidSignature')->willReturn(true);
        $vnpay->method('isValidAmount')->willReturn(true);
        $vnpay->expects($this->once())
            ->method('paymentMeta')
            ->willReturn([
                'payment_transaction_no' => '123456',
                'payment_response_code' => '00',
                'payment_transaction_status' => '00',
            ]);

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->method('findByCode')->willReturn($order);
        $orders->expects($this->once())->method('lockById')->with(10)->willReturn($lockedOrder);
        $orders->expects($this->once())->method('update');

        $result = $this->service($orders, $vnpay)->handleIpn($this->callbackData());

        $this->assertSame(['RspCode' => '00', 'Message' => 'Confirm Success'], $result);
        Event::assertDispatched(OrderPlaced::class);
    }

    public function test_successful_ipn_does_not_process_again_when_locked_order_is_already_paid(): void
    {
        Event::fake([OrderPlaced::class]);
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $order = $this->order(OrderPaymentStatus::Pending->value);
        $lockedOrder = $this->order(OrderPaymentStatus::Paid->value);

        $vnpay = $this->createMock(VnpayPaymentService::class);
        $vnpay->method('hasValidSignature')->willReturn(true);
        $vnpay->method('isValidAmount')->willReturn(true);
        $vnpay->expects($this->never())->method('paymentMeta');

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->method('findByCode')->willReturn($order);
        $orders->expects($this->once())->method('lockById')->with(10)->willReturn($lockedOrder);
        $orders->expects($this->never())->method('update');

        $result = $this->service($orders, $vnpay)->handleIpn($this->callbackData());

        $this->assertSame(['RspCode' => '00', 'Message' => 'Confirm Success'], $result);
        Event::assertNotDispatched(OrderPlaced::class);
    }

    public function test_successful_ipn_does_not_revive_expired_order(): void
    {
        DB::shouldReceive('transaction')->twice()->andReturnUsing(fn ($callback) => $callback());

        $order = $this->order(OrderPaymentStatus::Pending->value);
        $lockedOrder = $this->order(OrderPaymentStatus::Expired->value);
        $lockedOrder->order_status = OrderStatus::Cancelled->value;

        $vnpay = $this->createMock(VnpayPaymentService::class);
        $vnpay->method('hasValidSignature')->willReturn(true);
        $vnpay->method('isValidAmount')->willReturn(true);

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->method('findByCode')->willReturn($order);
        $orders->expects($this->exactly(2))->method('lockById')->with(10)->willReturn($lockedOrder);
        $orders->expects($this->once())
            ->method('update')
            ->with(
                $lockedOrder,
                $this->callback(function (array $payload): bool {
                    return $payload['payment_transaction_no'] === '123456'
                        && $payload['payment_response_code'] === '00'
                        && isset($payload['payment_reconciliation_required_at']);
                })
            );

        $result = $this->service($orders, $vnpay)->handleIpn($this->callbackData());

        $this->assertSame(['RspCode' => '02', 'Message' => 'Order already confirmed'], $result);
    }

    public function test_successful_ipn_requires_reconciliation_without_active_stock_reservation(): void
    {
        DB::shouldReceive('transaction')->twice()->andReturnUsing(fn ($callback) => $callback());

        $order = $this->order(OrderPaymentStatus::Pending->value);
        $lockedOrder = $this->order(OrderPaymentStatus::Pending->value);
        $lockedOrder->stock_deducted_at = null;

        $vnpay = $this->createMock(VnpayPaymentService::class);
        $vnpay->method('hasValidSignature')->willReturn(true);
        $vnpay->method('isValidAmount')->willReturn(true);
        $vnpay->expects($this->never())->method('paymentMeta');

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->method('findByCode')->willReturn($order);
        $orders->expects($this->exactly(2))->method('lockById')->with(10)->willReturn($lockedOrder);
        $orders->expects($this->once())
            ->method('update')
            ->with(
                $lockedOrder,
                $this->callback(fn (array $payload): bool => isset($payload['payment_reconciliation_required_at'])
                    && str_contains($payload['payment_reconciliation_note'], 'reservation tồn kho')
                )
            );

        $result = $this->service($orders, $vnpay)->handleIpn($this->callbackData());

        $this->assertSame(['RspCode' => '02', 'Message' => 'Order already confirmed'], $result);
    }

    public function test_order_requiring_reconciliation_cannot_be_paid_again(): void
    {
        $order = new Order([
            'payment_method' => PaymentMethod::Vnpay->value,
            'payment_status' => OrderPaymentStatus::Expired->value,
            'order_status' => OrderStatus::Cancelled->value,
            'payment_reconciliation_required_at' => now(),
        ]);

        $this->assertFalse($order->can_repay);
    }

    protected function service(
        OrderRepositoryInterface $orders,
        VnpayPaymentService $vnpay,
    ): VnpayCallbackService {
        return new VnpayCallbackService(
            $orders,
            $vnpay,
        );
    }

    protected function payload(): array
    {
        return [
            'vnp_TxnRef' => 'OD001',
            'vnp_Amount' => '1000000',
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TransactionNo' => '123456',
        ];
    }

    protected function callbackData(): VnpayCallbackData
    {
        return VnpayCallbackData::fromArray($this->payload());
    }

    protected function order(string $paymentStatus): Order
    {
        $order = new Order([
            'order_code' => 'OD001',
            'payment_status' => $paymentStatus,
            'order_status' => OrderStatus::Pending->value,
            'total_price' => 10000,
            'stock_deducted_at' => now(),
        ]);
        $order->id = 10;
        $order->exists = true;

        return $order;
    }
}
