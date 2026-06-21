<?php

namespace Tests\Unit\Client;

use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Enums\ProductStatus;
use App\Models\Cart;
use App\Models\CartDetail;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Client\Cart\ClientCartService;
use RuntimeException;
use Tests\TestCase;

class ClientCartServiceTest extends TestCase
{
    public function test_add_rejects_unavailable_variant_before_creating_cart(): void
    {
        $variants = $this->createMock(ProductVariantRepositoryInterface::class);
        $variants->expects($this->once())->method('lockAvailableForCart')->with(50)->willReturn(null);

        $carts = $this->createMock(CartRepositoryInterface::class);
        $carts->expects($this->never())->method('firstOrCreateForUser');

        $this->expectException(RuntimeException::class);

        (new ClientCartService($carts, $variants))->add(5, 50, 1);
    }

    public function test_add_locks_existing_detail_and_updates_aggregate_quantity(): void
    {
        $product = new Product(['status' => ProductStatus::Active->value]);
        $product->id = 20;

        $variant = new ProductVariant([
            'status' => ProductStatus::Active->value,
            'quantity' => 10,
        ]);
        $variant->id = 50;
        $variant->setRelation('product', $product);

        $cart = new Cart(['id_user' => 5]);
        $cart->id = 30;

        $detail = new CartDetail([
            'id_cart' => 30,
            'id_variant' => 50,
            'quantity' => 2,
        ]);
        $detail->id = 40;

        $variants = $this->createMock(ProductVariantRepositoryInterface::class);
        $variants->expects($this->once())->method('lockAvailableForCart')->with(50)->willReturn($variant);

        $carts = $this->createMock(CartRepositoryInterface::class);
        $carts->expects($this->once())->method('firstOrCreateForUser')->with(5)->willReturn($cart);
        $carts->expects($this->once())->method('lockDetailByVariant')->with(30, 50)->willReturn($detail);
        $carts->expects($this->once())->method('updateDetail')->with($detail, ['quantity' => 5])->willReturn(true);
        $carts->expects($this->never())->method('createDetail');

        (new ClientCartService($carts, $variants))->add(5, 50, 3);
    }
}
