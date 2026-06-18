<?php

namespace Tests\Feature;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\OrderPaymentStatus;
use App\Events\Sales\OrderPlaced;
use App\Http\Controllers\Client\CheckoutController;
use App\Models\Order;
use App\Models\User;
use App\Services\Checkout\ApplyCheckoutVoucherService;
use App\Services\Checkout\BuyNowCheckoutService;
use App\Services\Checkout\CheckoutAddressPresenter;
use App\Services\Checkout\CheckoutCartService;
use App\Services\Checkout\CheckoutInventoryService;
use App\Services\Checkout\CheckoutPageService;
use App\Services\Checkout\CheckoutShippingFeeService;
use App\Services\Checkout\GhnLocationService;
use App\Services\Checkout\PlaceCheckoutOrderService;
use App\Services\Checkout\RemoveCheckoutVoucherService;
use App\Services\Checkout\ReorderService;
use App\Services\Checkout\RepayVnpayService;
use App\Services\Checkout\SelectCheckoutItemsService;
use App\Services\Checkout\StoreCheckoutAddressService;
use App\Services\Checkout\VnpayCallbackService;
use App\Services\Checkout\VnpayPaymentService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class CheckoutRoutesFeatureTest extends TestCase
{
    public function test_checkout_index_redirects_to_cart_when_page_service_rejects_checkout(): void
    {
        $user = new User(['name' => 'Customer', 'email' => 'customer@example.test']);
        $user->id = 5;

        $checkoutPage = $this->createMock(CheckoutPageService::class);
        $checkoutPage->expects($this->once())
            ->method('dataFor')
            ->with($user)
            ->willThrowException(new RuntimeException('Giỏ hàng không có sản phẩm được chọn.'));

        $this->app->instance(CheckoutController::class, $this->checkoutController(checkoutPage: $checkoutPage));

        $this->actingAs($user)
            ->get(route('client.checkout.index'))
            ->assertRedirect(route('client.cart.index'));
    }

    public function test_vnpay_ipn_double_callback_processes_order_once(): void
    {
        Event::fake([OrderPlaced::class]);
        DB::shouldReceive('transaction')->twice()->andReturnUsing(fn ($callback) => $callback());
        DB::shouldReceive('afterCommit')->once()->andReturnUsing(fn ($callback) => $callback());

        $order = $this->order(OrderPaymentStatus::Pending->value);
        $firstLockedOrder = $this->order(OrderPaymentStatus::Pending->value);
        $secondLockedOrder = $this->order(OrderPaymentStatus::Paid->value);

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->exactly(2))->method('findByCode')->with('OD001')->willReturn($order);
        $orders->expects($this->exactly(2))->method('lockById')->with(10)->willReturnOnConsecutiveCalls($firstLockedOrder, $secondLockedOrder);
        $orders->expects($this->once())->method('update');

        $vnpay = $this->createMock(VnpayPaymentService::class);
        $vnpay->expects($this->exactly(2))->method('hasValidSignature')->willReturn(true);
        $vnpay->expects($this->exactly(2))->method('isValidAmount')->willReturn(true);
        $vnpay->expects($this->once())->method('paymentMeta')->willReturn([
            'payment_transaction_no' => '123456',
            'payment_response_code' => '00',
            'payment_transaction_status' => '00',
        ]);

        $inventory = $this->createMock(CheckoutInventoryService::class);
        $inventory->expects($this->once())->method('decreaseStockFromOrder')->with($firstLockedOrder);

        $cart = $this->createMock(CheckoutCartService::class);
        $cart->expects($this->once())->method('clearUserCartByOrder')->with($firstLockedOrder);

        $callback = new VnpayCallbackService($orders, $vnpay, $inventory, $cart);
        $this->app->instance(CheckoutController::class, $this->checkoutController(vnpayCallback: $callback));

        $this->get(route('client.checkout.vnpay-ipn', $this->vnpayPayload()))
            ->assertOk()
            ->assertJson(['RspCode' => '00', 'Message' => 'Confirm Success']);

        $this->get(route('client.checkout.vnpay-ipn', $this->vnpayPayload()))
            ->assertOk()
            ->assertJson(['RspCode' => '00', 'Message' => 'Confirm Success']);

        Event::assertDispatchedTimes(OrderPlaced::class, 1);
    }

    protected function checkoutController(
        ?CheckoutPageService $checkoutPage = null,
        ?VnpayCallbackService $vnpayCallback = null,
    ): CheckoutController {
        return new CheckoutController(
            $checkoutPage ?? $this->createMock(CheckoutPageService::class),
            $this->createMock(CheckoutShippingFeeService::class),
            $this->createMock(PlaceCheckoutOrderService::class),
            $vnpayCallback ?? $this->createMock(VnpayCallbackService::class),
            $this->createMock(ReorderService::class),
            $this->createMock(RepayVnpayService::class),
            $this->createMock(SelectCheckoutItemsService::class),
            $this->createMock(BuyNowCheckoutService::class),
            $this->createMock(GhnLocationService::class),
            $this->createMock(StoreCheckoutAddressService::class),
            $this->createMock(CheckoutAddressPresenter::class),
            $this->createMock(ApplyCheckoutVoucherService::class),
            $this->createMock(RemoveCheckoutVoucherService::class),
        );
    }

    protected function vnpayPayload(): array
    {
        return [
            'vnp_TxnRef' => 'OD001',
            'vnp_Amount' => '1000000',
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_TransactionNo' => '123456',
        ];
    }

    protected function order(string $paymentStatus): Order
    {
        $order = new Order([
            'order_code' => 'OD001',
            'payment_status' => $paymentStatus,
            'total_price' => 10000,
        ]);
        $order->id = 10;
        $order->exists = true;

        return $order;
    }
}
