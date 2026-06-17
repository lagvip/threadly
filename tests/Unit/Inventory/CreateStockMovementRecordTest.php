<?php

namespace Tests\Unit\Inventory;

use App\Contracts\Repositories\StockMovementRepositoryInterface;
use App\Enums\StockMovementType;
use App\Events\Inventory\StockMovementRecorded;
use App\Listeners\Inventory\CreateStockMovementRecord;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class CreateStockMovementRecordTest extends TestCase
{
    public function test_listener_creates_stock_movement_from_event(): void
    {
        Schema::shouldReceive('hasTable')
            ->once()
            ->with('stock_movements')
            ->andReturn(true);

        Schema::shouldReceive('hasColumn')
            ->once()
            ->with('stock_movements', 'unit_cost')
            ->andReturn(true);

        $movements = $this->createMock(StockMovementRepositoryInterface::class);
        $movements->expects($this->once())
            ->method('create')
            ->with([
                'product_variant_id' => 12,
                'type' => StockMovementType::Sale->value,
                'quantity_change' => -2,
                'stock_before' => 10,
                'stock_after' => 8,
                'reference_type' => 'order',
                'reference_id' => 5,
                'created_by' => 7,
                'note' => 'Sold from order.',
                'unit_cost' => 10000.0,
            ]);

        (new CreateStockMovementRecord($movements))->handle(
            new StockMovementRecorded(12, StockMovementType::Sale->value, -2, 10, 8, 'order', 5, 7, 'Sold from order.', 10000.0)
        );
    }
}
