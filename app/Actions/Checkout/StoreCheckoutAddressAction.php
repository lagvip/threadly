<?php

namespace App\Actions\Checkout;

use App\DTOs\Checkout\CheckoutAddressData;
use App\Models\Address;

class StoreCheckoutAddressAction
{
    public function execute(int $userId, CheckoutAddressData $data): Address
    {
        return Address::create([
            'user_id' => $userId,
            'recipient_name' => $data->recipientName,
            'phone_number' => $data->phone,
            'province' => $data->province,
            'district' => $data->district,
            'ward' => $data->ward,
            'detailed_address' => $data->detailedAddress,
            'ghn_province_id' => $data->ghnProvinceId,
            'ghn_district_id' => $data->ghnDistrictId,
            'ghn_ward_code' => $data->ghnWardCode,
            'address_type' => 'Home',
            'is_default' => Address::where('user_id', $userId)->count() === 0 ? 1 : 0,
        ]);
    }
}
