<?php

namespace App\Contracts\Repositories;

use App\Models\OrderStatusLog;

interface OrderStatusLogRepositoryInterface
{
    public function create(array $data): OrderStatusLog;
}
