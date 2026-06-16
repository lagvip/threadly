<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function query(): Builder
    {
        return Category::query();
    }

    public function paginatedForAdmin(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Category::with('parent')
            ->when($search, fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
            ->latest('id')
            ->paginate($perPage);
    }

    public function trashedQuery(): Builder
    {
        return Category::onlyTrashed();
    }

    public function trashedPaginatedForAdmin(int $perPage = 10): LengthAwarePaginator
    {
        return Category::onlyTrashed()->latest()->paginate($perPage);
    }

    public function create(array $data): Category
    {
        return Category::create($data);
    }

    public function update(Category $category, array $data): bool
    {
        return $category->update($data);
    }

    public function delete(Category $category): bool
    {
        return (bool) $category->delete();
    }

    public function restore(Category $category): bool
    {
        return (bool) $category->restore();
    }

    public function forceDelete(Category $category): bool
    {
        return (bool) $category->forceDelete();
    }

    public function find(int $id): Category
    {
        return Category::findOrFail($id);
    }

    public function findWithParentAndChildren(int $id): Category
    {
        return Category::with(['parent', 'children'])->findOrFail($id);
    }

    public function findWithTrashed(int $id): Category
    {
        return Category::withTrashed()->findOrFail($id);
    }

    public function rootTree(): Collection
    {
        return Category::query()
            ->whereNull('id_parent')
            ->with('childrenRecursive')
            ->get();
    }

    public function rootTreeOrdered(): Collection
    {
        return Category::query()
            ->where(fn ($query) => $query->whereNull('id_parent')->orWhere('id_parent', 0))
            ->with('childrenRecursive')
            ->orderBy('name')
            ->get();
    }

    public function rootCategories(?int $exceptId = null): Collection
    {
        return Category::whereNull('id_parent')
            ->when($exceptId, fn ($query) => $query->where('id', '!=', $exceptId))
            ->get();
    }

    public function childCategories(): Collection
    {
        return Category::with('parent')
            ->whereNotNull('id_parent')
            ->get();
    }

    public function childCategoriesOrdered(): Collection
    {
        return Category::with('parent')
            ->whereNotNull('id_parent')
            ->orderBy('name')
            ->get();
    }

    public function hasChildren(Category $category): bool
    {
        return $category->children()->exists();
    }

    public function hasProducts(Category $category): bool
    {
        return $category->products()->exists();
    }

    public function findWithChildren(int $id): Category
    {
        return Category::with('childrenRecursive')->findOrFail($id);
    }

    public function findWithChildrenOrNull(int $id): ?Category
    {
        return Category::with('childrenRecursive')->find($id);
    }
}
