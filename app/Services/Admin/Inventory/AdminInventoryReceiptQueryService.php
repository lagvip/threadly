<?php

namespace App\Services\Admin\Inventory;

use App\Contracts\Repositories\InventoryReceiptRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Contracts\Repositories\StockMovementRepositoryInterface;
use App\Models\InventoryReceipt;
use App\Support\Pagination;

class AdminInventoryReceiptQueryService
{
    public function __construct(
        protected InventoryReceiptRepositoryInterface $receipts,
        protected ProductRepositoryInterface $products,
        protected ProductVariantRepositoryInterface $variants,
        protected StockMovementRepositoryInterface $stockMovements,
    ) {
    }

    public function indexData(array $filters): array
    {
        $query = $this->receipts->queryForAdmin();

        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);

            $query->where(function ($q) use ($keyword) {
                $q->where('receipt_code', 'like', '%' . $keyword . '%')
                    ->orWhereHas('creator', fn ($userQuery) => $userQuery->where('name', 'like', '%' . $keyword . '%'));
            });
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return [
            'receipts' => Pagination::withQueryString($query->latest('id')->paginate(10)),
            'filters' => $filters,
        ];
    }

    public function createData(): array
    {
        return [];
    }

    public function showData(InventoryReceipt $receipt): array
    {
        return [
            'receipt' => $this->receipts->loadForShow($receipt),
        ];
    }

    public function movementsData(array $filters): array
    {
        $query = $this->stockMovements->queryForAdmin();

        if (!empty($filters['keyword'])) {
            $keyword = trim((string) $filters['keyword']);

            $query->where(function ($q) use ($keyword) {
                if (ctype_digit($keyword)) {
                    $q->orWhere('product_variant_id', (int) $keyword);
                }

                $q->orWhereHas('variant.product', fn ($productQuery) => $productQuery->where('name', 'like', '%' . $keyword . '%'))
                    ->orWhereHas('variant.color', fn ($colorQuery) => $colorQuery->where('name', 'like', '%' . $keyword . '%'))
                    ->orWhereHas('variant.size', fn ($sizeQuery) => $sizeQuery->where('name', 'like', '%' . $keyword . '%'));
            });
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        return [
            'movements' => Pagination::withQueryString($query->paginate(20)),
            'filters' => $filters,
        ];
    }

    public function productSearchData(string $keyword): array
    {
        return $this->products->searchForInventory(trim($keyword))
            ->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
            ])
            ->values()
            ->all();
    }

    public function productVariantData(int $productId): array
    {
        return $this->variants->forProductInventoryOptions($productId)
            ->map(fn ($variant) => [
                'id' => $variant->id,
                'label' => '#'.$variant->id
                    .' | Màu: '.($variant->color?->name ?? '-')
                    .' | Size: '.($variant->size?->name ?? '-')
                    .' | Tồn: '.((int) $variant->quantity),
                'stock' => (int) $variant->quantity,
            ])
            ->values()
            ->all();
    }
}
