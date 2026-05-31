<?php

namespace App\Actions\Checkout;

class RemoveCheckoutVoucherAction
{
    public function execute(): void
    {
        session()->forget(config('threadly.checkout.voucher_session_key'));
    }
}
