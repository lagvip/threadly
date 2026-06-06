<?php

namespace App\Services\Admin\Reviews;

use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Models\Review;
use App\Support\Pagination;

class AdminReviewService
{
    protected array $relations = [
        'user',
        'product',
        'variant.color',
        'variant.size',
        'orderDetail',
        'admin',
    ];

    public function __construct(protected ReviewRepositoryInterface $reviews)
    {
    }

    public function indexData(array $filters): array
    {
        $query = $this->reviews->queryWithRelations($this->relations);

        if (!empty($filters['search'])) {
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

        if (!empty($filters['rating'])) {
            $query->where('rating', (int) $filters['rating']);
        }

        return [
            'reviews' => Pagination::withQueryString($query->latest()->paginate(10)),
        ];
    }

    public function trashData(): array
    {
        return [
            'trashedReviews' => $this->reviews->trashedWithRelations($this->relations)
                ->latest('deleted_at')
                ->paginate(10),
        ];
    }

    public function loadForEdit(Review $review): Review
    {
        return $review->loadMissing($this->relations);
    }

    public function reply(Review $review, string $reply, int $adminId): void
    {
        $review->admin_reply = trim($reply);
        $review->admin_id = $adminId;
        $review->save();
    }

    public function softDelete(Review $review): void
    {
        $review->delete();
    }

    public function restore(int $id): void
    {
        $this->reviews->findTrashed($id)->restore();
    }

    public function bulkRestore(array $ids): void
    {
        $this->reviews->restoreMany($ids);
    }

    public function forceDelete(int $id): void
    {
        $this->reviews->findTrashed($id)->forceDelete();
    }
}
