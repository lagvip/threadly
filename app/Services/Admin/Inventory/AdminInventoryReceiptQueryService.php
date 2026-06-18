<?php

namespace App\Services\Admin\Inventory;

use App\Contracts\Repositories\InventoryReceiptRepositoryInterface;
use App\Contracts\Repositories\ProductRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Contracts\Repositories\StockMovementRepositoryInterface;
use App\Enums\InventoryReceiptStatus;
use App\Enums\StockMovementType;
use App\Models\InventoryReceipt;
use App\Support\Pagination;

class AdminInventoryReceiptQueryService
{
    public function __construct(
        protected InventoryReceiptRepositoryInterface $receipts,
        protected ProductRepositoryInterface $products,
        protected ProductVariantRepositoryInterface $variants,
        protected StockMovementRepositoryInterface $stockMovements,
    ) {}

    public function indexData(array $filters): array
    {
        return [
            'receipts' => Pagination::withQueryString($this->receipts->paginateForAdmin($filters, 10)),
            'filters' => $filters,
            'receiptStatusLabels' => $this->receiptStatusLabels(),
            'receiptStatusBadges' => $this->receiptStatusBadges(),
        ];
    }

    public function createData(): array
    {
        return [];
    }

    public function showData(InventoryReceipt $receipt): array
    {
        $receipt = $this->receipts->loadForShow($receipt);

        return [
            'receipt' => $receipt,
            'canPostReceipt' => $receipt->status === InventoryReceiptStatus::Draft->value,
            'receiptStatusLabels' => $this->receiptStatusLabels(),
        ];
    }

    public function movementsData(array $filters): array
    {
        return [
            'movements' => Pagination::withQueryString($this->stockMovements->paginateForAdmin($filters, 20)),
            'filters' => $filters,
            'movementTypeLabels' => $this->movementTypeLabels(),
            'importMovementType' => StockMovementType::Import->value,
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

    protected function receiptStatusLabels(): array
    {
        return [
            InventoryReceiptStatus::Draft->value => InventoryReceiptStatus::Draft->label(),
            InventoryReceiptStatus::Posted->value => InventoryReceiptStatus::Posted->label(),
            InventoryReceiptStatus::Cancelled->value => InventoryReceiptStatus::Cancelled->label(),
        ];
    }

    protected function receiptStatusBadges(): array
    {
        return [
            InventoryReceiptStatus::Draft->value => InventoryReceiptStatus::Draft->badge(),
            InventoryReceiptStatus::Posted->value => InventoryReceiptStatus::Posted->badge(),
            InventoryReceiptStatus::Cancelled->value => InventoryReceiptStatus::Cancelled->badge(),
        ];
    }

    protected function movementTypeLabels(): array
    {
        return [
            StockMovementType::Import->value => StockMovementType::Import->label(),
            StockMovementType::Sale->value => StockMovementType::Sale->label(),
            StockMovementType::CancelRelease->value => StockMovementType::CancelRelease->label(),
            StockMovementType::RefundRestock->value => StockMovementType::RefundRestock->label(),
            StockMovementType::Adjustment->value => StockMovementType::Adjustment->label(),
        ];
    }
}
