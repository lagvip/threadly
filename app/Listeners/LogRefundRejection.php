<?php

namespace App\Listeners;

use App\Events\RefundRejected;
use Illuminate\Support\Facades\Log;

class LogRefundRejection
{
    public function handle(RefundRejected $event): void
    {
        Log::info('Refund request rejected.', [
            'refund_request_id' => $event->refundRequestId,
            'order_id' => $event->orderId,
            'user_id' => $event->userId,
            'admin_id' => $event->adminId,
            'admin_note' => $event->adminNote,
        ]);
    }
}
