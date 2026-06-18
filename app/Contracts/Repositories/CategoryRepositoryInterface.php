<?php

namespace App\Contracts\Repositories;

use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface CategoryRepositoryInterface
{
    public function paginatedForAdmin(?string $search = null, int $perPage = 10): LengthAwarePaginator;

    public function trashedPaginatedForAdmin(int $perPage = 10): LengthAwarePaginator;

    public function create(array $data): Category;

    public function update(Category $category, array $data): bool;

    public function delete(Category $category): bool;

    public function restore(Category $category): bool;

    public function forceDelete(Category $category): bool;

    public function find(int $id): Category;

    public function findWithParentAndChildren(int $id): Category;

    public function findWithTrashed(int $id): Category;

    public function rootTree(): Collection;

    public function rootTreeOrdered(): Collection;

    public function rootCategories(?int $exceptId = null): Collection;

    public function childCategories(): Collection;

    public function childCategoriesOrdered(): Collection;

    public function hasChildren(Category $category): bool;

    public function hasProducts(Category $category): bool;

    public function findWithChildren(int $id): Category;

    public function findWithChildrenOrNull(int $id): ?Category;
}
