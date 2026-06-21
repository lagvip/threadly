<?php

namespace Tests\Unit\Vouchers;

use App\Enums\VoucherStatus;
use App\Enums\VoucherType;
use App\Models\Voucher;
use Tests\TestCase;

class VoucherQuantityTest extends TestCase
{
    public function test_zero_quantity_is_valid_only_for_explicitly_unlimited_voucher(): void
    {
        $unlimited = $this->voucher(true);
        $exhausted = $this->voucher(false);

        $this->assertTrue($unlimited->isValid(100000));
        $this->assertFalse($exhausted->isValid(100000));
    }

    protected function voucher(bool $isUnlimited): Voucher
    {
        return new Voucher([
            'type' => VoucherType::Fixed->value,
            'value' => 10000,
            'quantity' => 0,
            'is_unlimited' => $isUnlimited,
            'status' => VoucherStatus::Active->value,
            'start_date' => now()->subDay(),
            'end_date' => now()->addDay(),
            'max_uses_per_user' => 1,
            'max_uses_per_order' => 1,
        ]);
    }
}
