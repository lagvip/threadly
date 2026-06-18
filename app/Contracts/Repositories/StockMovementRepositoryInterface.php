<?php

namespace App\Contracts\Repositories;

use App\Models\StockMovement;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StockMovementRepositoryInterface
{
    public function paginateForAdmin(array $filters = [], int $perPage = 20): LengthAwarePaginator;

    public function create(array $data): StockMovement;
}
