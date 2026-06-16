<?php

namespace App\Listeners\Sales;

use App\Events\Sales\RefundApproved;
use Illuminate\Support\Facades\Log;

class LogRefundApproval
{
    public function handle(RefundApproved $event): void
    {
        Log::info('Refund request approved.', [
            'refund_request_id' => $event->refundRequestId,
            'order_id' => $event->orderId,
            'user_id' => $event->userId,
            'admin_id' => $event->adminId,
            'amount' => $event->amount,
        ]);
    }
}
