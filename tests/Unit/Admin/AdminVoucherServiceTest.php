<?php

namespace Tests\Unit\Admin;

use App\Contracts\Repositories\VoucherRepositoryInterface;
use App\Enums\VoucherType;
use App\Services\Admin\Vouchers\AdminVoucherService;
use RuntimeException;
use Tests\TestCase;

class AdminVoucherServiceTest extends TestCase
{
    public function test_create_rejects_percent_discount_above_100(): void
    {
        $service = new AdminVoucherService($this->createMock(VoucherRepositoryInterface::class));

        $this->expectException(RuntimeException::class);

        $service->create($this->voucherData([
            'type' => VoucherType::Percent->value,
            'value' => 101,
        ]));
    }

    public function test_create_rejects_end_date_before_start_date(): void
    {
        $service = new AdminVoucherService($this->createMock(VoucherRepositoryInterface::class));

        $this->expectException(RuntimeException::class);

        $service->create($this->voucherData([
            'start_date' => '2026-06-10 00:00:00',
            'end_date' => '2026-06-09 23:59:59',
        ]));
    }

    protected function voucherData(array $overrides = []): array
    {
        return array_merge([
            'code' => 'SALE10',
            'type' => VoucherType::Percent->value,
            'value' => 10,
            'max_discount' => null,
            'min_order_value' => null,
            'start_date' => '2026-06-01 00:00:00',
            'end_date' => '2026-06-30 23:59:59',
            'quantity' => 100,
            'max_uses_per_user' => 1,
            'max_uses_per_order' => 1,
        ], $overrides);
    }
}
