<?php

namespace App\Contracts\Repositories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface ProductRepositoryInterface
{
    public function findAvailableWithDetail(int $id): Product;

    public function find(int $id): Product;

    public function findWithAdminDetail(int $id): Product;

    public function findForAdminOrNull(int $id): ?Product;

    public function findTrashed(int $id): Product;

    public function create(array $data): Product;

    public function update(Product $product, array $data): bool;

    public function delete(Product $product): bool;

    public function restore(Product $product): bool;

    public function forceDelete(Product $product): bool;

    public function adminListQuery(): Builder;

    public function trashedForAdmin();

    public function restoreMany(array $ids): int;

    public function deleteMany(array $ids): int;

    public function byCategoryIdsQuery(array $categoryIds): Builder;

    public function relatedAvailable(Product $product, int $limit = 8);

    public function availableCatalogQuery(): Builder;

    public function activeProductsQuery(): Builder;

    public function activeVariantsQuery(array $categoryIds = []): Builder;

    public function topSoldProductIds(int $limit = 12): array;

    public function activeForChat(array $keywords = [], int $limit = 6): Collection;

    public function searchForInventory(string $keyword = '', int $limit = 20): Collection;
}
