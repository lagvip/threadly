<?php

namespace App\Contracts\Repositories;

use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface ProductVariantRepositoryInterface
{
    public function query(): Builder;

    public function allWithRelations(): Collection;

    public function find(int $id): ProductVariant;

    public function findWithProduct(int $id): ProductVariant;

    public function findWithRelations(int $id): ProductVariant;

    public function findWithRelationsOrNull(int $id): ?ProductVariant;

    public function lockById(int $id): ?ProductVariant;

    public function findForProduct(int $variantId, int $productId): ?ProductVariant;

    public function existsActiveCombination(int $productId, int $colorId, int $sizeId): bool;

    public function trashedWithRelations(): Collection;

    public function findTrashed(int $id): ProductVariant;

    public function create(array $data): ProductVariant;

    public function update(ProductVariant $variant, array $data): bool;

    public function restoreMany(array $ids): int;

    public function totalStock(): int;

    public function lowStock(int $limit = 10): Collection;

    public function forProductInventoryOptions(int $productId): Collection;
}
