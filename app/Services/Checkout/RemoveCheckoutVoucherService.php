<?php

namespace App\Services\Checkout;

class RemoveCheckoutVoucherService
{
    public function execute(): void
    {
        session()->forget(config('threadly.checkout.voucher_session_key'));
    }
}
