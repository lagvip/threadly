<?php

namespace App\Contracts\Repositories;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
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

    public function paginateForAdmin(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function trashedForAdmin();

    public function restoreMany(array $ids): int;

    public function deleteMany(array $ids): int;

    public function paginateForCategoryIds(array $categoryIds, int $perPage = 10): LengthAwarePaginator;

    public function relatedAvailable(Product $product, int $limit = 8);

    public function paginateAvailableCatalog(array $filters = [], array $categoryIds = [], bool $includeBrandCategory = true, int $perPage = 16): LengthAwarePaginator;

    public function randomActiveProducts(int $limit): Collection;

    public function featuredActiveProducts(array $soldProductIds, int $limit = 10): Collection;

    public function activeVariantPriceRange(array $categoryIds = []): array;

    public function activeProductsForCategory(int $categoryId): Collection;

    public function topSoldProductIds(int $limit = 12): array;

    public function activeForChat(array $keywords = [], int $limit = 6): Collection;

    public function searchForInventory(string $keyword = '', int $limit = 20): Collection;
}
