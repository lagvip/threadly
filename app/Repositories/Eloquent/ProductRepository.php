<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Enums\ProductStatus;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    public function findAvailableWithDetail(int $id): Product
    {
        return Product::with([
            'variants' => fn ($query) => $query->where('status', ProductStatus::Active->value)->with(['color', 'size']),
            'category',
            'brand',
        ])
            ->available()
            ->findOrFail($id);
    }

    public function find(int $id): Product
    {
        return Product::findOrFail($id);
    }

    public function findWithAdminDetail(int $id): Product
    {
        return Product::with(['brand', 'category', 'variants.color', 'variants.size'])->findOrFail($id);
    }

    public function findForAdminOrNull(int $id): ?Product
    {
        return $this->adminListQuery()->find($id);
    }

    public function findTrashed(int $id): Product
    {
        return Product::onlyTrashed()->findOrFail($id);
    }

    public function create(array $data): Product
    {
        return Product::create($data);
    }

    public function update(Product $product, array $data): bool
    {
        return $product->update($data);
    }

    public function delete(Product $product): bool
    {
        return (bool) $product->delete();
    }

    public function restore(Product $product): bool
    {
        return (bool) $product->restore();
    }

    public function forceDelete(Product $product): bool
    {
        return (bool) $product->forceDelete();
    }

    protected function adminListQuery(): Builder
    {
        return Product::with(['brand', 'category']);
    }

    public function paginateForAdmin(array $filters = [], int $perPage = 10): LengthAwarePaginator
    {
        $query = $this->adminListQuery();

        if (! empty($filters['search'])) {
            $query->where('name', 'like', '%'.trim($filters['search']).'%');
        }

        if (! empty($filters['brand_id'])) {
            $query->where('id_brand', $filters['brand_id']);
        }

        if (! empty($filters['category_id'])) {
            $query->where('id_category', $filters['category_id']);
        }

        return $query->latest('created_at')->paginate($perPage);
    }

    public function trashedForAdmin()
    {
        return Product::onlyTrashed()->with(['brand', 'category'])->get();
    }

    public function restoreMany(array $ids): int
    {
        return Product::onlyTrashed()->whereIn('id', $ids)->restore();
    }

    public function deleteMany(array $ids): int
    {
        return Product::whereIn('id', $ids)->delete();
    }

    public function paginateForCategoryIds(array $categoryIds, int $perPage = 10): LengthAwarePaginator
    {
        return $this->adminListQuery()
            ->whereIn('id_category', $categoryIds)
            ->latest('id')
            ->paginate($perPage);
    }

    public function relatedAvailable(Product $product, int $limit = 8)
    {
        return Product::with([
            'variants' => fn ($q) => $q->where('status', ProductStatus::Active->value)->with(['color', 'size']),
            'category',
        ])
            ->available()
            ->where('id_category', $product->id_category)
            ->where('id', '!=', $product->id)
            ->take($limit)
            ->get();
    }

    protected function availableCatalogQuery(): Builder
    {
        return Product::query()
            ->available()
            ->with([
                'brand',
                'category',
                'reviews',
                'variants' => fn ($query) => $query->where('status', ProductStatus::Active->value)
                    ->with(['color', 'size'])
                    ->orderBy('price', 'asc'),
            ]);
    }

    protected function activeProductsQuery(): Builder
    {
        return Product::with([
            'variants' => fn ($query) => $query->where('status', ProductStatus::Active->value)->orderBy('price', 'asc'),
        ])
            ->available()
            ->whereHas('variants', fn ($query) => $query->where('status', ProductStatus::Active->value));
    }

    protected function activeVariantsQuery(array $categoryIds = []): Builder
    {
        return ProductVariant::query()
            ->where('status', ProductStatus::Active->value)
            ->whereHas('product', function ($query) use ($categoryIds) {
                $query->available();

                if (! empty($categoryIds)) {
                    $query->whereIn('id_category', $categoryIds);
                }
            });
    }

    public function paginateAvailableCatalog(array $filters = [], array $categoryIds = [], bool $includeBrandCategory = true, int $perPage = 16): LengthAwarePaginator
    {
        $query = $this->availableCatalogQuery();

        if (! empty($categoryIds)) {
            $query->whereIn('id_category', $categoryIds);
        }

        $this->applyCatalogKeyword($query, (string) ($filters['q'] ?? ''), $includeBrandCategory);
        if (empty($categoryIds)) {
            $this->applyCatalogCategory($query, $filters['category_id'] ?? null);
        }
        $this->applyCatalogBrands($query, $filters['brand'] ?? []);
        $this->applyCatalogPrice($query, $filters['price_min'] ?? null, $filters['price_max'] ?? null);
        $this->applyCatalogSort($query, $filters['sort'] ?? 'newest');

        return $query->paginate($perPage);
    }

    public function randomActiveProducts(int $limit): Collection
    {
        return $this->activeProductsQuery()
            ->inRandomOrder()
            ->limit($limit)
            ->get();
    }

    public function featuredActiveProducts(array $soldProductIds, int $limit = 10): Collection
    {
        $featuredProducts = collect();

        if (! empty($soldProductIds)) {
            $featuredProducts = $this->activeProductsQuery()
                ->whereIn('id', $soldProductIds)
                ->get()
                ->sortBy(fn ($product) => array_search($product->id, $soldProductIds, true))
                ->values();
        }

        if ($featuredProducts->count() >= $limit) {
            return $featuredProducts;
        }

        $excludeIds = $featuredProducts->pluck('id')->all();
        $fillProducts = $this->activeProductsQuery()
            ->when(! empty($excludeIds), fn ($query) => $query->whereNotIn('id', $excludeIds))
            ->inRandomOrder()
            ->limit($limit - $featuredProducts->count())
            ->get();

        return $featuredProducts->concat($fillProducts);
    }

    public function activeVariantPriceRange(array $categoryIds = []): array
    {
        $query = $this->activeVariantsQuery($categoryIds);

        return [
            'min' => (clone $query)->min('price'),
            'max' => (clone $query)->max('price'),
        ];
    }

    public function activeProductsForCategory(int $categoryId): Collection
    {
        return $this->adminListQuery()
            ->with(['variants.color', 'variants.size'])
            ->where('id_category', $categoryId)
            ->where('status', ProductStatus::Active->value)
            ->whereHas('variants', function ($query) {
                $query->where('price', '>', 0)
                    ->where('status', ProductStatus::Active->value);
            })
            ->get();
    }

    protected function applyCatalogKeyword(Builder $query, string $keyword, bool $includeBrandCategory): void
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

    protected function applyCatalogCategory(Builder $query, $categoryId): void
    {
        if ($categoryId) {
            $query->where('id_category', (int) $categoryId);
        }
    }

    protected function applyCatalogBrands(Builder $query, array $brandIds): void
    {
        if (! empty($brandIds)) {
            $query->whereIn('id_brand', $brandIds);
        }
    }

    protected function applyCatalogPrice(Builder $query, $priceMin, $priceMax): void
    {
        if (! is_numeric($priceMin) && ! is_numeric($priceMax)) {
            return;
        }

        $min = is_numeric($priceMin) ? (float) $priceMin : 0;
        $max = is_numeric($priceMax) ? (float) $priceMax : null;

        $query->whereHas('variants', function ($q) use ($min, $max) {
            $q->where('status', ProductStatus::Active->value)->where('price', '>=', $min);

            if ($max !== null) {
                $q->where('price', '<=', $max);
            }
        });
    }

    protected function applyCatalogSort(Builder $query, string $sort): void
    {
        if ($sort === 'price_asc' || $sort === 'price_desc') {
            $query->orderByRaw(
                '(select min(pv.price) from product_variants pv where pv.id_product = products.id and pv.status = ? and pv.deleted_at is null) '
                .($sort === 'price_asc' ? 'asc' : 'desc'),
                [ProductStatus::Active->value]
            );

            return;
        }

        $query->orderByDesc('created_at');
    }

    public function topSoldProductIds(int $limit = 12): array
    {
        return DB::table('order_details')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->where('products.status', ProductStatus::Active->value)
            ->whereNull('products.deleted_at')
            ->select('order_details.product_id', DB::raw('SUM(order_details.quantity) as total_sold'))
            ->groupBy('order_details.product_id')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->pluck('order_details.product_id')
            ->toArray();
    }

    public function activeForChat(array $keywords = [], int $limit = 6): Collection
    {
        $query = Product::with([
            'brand:id,name',
            'category:id,name',
            'variants' => function ($q) {
                $q->where('status', ProductStatus::Active->value)->orderBy('price', 'asc');
            },
        ])
            ->where('status', ProductStatus::Active->value);

        if (! empty($keywords)) {
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $keyword) {
                    $q->orWhere('name', 'like', "%{$keyword}%")
                        ->orWhere('description', 'like', "%{$keyword}%");
                }
            });
        }

        return $query->latest('id')->take($limit)->get();
    }

    public function searchForInventory(string $keyword = '', int $limit = 20): Collection
    {
        return Product::query()
            ->select('id', 'name')
            ->when($keyword !== '', fn ($query) => $query->where('name', 'like', '%'.$keyword.'%'))
            ->latest('id')
            ->limit($limit)
            ->get();
    }
}
