<?php

namespace Tests\Unit\Checkout;

use App\Models\Address;
use App\Services\Checkout\CheckoutAddressPresenter;
use Tests\TestCase;

class CheckoutAddressPresenterTest extends TestCase
{
    public function test_to_array_formats_checkout_address_payload(): void
    {
        $address = new Address([
            'province' => 'Ha Noi',
            'district' => 'Ba Dinh',
            'ward' => 'Doi Can',
            'detailed_address' => '10 Kim Ma',
            'ghn_province_id' => 201,
            'ghn_district_id' => 1482,
            'ghn_ward_code' => '1A0201',
        ]);
        $address->id = 99;

        $this->assertSame([
            'id' => 99,
            'text' => '10 Kim Ma, Doi Can, Ba Dinh, Ha Noi',
            'province' => 'Ha Noi',
            'district' => 'Ba Dinh',
            'ward' => 'Doi Can',
            'detail' => '10 Kim Ma',
            'ghn_province_id' => 201,
            'ghn_district_id' => 1482,
            'ghn_ward_code' => '1A0201',
        ], (new CheckoutAddressPresenter())->toArray($address));
    }
}
