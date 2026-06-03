<?php

namespace App\Contracts\Repositories;

use App\Models\Wishlist;
use Illuminate\Support\Collection;

interface WishlistRepositoryInterface
{
    public function forUserWithProducts(int $userId): Collection;

    public function firstOrCreate(int $userId, int $variantId): Wishlist;

    public function findForUser(int $userId, int $id): Wishlist;
}
