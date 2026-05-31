<?php

namespace App\Services\Client\Wishlist;

use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Contracts\Repositories\WishlistRepositoryInterface;
use RuntimeException;

class ClientWishlistService
{
    public function __construct(
        protected WishlistRepositoryInterface $wishlists,
        protected ProductVariantRepositoryInterface $variants,
    ) {
    }

    public function indexData(int $userId): array
    {
        $wishlists = $this->wishlists->forUserWithProducts($userId)
            ->filter(fn ($item) => $item->variant && $item->variant->product)
            ->values();

        return compact('wishlists');
    }

    public function add(int $userId, int $variantId): void
    {
        $variant = $this->variants->findWithProduct($variantId);

        if ($variant->status !== 'active') {
            throw new RuntimeException('Biến thể này hiện không khả dụng.');
        }

        $this->wishlists->firstOrCreate($userId, $variant->id);
    }

    public function remove(int $userId, int $id): void
    {
        $this->wishlists->findForUser($userId, $id)->delete();
    }
}
