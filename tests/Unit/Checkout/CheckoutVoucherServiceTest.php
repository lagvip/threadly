<?php

namespace Tests\Unit\Checkout;

use App\Contracts\Repositories\VoucherRepositoryInterface;
use App\Models\Order;
use App\Services\Checkout\CheckoutVoucherService;
use Tests\TestCase;

class CheckoutVoucherServiceTest extends TestCase
{
    public function test_repay_does_not_reserve_voucher_twice_when_reservation_is_active(): void
    {
        $order = new Order([
            'voucher_id' => 10,
            'voucher_released_at' => null,
        ]);

        $vouchers = $this->createMock(VoucherRepositoryInterface::class);
        $vouchers->expects($this->never())->method('lockById');

        (new CheckoutVoucherService($vouchers))->reserveVoucherForRepay($order);
    }
}
