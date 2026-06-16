<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class ProductRepository implements ProductRepositoryInterface
{
    public function findAvailableWithDetail(int $id): Product
    {
        return Product::with([
            'variants' => fn ($query) => $query->where('status', 'active')->with(['color', 'size']),
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

    public function adminListQuery(): Builder
    {
        return Product::with(['brand', 'category']);
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

    public function byCategoryIdsQuery(array $categoryIds): Builder
    {
        return $this->adminListQuery()->whereIn('id_category', $categoryIds);
    }

    public function relatedAvailable(Product $product, int $limit = 8)
    {
        return Product::with([
            'variants' => fn ($q) => $q->where('status', 'active')->with(['color', 'size']),
            'category',
        ])
            ->available()
            ->where('id_category', $product->id_category)
            ->where('id', '!=', $product->id)
            ->take($limit)
            ->get();
    }

    public function availableCatalogQuery(): Builder
    {
        return Product::query()
            ->available()
            ->with([
                'brand',
                'category',
                'reviews',
                'variants' => fn ($query) => $query->where('status', 'active')
                    ->with(['color', 'size'])
                    ->orderBy('price', 'asc'),
            ]);
    }

    public function activeProductsQuery(): Builder
    {
        return Product::with([
            'variants' => fn ($query) => $query->where('status', 'active')->orderBy('price', 'asc'),
        ])
            ->available()
            ->whereHas('variants', fn ($query) => $query->where('status', 'active'));
    }

    public function activeVariantsQuery(array $categoryIds = []): Builder
    {
        return ProductVariant::query()
            ->where('status', 'active')
            ->whereHas('product', function ($query) use ($categoryIds) {
                $query->available();

                if (! empty($categoryIds)) {
                    $query->whereIn('id_category', $categoryIds);
                }
            });
    }

    public function topSoldProductIds(int $limit = 12): array
    {
        return DB::table('order_details')
            ->join('products', 'products.id', '=', 'order_details.product_id')
            ->where('products.status', 'active')
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
                $q->where('status', 'active')->orderBy('price', 'asc');
            },
        ])
            ->where('status', 'active');

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
