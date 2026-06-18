<?php

namespace App\Contracts\Repositories;

use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ReviewRepositoryInterface
{
    public function paginateForAdmin(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function productComments(int $productId): Collection;

    public function updateOrCreate(array $attributes, array $values): Review;

    public function paginateTrashedForAdmin(int $perPage = 10): LengthAwarePaginator;

    public function findTrashed(int $id): Review;

    public function update(Review $review, array $data): bool;

    public function delete(Review $review): bool;

    public function restore(Review $review): bool;

    public function forceDelete(Review $review): bool;

    public function restoreMany(array $ids): int;
}
