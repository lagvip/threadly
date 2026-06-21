<?php

namespace Tests\Unit\Checkout;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Enums\ProductStatus;
use App\Events\Inventory\StockMovementRecorded;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Checkout\CheckoutInventoryService;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class CheckoutInventoryServiceTest extends TestCase
{
    public function test_decrease_stock_reserves_inventory_and_marks_order_once(): void
    {
        Event::fake([StockMovementRecorded::class]);

        $product = new Product(['name' => 'Áo', 'status' => ProductStatus::Active->value]);
        $product->id = 2;

        $variant = new ProductVariant([
            'quantity' => 3,
            'status' => ProductStatus::Active->value,
        ]);
        $variant->id = 4;
        $variant->setRelation('product', $product);

        $detail = new OrderDetail(['variant_id' => 4, 'quantity' => 2]);
        $order = new Order(['order_code' => 'OD001']);
        $order->id = 10;
        $order->setRelation('details', collect([$detail]));

        $variants = $this->createMock(ProductVariantRepositoryInterface::class);
        $variants->expects($this->once())->method('lockById')->with(4)->willReturn($variant);
        $variants->expects($this->once())->method('update')->with($variant, ['quantity' => 1]);

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())
            ->method('update')
            ->with($order, $this->callback(fn (array $data): bool => isset($data['stock_deducted_at']) && $data['stock_released_at'] === null
            ));

        (new CheckoutInventoryService($variants, $orders))->decreaseStockFromOrder($order);

        Event::assertDispatched(StockMovementRecorded::class);
    }

    public function test_decrease_stock_is_idempotent_while_reservation_is_active(): void
    {
        $order = new Order([
            'stock_deducted_at' => now(),
            'stock_released_at' => null,
        ]);

        $variants = $this->createMock(ProductVariantRepositoryInterface::class);
        $variants->expects($this->never())->method('lockById');

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->never())->method('update');

        (new CheckoutInventoryService($variants, $orders))->decreaseStockFromOrder($order);
    }
}
