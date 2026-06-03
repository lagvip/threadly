<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\WishlistRepositoryInterface;
use App\Models\Wishlist;
use Illuminate\Support\Collection;

class WishlistRepository implements WishlistRepositoryInterface
{
    public function forUserWithProducts(int $userId): Collection
    {
        return Wishlist::with([
                'variant.product.category',
                'variant.product.brand',
                'variant.color',
                'variant.size',
            ])
            ->where('user_id', $userId)
            ->latest()
            ->get();
    }

    public function firstOrCreate(int $userId, int $variantId): Wishlist
    {
        return Wishlist::firstOrCreate([
            'user_id' => $userId,
            'product_variant_id' => $variantId,
        ]);
    }

    public function findForUser(int $userId, int $id): Wishlist
    {
        return Wishlist::where('user_id', $userId)->findOrFail($id);
    }
}
