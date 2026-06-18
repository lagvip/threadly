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

    public function __construct(protected ReviewRepositoryInterface $reviews) {}

    public function indexData(array $filters): array
    {
        return [
            'reviews' => Pagination::withQueryString($this->reviews->paginateForAdmin($filters, 10)),
        ];
    }

    public function trashData(): array
    {
        return [
            'trashedReviews' => $this->reviews->paginateTrashedForAdmin(10),
        ];
    }

    public function loadForEdit(Review $review): Review
    {
        return $review->loadMissing($this->relations);
    }

    public function reply(Review $review, string $reply, int $adminId): void
    {
        $this->reviews->update($review, [
            'admin_reply' => trim($reply),
            'admin_id' => $adminId,
        ]);
    }

    public function softDelete(Review $review): void
    {
        $this->reviews->delete($review);
    }

    public function restore(int $id): void
    {
        $this->reviews->restore($this->reviews->findTrashed($id));
    }

    public function bulkRestore(array $ids): void
    {
        $this->reviews->restoreMany($ids);
    }

    public function forceDelete(int $id): void
    {
        $this->reviews->forceDelete($this->reviews->findTrashed($id));
    }
}
