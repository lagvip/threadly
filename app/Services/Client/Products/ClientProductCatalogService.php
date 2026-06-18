<?php

namespace App\Services\Client\Products;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\CategoryRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ReviewRepositoryInterface;
use App\Models\Category;
use App\Models\Product;

class ClientProductCatalogService
{
    public function __construct(
        protected CategoryRepositoryInterface $categories,
        protected ProductRepositoryInterface $products,
        protected BrandRepositoryInterface $brands,
        protected ReviewRepositoryInterface $reviews,
    ) {}

    public function detailData(int $id): array
    {
        $product = $this->products->findAvailableWithDetail($id);

        $reviews = $this->reviews->productComments($product->id);

        $reviewCount = $reviews->count();

        return [
            'product' => $product,
            'variant' => $product->variants->first(),
            'relatedProducts' => $this->relatedProducts($product),
            'reviews' => $reviews,
            'reviewCount' => $reviewCount,
            'averageRating' => $reviewCount > 0 ? round((float) $reviews->avg('rating'), 1) : 0,
            'ratingSummary' => $this->ratingSummary($reviews, $reviewCount),
        ];
    }

    public function searchData(array $filters): array
    {
        $categoryIds = [];

        if (! empty($filters['category_id'])) {
            $category = $this->categories->findWithChildrenOrNull((int) $filters['category_id']);
            $categoryIds = $category ? $this->collectCategoryIds($category) : [];
            unset($filters['category_id']);
        }

        $priceRange = $this->products->activeVariantPriceRange($categoryIds);

        return [
            'products' => $this->products->paginateAvailableCatalog($filters, $categoryIds, true, 16)->appends($filters),
            'categories' => $this->rootCategories(),
            'brands' => $this->brands(),
            'priceRangeMin' => $priceRange['min'],
            'priceRangeMax' => $priceRange['max'],
            'keyword' => $filters['q'] ?? '',
        ];
    }

    public function categoryData(int $id, array $filters): array
    {
        $category = $this->categories->findWithChildren($id);
        $categoryIds = $this->collectCategoryIds($category);
        $priceRange = $this->products->activeVariantPriceRange($categoryIds);

        return [
            'category' => $category,
            'products' => $this->products->paginateAvailableCatalog($filters, $categoryIds, false, 16)->appends($filters),
            'categories' => $this->rootCategories(),
            'activeCategoryIds' => $this->collectActiveCategoryPathIds($category),
            'brands' => $this->brands(),
            'priceRangeMin' => $priceRange['min'],
            'priceRangeMax' => $priceRange['max'],
        ];
    }

    protected function relatedProducts(Product $product)
    {
        return $this->products->relatedAvailable($product);
    }

    protected function ratingSummary($reviews, int $reviewCount)
    {
        return collect(range(5, 1))->mapWithKeys(function ($star) use ($reviews, $reviewCount) {
            $count = $reviews->where('rating', $star)->count();

            return [
                $star => [
                    'count' => $count,
                    'percent' => $reviewCount > 0 ? round(($count / $reviewCount) * 100) : 0,
                ],
            ];
        });
    }

    protected function rootCategories()
    {
        return $this->categories->rootTreeOrdered();
    }

    protected function brands()
    {
        return $this->brands->ordered();
    }

    protected function collectCategoryIds(Category $category): array
    {
        $ids = [$category->id];

        foreach ($category->childrenRecursive ?? [] as $child) {
            $ids = array_merge($ids, $this->collectCategoryIds($child));
        }

        return array_values(array_unique($ids));
    }

    protected function collectActiveCategoryPathIds(Category $category): array
    {
        $ids = [];
        $current = $category;

        while ($current) {
            $ids[] = $current->id;

            if (empty($current->id_parent) || (int) $current->id_parent === 0) {
                break;
            }

            $current = $this->categories->find((int) $current->id_parent);
        }

        return $ids;
    }
}
