<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Jobs\SendOrderPlacedMailJob;

class QueueOrderPlacedMail
{
    public function handle(OrderPlaced $event): void
    {
        SendOrderPlacedMailJob::dispatch($event->orderId);
    }
}
