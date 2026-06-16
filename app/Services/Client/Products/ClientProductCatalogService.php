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
        $productsQuery = $this->baseProductQuery();

        $this->applyKeyword($productsQuery, $filters['q'] ?? '', true);
        $this->applyCategory($productsQuery, $filters['category_id'] ?? null);
        $this->applyBrands($productsQuery, $filters['brand'] ?? []);
        $this->applyPrice($productsQuery, $filters['price_min'] ?? null, $filters['price_max'] ?? null);
        $this->applySort($productsQuery, $filters['sort'] ?? 'newest');

        return [
            'products' => $productsQuery->paginate(16)->appends($filters),
            'categories' => $this->rootCategories(),
            'brands' => $this->brands(),
            'priceRangeMin' => (clone $this->products->activeVariantsQuery())->min('price'),
            'priceRangeMax' => (clone $this->products->activeVariantsQuery())->max('price'),
            'keyword' => $filters['q'] ?? '',
        ];
    }

    public function categoryData(int $id, array $filters): array
    {
        $category = $this->categories->findWithChildren($id);
        $categoryIds = $this->collectCategoryIds($category);
        $productsQuery = $this->baseProductQuery()
            ->whereIn('id_category', $categoryIds);

        $this->applyKeyword($productsQuery, $filters['q'] ?? '', false);
        $this->applyBrands($productsQuery, $filters['brand'] ?? []);
        $this->applyPrice($productsQuery, $filters['price_min'] ?? null, $filters['price_max'] ?? null);
        $this->applySort($productsQuery, $filters['sort'] ?? 'newest');

        $variantsQuery = $this->products->activeVariantsQuery($categoryIds);

        return [
            'category' => $category,
            'products' => $productsQuery->paginate(16)->appends($filters),
            'categories' => $this->rootCategories(),
            'activeCategoryIds' => $this->collectActiveCategoryPathIds($category),
            'brands' => $this->brands(),
            'priceRangeMin' => (clone $variantsQuery)->min('price'),
            'priceRangeMax' => (clone $variantsQuery)->max('price'),
        ];
    }

    protected function baseProductQuery()
    {
        return $this->products->availableCatalogQuery();
    }

    protected function applyKeyword($query, string $keyword, bool $includeBrandCategory): void
    {
        if ($keyword === '') {
            return;
        }

        if (! $includeBrandCategory) {
            $query->where(fn ($q) => $q->where('name', 'like', '%'.$keyword.'%')->orWhere('description', 'like', '%'.$keyword.'%'));

            return;
        }

        $keywordLike = '%'.addcslashes(mb_strtolower($keyword, 'UTF-8'), '\\%_').'%';

        $query->where(function ($q) use ($keywordLike) {
            $q->whereRaw('LOWER(products.name) COLLATE utf8mb4_bin LIKE ?', [$keywordLike])
                ->orWhereHas('brand', fn ($brandQuery) => $brandQuery->whereRaw('LOWER(brands.name) COLLATE utf8mb4_bin LIKE ?', [$keywordLike]))
                ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->whereRaw('LOWER(categories.name) COLLATE utf8mb4_bin LIKE ?', [$keywordLike]));
        });
    }

    protected function applyCategory($query, $categoryId): void
    {
        if (! $categoryId) {
            return;
        }

        $category = $this->categories->findWithChildrenOrNull((int) $categoryId);

        if ($category) {
            $query->whereIn('id_category', $this->collectCategoryIds($category));
        }
    }

    protected function applyBrands($query, array $brandIds): void
    {
        if (! empty($brandIds)) {
            $query->whereIn('id_brand', $brandIds);
        }
    }

    protected function applyPrice($query, $priceMin, $priceMax): void
    {
        if (! is_numeric($priceMin) && ! is_numeric($priceMax)) {
            return;
        }

        $min = is_numeric($priceMin) ? (float) $priceMin : 0;
        $max = is_numeric($priceMax) ? (float) $priceMax : null;

        $query->whereHas('variants', function ($q) use ($min, $max) {
            $q->where('status', 'active')->where('price', '>=', $min);

            if ($max !== null) {
                $q->where('price', '<=', $max);
            }
        });
    }

    protected function applySort($query, string $sort): void
    {
        if ($sort === 'price_asc' || $sort === 'price_desc') {
            $query->orderByRaw(
                "(select min(pv.price) from product_variants pv where pv.id_product = products.id and pv.status = 'active' and pv.deleted_at is null) "
                .($sort === 'price_asc' ? 'asc' : 'desc')
            );

            return;
        }

        $query->orderByDesc('created_at');
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
