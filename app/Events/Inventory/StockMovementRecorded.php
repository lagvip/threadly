<?php

namespace App\Events\Inventory;

use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StockMovementRecorded implements ShouldDispatchAfterCommit
{
    use Dispatchable, SerializesModels;

    public function __construct(
        public int $productVariantId,
        public string $type,
        public int $quantityChange,
        public int $stockBefore,
        public int $stockAfter,
        public ?string $referenceType = null,
        public ?int $referenceId = null,
        public ?int $userId = null,
        public ?string $note = null,
        public ?float $unitCost = null,
    ) {}
}
