<?php

namespace App\Services\Checkout;

use App\Events\OrderPlaced;
use App\Models\Order;
use Illuminate\Support\Facades\Log;

class OrderNotificationService
{
    public function sendOrderPlacedMail(Order $order): void
    {
        if (empty($order->email)) {
            return;
        }

        try {
            OrderPlaced::dispatch((int) $order->id);
        } catch (\Throwable $e) {
            Log::error('Dispatch order placed event error: ' . $e->getMessage(), [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'email' => $order->email,
            ]);
        }
    }
}
