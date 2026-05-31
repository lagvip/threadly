<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RefundApproved
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $refundRequestId,
        public int $orderId,
        public int $userId,
        public int $adminId,
        public float $amount,
    ) {
    }
}
