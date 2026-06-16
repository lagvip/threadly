<?php

namespace App\Events\Sales;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChanged implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $orderId,
        public string $status,
        public ?string $note = null,
        public ?int $changedBy = null,
    ) {}
}
