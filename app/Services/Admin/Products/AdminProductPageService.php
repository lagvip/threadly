<?php

namespace App\Services\Admin\Products;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ColorRepositoryInterface;
use App\Contracts\Repositories\SizeRepositoryInterface;

class AdminProductPageService
{
    public function __construct(
        protected AdminProductService $products,
        protected AdminProductVariantService $variants,
        protected BrandRepositoryInterface $brands,
        protected CategoryRepositoryInterface $categories,
        protected ColorRepositoryInterface $colors,
        protected SizeRepositoryInterface $sizes,
    ) {}

    public function indexData(array $filters): array
    {
        $products = $this->products->getAllProducts($filters)->appends($filters);

        return array_merge(
            ['products' => $products],
            $this->filterData(),
            [
                'searchTerm' => $filters['search'] ?? null,
                'brandId' => $filters['brand_id'] ?? null,
                'categoryId' => $filters['category_id'] ?? null,
            ]
        );
    }

    public function createData(): array
    {
        return $this->formData();
    }

    public function editData(int $id): array
    {
        return array_merge(
            ['product' => $this->products->getProductById($id)],
            $this->formData()
        );
    }

    public function detailData(int $id): array
    {
        return [
            'product' => $this->products->getProductById($id),
            'brands' => $this->brands->all(),
            'categories' => $this->categories->childCategories(),
        ];
    }

    public function trashData(): array
    {
        return [
            'trashedProducts' => $this->products->getTrashedProducts(),
        ];
    }

    public function variantTrashData(): array
    {
        return [
            'trashedVariants' => $this->variants->getTrashedProductVariants(),
        ];
    }

    protected function formData(): array
    {
        return [
            'brands' => $this->brands->all(),
            'categories' => $this->categories->childCategories(),
            'colors' => $this->colors->all(),
            'sizes' => $this->sizes->all(),
        ];
    }

    protected function filterData(): array
    {
        return [
            'brands' => $this->brands->ordered(),
            'categories' => $this->categories->childCategoriesOrdered(),
        ];
    }
}
