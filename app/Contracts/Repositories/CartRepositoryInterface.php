<?php

namespace App\Contracts\Repositories;

use App\Models\Cart;
use App\Models\CartDetail;
use Illuminate\Support\Collection;

interface CartRepositoryInterface
{
    public function firstOrCreateForUser(int $userId): Cart;

    public function findForUser(int $userId): ?Cart;

    public function detailsForCart(int $cartId): Collection;

    public function selectedDetailIds(int $cartId, array $selectedIds): array;

    public function selectedDetails(int $cartId, array $selectedIds): Collection;

    public function findDetailForCart(int $cartId, int $detailId): CartDetail;

    public function findDetailByVariant(int $cartId, int $variantId): ?CartDetail;

    public function lockDetailByVariant(int $cartId, int $variantId): ?CartDetail;

    public function detailsForUpdate(int $cartId, array $detailIds): Collection;

    public function createDetail(array $data): CartDetail;

    public function updateDetail(CartDetail $detail, array $data): bool;

    public function deleteDetail(CartDetail $detail): bool;

    public function deleteDetails(int $cartId, array $detailIds): int;

    public function deleteAllDetails(int $cartId): int;

    public function validDetailIds(int $cartId, array $ids): array;
}
