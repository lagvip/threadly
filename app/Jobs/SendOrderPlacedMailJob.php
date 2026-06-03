<?php

namespace App\Jobs;

use App\Mail\OrderPlacedMail;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendOrderPlacedMailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public int $orderId)
    {
    }

    public function handle(): void
    {
        $order = Order::with([
                'details.variant.color',
                'details.variant.size',
            ])
            ->find($this->orderId);

        if (!$order || empty($order->email)) {
            return;
        }

        Mail::to($order->email)->send(new OrderPlacedMail($order));
    }

    public function failed(\Throwable $e): void
    {
        Log::error('Send order mail job failed: ' . $e->getMessage(), [
            'order_id' => $this->orderId,
        ]);
    }
}
