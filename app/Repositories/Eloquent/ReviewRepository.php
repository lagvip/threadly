<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Models\Review;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function queryWithRelations(array $relations): Builder
    {
        return Review::with($relations);
    }

    public function productComments(int $productId): Collection
    {
        return Review::with(['user:id,name,avatar', 'admin:id,name,avatar'])
            ->where('product_id', $productId)
            ->whereNotNull('comment')
            ->orderByDesc('created_at')
            ->get();
    }

    public function updateOrCreate(array $attributes, array $values): Review
    {
        return Review::updateOrCreate($attributes, $values);
    }

    public function trashedWithRelations(array $relations): Builder
    {
        return Review::onlyTrashed()->with($relations);
    }

    public function findTrashed(int $id): Review
    {
        return Review::onlyTrashed()->findOrFail($id);
    }

    public function update(Review $review, array $data): bool
    {
        return $review->update($data);
    }

    public function delete(Review $review): bool
    {
        return (bool) $review->delete();
    }

    public function restore(Review $review): bool
    {
        return (bool) $review->restore();
    }

    public function forceDelete(Review $review): bool
    {
        return (bool) $review->forceDelete();
    }

    public function restoreMany(array $ids): int
    {
        return Review::onlyTrashed()->whereIn('id', $ids)->restore();
    }
}
