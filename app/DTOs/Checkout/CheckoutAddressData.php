<?php

namespace App\DTOs\Checkout;

class CheckoutAddressData
{
    public function __construct(
        public readonly string $recipientName,
        public readonly string $phone,
        public readonly string $province,
        public readonly string $district,
        public readonly string $ward,
        public readonly string $detailedAddress,
        public readonly int $ghnProvinceId,
        public readonly int $ghnDistrictId,
        public readonly string $ghnWardCode,
    ) {
    }
}
