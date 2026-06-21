<?php

namespace Tests\Unit\Checkout;

use App\Contracts\Repositories\AddressRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\VoucherRepositoryInterface;
use App\DTOs\Checkout\CheckoutOrderData;
use App\Enums\PaymentMethod;
use App\Enums\ProductStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Services\Checkout\CheckoutCartService;
use App\Services\Checkout\CheckoutInventoryService;
use App\Services\Checkout\CheckoutPricingService;
use App\Services\Checkout\CheckoutVoucherService;
use App\Services\Checkout\PlaceCheckoutOrderService;
use App\Services\Checkout\VnpayPaymentService;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class PlaceCheckoutOrderServiceTest extends TestCase
{
    public function test_vnpay_order_reserves_stock_before_payment_url_is_returned(): void
    {
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());
        DB::shouldReceive('afterCommit')->twice()->andReturnUsing(fn ($callback) => $callback());

        $user = new User(['email' => 'customer@example.test']);
        $user->id = 5;
        $address = new Address;
        $address->id = 7;

        $product = new Product(['name' => 'Áo', 'status' => ProductStatus::Active->value]);
        $product->id = 2;
        $variant = new ProductVariant([
            'price' => 100000,
            'quantity' => 2,
            'status' => ProductStatus::Active->value,
        ]);
        $variant->id = 4;
        $variant->setRelation('product', $product);
        $item = (object) ['id' => null, 'quantity' => 1, 'variant' => $variant];

        $order = new Order(['order_code' => 'OD001']);
        $order->id = 10;

        $addresses = $this->createMock(AddressRepositoryInterface::class);
        $addresses->method('findForUser')->with(7, 5)->willReturn($address);
        $carts = $this->createMock(CartRepositoryInterface::class);
        $carts->method('findForUser')->with(5)->willReturn(null);
        $orderDetails = $this->createMock(OrderDetailRepositoryInterface::class);
        $orderDetails->expects($this->once())->method('create');
        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())->method('create')->willReturn($order);

        $checkoutCart = $this->createMock(CheckoutCartService::class);
        $checkoutCart->method('resolveCheckoutItems')->willReturn([
            'source' => 'buy_now',
            'items' => collect([$item]),
        ]);
        $pricing = $this->createMock(CheckoutPricingService::class);
        $pricing->method('calculateShippingFromCart')->willReturn(0);
        $pricing->method('buildFullAddress')->willReturn('Hà Nội');
        $inventory = $this->createMock(CheckoutInventoryService::class);
        $inventory->expects($this->once())->method('decreaseStockFromOrder')->with($order);
        $vnpay = $this->createMock(VnpayPaymentService::class);
        $vnpay->expects($this->once())->method('createPaymentUrl')->with($order, '127.0.0.1')->willReturn('https://vnpay.test');

        $service = new PlaceCheckoutOrderService(
            $carts,
            $addresses,
            $orders,
            $orderDetails,
            $this->createMock(VoucherRepositoryInterface::class),
            $checkoutCart,
            $pricing,
            $inventory,
            $this->createMock(CheckoutVoucherService::class),
            $vnpay,
        );

        $result = $service->execute($user, new CheckoutOrderData(
            'Khách hàng',
            '0900000000',
            7,
            null,
            PaymentMethod::Vnpay->value,
        ), '127.0.0.1');

        $this->assertTrue($result->isVnpay());
        $this->assertSame('https://vnpay.test', $result->paymentUrl);
    }
}
