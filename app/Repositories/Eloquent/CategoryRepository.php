<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Models\Category;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class CategoryRepository implements CategoryRepositoryInterface
{
    public function query(): Builder
    {
        return Category::query();
    }

    public function trashedQuery(): Builder
    {
        return Category::onlyTrashed();
    }

    public function create(array $data): Category
    {
        return Category::create($data);
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

    public function findWithChildren(int $id): Category
    {
        return Category::with('childrenRecursive')->findOrFail($id);
    }

    public function findWithChildrenOrNull(int $id): ?Category
    {
        return Category::with('childrenRecursive')->find($id);
    }
}
