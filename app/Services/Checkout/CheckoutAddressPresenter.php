<?php

namespace App\Services\Checkout;

use App\Models\Address;

class CheckoutAddressPresenter
{
    public function toArray(Address $address): array
    {
        return [
            'id' => $address->id,
            'text' => $address->detailed_address . ', ' . $address->ward . ', ' . $address->district . ', ' . $address->province,
            'province' => $address->province,
            'district' => $address->district,
            'ward' => $address->ward,
            'detail' => $address->detailed_address,
            'ghn_province_id' => $address->ghn_province_id,
            'ghn_district_id' => $address->ghn_district_id,
            'ghn_ward_code' => $address->ghn_ward_code,
        ];
    }
}
