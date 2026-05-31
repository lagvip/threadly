<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\OrderStatusLogRepositoryInterface;
use App\Models\OrderStatusLog;

class OrderStatusLogRepository implements OrderStatusLogRepositoryInterface
{
    public function create(array $data): OrderStatusLog
    {
        return OrderStatusLog::create($data);
    }
}
