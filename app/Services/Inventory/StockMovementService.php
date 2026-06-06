<?php

namespace App\Services\Inventory;

use App\Contracts\Repositories\StockMovementRepositoryInterface;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Illuminate\Support\Facades\Schema;

class StockMovementService
{
    protected ?bool $tableExists = null;
    protected ?bool $unitCostColumnExists = null;

    public function __construct(protected StockMovementRepositoryInterface $stockMovements)
    {
    }

    public function record(
        ProductVariant $variant,
        string $type,
        int $quantityChange,
        int $stockBefore,
        int $stockAfter,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $userId = null,
        ?string $note = null,
        ?float $unitCost = null
    ): ?StockMovement {
        if (!$this->stockMovementsTableExists()) {
            return null;
        }

        $data = [
            'product_variant_id' => $variant->id,
            'type' => $type,
            'quantity_change' => $quantityChange,
            'stock_before' => $stockBefore,
            'stock_after' => $stockAfter,
            'reference_type' => $referenceType,
            'reference_id' => $referenceId,
            'created_by' => $userId,
            'note' => $note,
        ];

        if ($this->stockMovementsUnitCostColumnExists()) {
            $data['unit_cost'] = $unitCost;
        }

        return $this->stockMovements->create($data);
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
