<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ProductVariantRepository implements ProductVariantRepositoryInterface
{
    protected function query(): Builder
    {
        return ProductVariant::query();
    }

    public function allWithRelations(): Collection
    {
        return ProductVariant::with(['product', 'color', 'size'])->get();
    }

    public function find(int $id): ProductVariant
    {
        return ProductVariant::findOrFail($id);
    }

    public function findWithProduct(int $id): ProductVariant
    {
        return ProductVariant::with('product')->findOrFail($id);
    }

    public function findWithRelations(int $id): ProductVariant
    {
        return ProductVariant::with(['product', 'color', 'size'])->findOrFail($id);
    }

    public function findWithRelationsOrNull(int $id): ?ProductVariant
    {
        return ProductVariant::with(['product', 'color', 'size'])->find($id);
    }

    public function lockById(int $id): ?ProductVariant
    {
        return ProductVariant::whereKey($id)->lockForUpdate()->first();
    }

    public function findForProduct(int $variantId, int $productId): ?ProductVariant
    {
        return ProductVariant::where('id', $variantId)
            ->where('id_product', $productId)
            ->first();
    }

    public function existsActiveCombination(int $productId, int $colorId, int $sizeId): bool
    {
        return ProductVariant::where('id_product', $productId)
            ->where('id_color', $colorId)
            ->where('id_size', $sizeId)
            ->whereNull('deleted_at')
            ->exists();
    }

    public function trashedWithRelations(): Collection
    {
        return ProductVariant::onlyTrashed()->with(['product', 'color', 'size'])->get();
    }

    public function findTrashed(int $id): ProductVariant
    {
        return ProductVariant::onlyTrashed()->findOrFail($id);
    }

    public function findOrNull(int $id): ?ProductVariant
    {
        return ProductVariant::find($id);
    }

    public function create(array $data): ProductVariant
    {
        return ProductVariant::create($data);
    }

    public function update(ProductVariant $variant, array $data): bool
    {
        return $variant->update($data);
    }

    public function delete(ProductVariant $variant): bool
    {
        return (bool) $variant->delete();
    }

    public function restore(ProductVariant $variant): bool
    {
        return (bool) $variant->restore();
    }

    public function forceDelete(ProductVariant $variant): bool
    {
        return (bool) $variant->forceDelete();
    }

    public function deleteMany(array $ids): int
    {
        return ProductVariant::whereIn('id', $ids)->delete();
    }

    public function restoreMany(array $ids): int
    {
        return ProductVariant::onlyTrashed()->whereIn('id', $ids)->restore();
    }

    public function totalStock(): int
    {
        return (int) ProductVariant::sum('quantity');
    }

    public function lowStock(int $limit = 10): Collection
    {
        return ProductVariant::query()
            ->with('product')
            ->where('quantity', '<=', 5)
            ->orderBy('quantity')
            ->limit($limit)
            ->get();
    }

    public function forProductInventoryOptions(int $productId): Collection
    {
        return ProductVariant::query()
            ->with(['color', 'size'])
            ->where('id_product', $productId)
            ->whereNull('deleted_at')
            ->orderBy('id_color')
            ->orderBy('id_size')
            ->get();
    }
}
