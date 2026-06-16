<?php

namespace App\Listeners\Sales;

use App\Events\Sales\OrderPlaced;
use App\Jobs\Sales\SendOrderPlacedMailJob;

class QueueOrderPlacedMail
{
    public function handle(OrderPlaced $event): void
    {
        SendOrderPlacedMailJob::dispatch($event->orderId);
    }
}
