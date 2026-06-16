<?php

namespace App\Providers\Modules;

use App\Contracts\Repositories\InventoryReceiptItemRepositoryInterface;
use App\Contracts\Repositories\InventoryReceiptRepositoryInterface;
use App\Contracts\Repositories\StockMovementRepositoryInterface;
use App\Repositories\Eloquent\InventoryReceiptItemRepository;
use App\Repositories\Eloquent\InventoryReceiptRepository;
use App\Repositories\Eloquent\StockMovementRepository;
use Illuminate\Support\ServiceProvider;

class InventoryRepositoryServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(InventoryReceiptItemRepositoryInterface::class, InventoryReceiptItemRepository::class);
        $this->app->bind(InventoryReceiptRepositoryInterface::class, InventoryReceiptRepository::class);
        $this->app->bind(StockMovementRepositoryInterface::class, StockMovementRepository::class);
    }
}
