<?php

namespace App\Listeners\Sales;

use App\Contracts\Repositories\OrderStatusLogRepositoryInterface;
use App\Events\Sales\OrderStatusChanged;

class CreateOrderStatusLog
{
    public function __construct(
        protected OrderStatusLogRepositoryInterface $statusLogs,
    ) {}

    public function handle(OrderStatusChanged $event): void
    {
        $this->statusLogs->create([
            'order_id' => $event->orderId,
            'status' => $event->status,
            'note' => $event->note,
            'changed_by' => $event->changedBy,
        ]);
    }
}
