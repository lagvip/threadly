<?php

namespace Tests\Unit\Checkout;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTOs\Checkout\VnpayCallbackData;
use App\Services\Checkout\CheckoutCartService;
use App\Services\Checkout\CheckoutInventoryService;
use App\Services\Checkout\VnpayCallbackService;
use App\Services\Checkout\VnpayPaymentService;
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

    protected function service(
        OrderRepositoryInterface $orders,
        VnpayPaymentService $vnpay
    ): VnpayCallbackService {
        return new VnpayCallbackService(
            $orders,
            $vnpay,
            $this->createMock(CheckoutInventoryService::class),
            $this->createMock(CheckoutCartService::class),
        );
    }

    protected function payload(): array
    {
        return [
            'vnp_TxnRef' => 'OD001',
            'vnp_Amount' => '1000000',
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
        ];
    }

    protected function callbackData(): VnpayCallbackData
    {
        return VnpayCallbackData::fromArray($this->payload());
    }
}
