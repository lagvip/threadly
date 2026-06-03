<?php

namespace Tests\Unit\Checkout;

use App\Models\Address;
use App\Services\Checkout\CheckoutPricingService;
use App\Services\GhnService;
use Tests\TestCase;

class CheckoutPricingServiceTest extends TestCase
{
    public function test_calculate_subtotal_ignores_items_without_variant(): void
    {
        $items = collect([
            (object) [
                'quantity' => 2,
                'variant' => (object) ['price' => 125000],
            ],
            (object) [
                'quantity' => 3,
                'variant' => (object) ['price' => 50000],
            ],
            (object) [
                'quantity' => 10,
                'variant' => null,
            ],
        ]);

        $this->assertSame(400000.0, $this->service()->calculateSubtotal($items));
    }

    public function test_build_full_address_skips_empty_parts(): void
    {
        $address = new Address([
            'detailed_address' => '12 Nguyen Trai',
            'ward' => 'Ward 1',
            'district' => '',
            'province' => 'Ho Chi Minh',
        ]);

        $this->assertSame(
            '12 Nguyen Trai, Ward 1, Ho Chi Minh',
            $this->service()->buildFullAddress($address)
        );
    }

    protected function service(): CheckoutPricingService
    {
        return new CheckoutPricingService($this->createMock(GhnService::class));
    }
}
