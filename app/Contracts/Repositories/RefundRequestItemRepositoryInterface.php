<?php

namespace App\Contracts\Repositories;

use App\Models\RefundRequestItem;
use Illuminate\Support\Collection;

interface RefundRequestItemRepositoryInterface
{
    public function create(array $data): RefundRequestItem;

    public function update(RefundRequestItem $item, array $data): bool;

    public function approvedSummaryForOrder(int $orderId): Collection;

    public function approvedQuantitiesForOrder(int $orderId): Collection;
}
