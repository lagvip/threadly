<?php

namespace App\Listeners\Inventory;

use App\Contracts\Repositories\StockMovementRepositoryInterface;
use App\Events\Inventory\StockMovementRecorded;
use Illuminate\Support\Facades\Schema;

class CreateStockMovementRecord
{
    protected ?bool $tableExists = null;

    protected ?bool $unitCostColumnExists = null;

    public function __construct(
        protected StockMovementRepositoryInterface $stockMovements,
    ) {}

    public function handle(StockMovementRecorded $event): void
    {
        if (! $this->stockMovementsTableExists()) {
            return;
        }

        $data = [
            'product_variant_id' => $event->productVariantId,
            'type' => $event->type,
            'quantity_change' => $event->quantityChange,
            'stock_before' => $event->stockBefore,
            'stock_after' => $event->stockAfter,
            'reference_type' => $event->referenceType,
            'reference_id' => $event->referenceId,
            'created_by' => $event->userId,
            'note' => $event->note,
        ];

        if ($this->stockMovementsUnitCostColumnExists()) {
            $data['unit_cost'] = $event->unitCost;
        }

        $this->stockMovements->create($data);
    }

    protected function stockMovementsTableExists(): bool
    {
        return $this->tableExists ??= Schema::hasTable('stock_movements');
    }

    protected function stockMovementsUnitCostColumnExists(): bool
    {
        return $this->unitCostColumnExists ??= Schema::hasColumn('stock_movements', 'unit_cost');
    }
}
