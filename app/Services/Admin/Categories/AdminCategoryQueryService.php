<?php

namespace App\Services\Admin\Categories;

use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Support\Pagination;

class AdminCategoryQueryService
{
    public function __construct(
        protected CategoryRepositoryInterface $categories,
        protected ProductRepositoryInterface $products,
    ) {}

    public function indexData(?string $search = null): array
    {
        $category = $this->categories->paginatedForAdmin($search);

        $category = Pagination::withQueryString($category);

        return compact('category');
    }

    public function createData(): array
    {
        return [
            'categories' => $this->categories->rootCategories(),
        ];
    }

    public function detailData(int $id): array
    {
        $category = $this->categories->findWithParentAndChildren($id);
        $categoryIds = array_merge([$category->id], $category->children->pluck('id')->toArray());
        $products = $this->products->byCategoryIdsQuery($categoryIds)
            ->latest('id')
            ->paginate(10);

        return compact('category', 'products');
    }

    public function editData(int $id): array
    {
        $category = $this->categories->find($id);
        $allCategories = $this->categories->rootCategories($category->id);

        return compact('category', 'allCategories');
    }

    public function trashData(): array
    {
        return [
            'category' => $this->categories->trashedPaginatedForAdmin(),
        ];
    }
}
