<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Models\Review;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ReviewRepository implements ReviewRepositoryInterface
{
    protected array $adminRelations = [
        'user',
        'product',
        'variant.color',
        'variant.size',
        'orderDetail',
        'admin',
    ];

    protected function queryWithRelations(array $relations): Builder
    {
        return Review::with($relations);
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->queryWithRelations($this->adminRelations);

        if (! empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('comment', 'like', "%{$search}%")
                    ->orWhere('admin_reply', 'like', "%{$search}%")
                    ->orWhereHas('product', fn ($productQuery) => $productQuery->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('first_name', 'like', "%{$search}%")
                            ->orWhere('last_name', 'like', "%{$search}%");
                    });
            });
        }

        if (($filters['status'] ?? null) === 'replied') {
            $query->whereNotNull('admin_reply');
        } elseif (($filters['status'] ?? null) === 'unreplied') {
            $query->whereNull('admin_reply');
        }

        if (! empty($filters['rating'])) {
            $query->where('rating', (int) $filters['rating']);
        }

        return $query->latest()->paginate($perPage);
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

    public function paginateTrashedForAdmin(int $perPage = 10): LengthAwarePaginator
    {
        return Review::onlyTrashed()
            ->with($this->adminRelations)
            ->latest('deleted_at')
            ->paginate($perPage);
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
