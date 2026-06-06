<?php

namespace App\Contracts\Repositories;

use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Builder;

interface StockMovementRepositoryInterface
{
    public function queryForAdmin(): Builder;

    public function create(array $data): StockMovement;
}
