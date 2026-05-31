<?php

namespace App\Contracts\Repositories;

use App\Models\RefundRequestItem;
use Illuminate\Support\Collection;

interface RefundRequestItemRepositoryInterface
{
    public function create(array $data): RefundRequestItem;

    public function approvedQuantitiesForOrder(int $orderId): Collection;
}
