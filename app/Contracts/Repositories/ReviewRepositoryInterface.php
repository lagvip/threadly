<?php

namespace App\Contracts\Repositories;

use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface ReviewRepositoryInterface
{
    public function queryWithRelations(array $relations): Builder;

    public function productComments(int $productId): Collection;

    public function updateOrCreate(array $attributes, array $values): Review;

    public function trashedWithRelations(array $relations): Builder;

    public function findTrashed(int $id): Review;

    public function restoreMany(array $ids): int;
}
