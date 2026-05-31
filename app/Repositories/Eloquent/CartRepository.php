<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Models\Cart;
use App\Models\CartDetail;
use Illuminate\Support\Collection;

class CartRepository implements CartRepositoryInterface
{
    public function firstOrCreateForUser(int $userId): Cart
    {
        return Cart::firstOrCreate([
            'id_user' => $userId,
        ]);
    }

    public function findForUser(int $userId): ?Cart
    {
        return Cart::where('id_user', $userId)->first();
    }

    public function detailsForCart(int $cartId): Collection
    {
        return CartDetail::with([
                'variant.product',
                'variant.color',
                'variant.size',
            ])
            ->where('id_cart', $cartId)
            ->get();
    }

    public function selectedDetailIds(int $cartId, array $selectedIds): array
    {
        return CartDetail::where('id_cart', $cartId)
            ->whereIn('id', $selectedIds)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();
    }

    public function selectedDetails(int $cartId, array $selectedIds): Collection
    {
        return CartDetail::with([
                'variant.product',
                'variant.color',
                'variant.size',
            ])
            ->where('id_cart', $cartId)
            ->whereIn('id', $selectedIds)
            ->get();
    }

    public function findDetailForCart(int $cartId, int $detailId): CartDetail
    {
        return CartDetail::where('id_cart', $cartId)
            ->where('id', $detailId)
            ->firstOrFail();
    }

    public function findDetailByVariant(int $cartId, int $variantId): ?CartDetail
    {
        return CartDetail::where('id_cart', $cartId)
            ->where('id_variant', $variantId)
            ->first();
    }

    public function lockDetailByVariant(int $cartId, int $variantId): ?CartDetail
    {
        return CartDetail::where('id_cart', $cartId)
            ->where('id_variant', $variantId)
            ->lockForUpdate()
            ->first();
    }

    public function detailsForUpdate(int $cartId, array $detailIds): Collection
    {
        return CartDetail::with(['variant.product'])
            ->where('id_cart', $cartId)
            ->whereIn('id', $detailIds)
            ->get();
    }

    public function createDetail(array $data): CartDetail
    {
        return CartDetail::create($data);
    }

    public function deleteDetails(int $cartId, array $detailIds): int
    {
        return CartDetail::where('id_cart', $cartId)->whereIn('id', $detailIds)->delete();
    }

    public function deleteAllDetails(int $cartId): int
    {
        return CartDetail::where('id_cart', $cartId)->delete();
    }

    public function deleteDetailsByVariants(int $cartId, array $variantIds): int
    {
        return CartDetail::where('id_cart', $cartId)->whereIn('id_variant', $variantIds)->delete();
    }

    public function validDetailIds(int $cartId, array $ids): array
    {
        return CartDetail::where('id_cart', $cartId)
            ->whereIn('id', $ids)
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->toArray();
    }
}
