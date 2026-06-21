<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\AddressRepositoryInterface;
use App\Contracts\Repositories\CartRepositoryInterface;
use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\VoucherRepositoryInterface;
use App\DTOs\Checkout\CheckoutOrderData;
use App\DTOs\Checkout\CheckoutOrderResult;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\ProductStatus;
use App\Events\Sales\OrderPlaced;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class PlaceCheckoutOrderService
{
    public function __construct(
        protected CartRepositoryInterface $carts,
        protected AddressRepositoryInterface $addresses,
        protected OrderRepositoryInterface $orders,
        protected OrderDetailRepositoryInterface $orderDetails,
        protected VoucherRepositoryInterface $vouchers,
        protected CheckoutCartService $checkoutCart,
        protected CheckoutPricingService $checkoutPricing,
        protected CheckoutInventoryService $checkoutInventory,
        protected CheckoutVoucherService $checkoutVoucher,
        protected VnpayPaymentService $vnpay,
    ) {}

    public function execute(User $user, CheckoutOrderData $data, ?string $clientIp = null): CheckoutOrderResult
    {
        $address = $this->addresses->findForUser($data->addressId, $user->id);
        $cart = $this->carts->findForUser($user->id);
        $checkoutData = $this->checkoutCart->resolveCheckoutItems($cart);
        $checkoutSource = $checkoutData['source'];
        $cartItems = $checkoutData['items'];

        if ($checkoutSource === 'cart' && ! $cart) {
            throw new RuntimeException('Không tìm thấy giỏ hàng.');
        }

        if ($cartItems->isEmpty()) {
            throw new RuntimeException('Không có sản phẩm để thanh toán.');
        }

        $selectedCartItemIds = $checkoutSource === 'cart' && $cart
            ? $this->checkoutCart->getSelectedCheckoutItemIds($cart)
            : [];

        $subtotal = $this->validateItemsAndSubtotal($cartItems);
        $shippingFee = $this->checkoutPricing->calculateShippingFromCart($cartItems, $address);
        $fullAddress = $this->checkoutPricing->buildFullAddress($address);

        return DB::transaction(function () use (
            $user,
            $data,
            $address,
            $cart,
            $cartItems,
            $checkoutSource,
            $selectedCartItemIds,
            $subtotal,
            $shippingFee,
            $fullAddress,
            $clientIp
        ) {
            $voucherData = $this->reserveSessionVoucher($subtotal, $user->id);
            $totalPrice = max(0, $subtotal + $shippingFee - $voucherData['discount']);

            $order = $this->orders->create([
                'user_id' => $user->id,
                'name' => $data->name,
                'phone' => $data->phone,
                'email' => $user->email ?? null,
                'address' => $fullAddress,
                'customer_note' => $data->customerNote,
                'shipping_address_id' => $address->id,
                'ghn_to_province_id' => $address->ghn_province_id,
                'ghn_to_district_id' => $address->ghn_district_id,
                'ghn_to_ward_code' => $address->ghn_ward_code,
                'payment_method' => $data->paymentMethod,
                'payment_status' => $data->paymentMethod === PaymentMethod::Vnpay->value
                    ? OrderPaymentStatus::Pending->value
                    : OrderPaymentStatus::Unpaid->value,
                'order_status' => OrderStatus::Pending->value,
                'shipping_fee' => $shippingFee,
                'discount' => $voucherData['discount'],
                'voucher_id' => $voucherData['voucher_id'],
                'voucher_code' => $voucherData['voucher_code'],
                'total_price' => $totalPrice,
                'order_code' => $this->generateOrderCode(),
            ]);

            foreach ($cartItems as $item) {
                $variant = $item->variant;
                $unitPrice = (float) $variant->price;
                $quantity = (int) $item->quantity;

                $this->orderDetails->create([
                    'order_id' => $order->id,
                    'product_id' => $variant->product->id,
                    'variant_id' => $variant->id,
                    'product_name' => $variant->product->name ?? 'N/A',
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total' => $unitPrice * $quantity,
                ]);
            }

            $this->checkoutInventory->decreaseStockFromOrder($order);

            if ($checkoutSource === 'buy_now') {
                DB::afterCommit(fn () => session()->forget(config('threadly.checkout.buy_now_session_key')));
            } else {
                $this->checkoutCart->clearSelectedCartItems($cart, $selectedCartItemIds);
                DB::afterCommit(fn () => session()->forget(config('threadly.checkout.cart_session_key')));
            }

            DB::afterCommit(fn () => session()->forget(config('threadly.checkout.voucher_session_key')));

            if ($data->paymentMethod === PaymentMethod::Cod->value) {
                OrderPlaced::dispatch((int) $order->id);

                return new CheckoutOrderResult($order, $data->paymentMethod);
            }

            return new CheckoutOrderResult(
                $order,
                $data->paymentMethod,
                $this->vnpay->createPaymentUrl($order, $clientIp)
            );
        });
    }

    protected function validateItemsAndSubtotal($cartItems): float
    {
        $subtotal = 0;

        foreach ($cartItems as $item) {
            $variant = $item->variant;

            if (! $variant) {
                throw new RuntimeException('Có sản phẩm không hợp lệ.');
            }

            if (
                $variant->status !== ProductStatus::Active->value
                || ! $variant->product
                || $variant->product->status !== ProductStatus::Active->value
            ) {
                throw new RuntimeException('Có sản phẩm đã ngừng bán hoặc không còn khả dụng.');
            }

            if ($variant->quantity < $item->quantity) {
                throw new RuntimeException('Sản phẩm "'.($variant->product->name ?? 'N/A').'" không đủ tồn kho.');
            }

            $subtotal += ((float) $variant->price * (int) $item->quantity);
        }

        return (float) $subtotal;
    }

    protected function reserveSessionVoucher(float $subtotal, int $userId): array
    {
        $discount = 0;
        $voucherId = null;
        $voucherCode = null;
        $sessionVoucherId = session(config('threadly.checkout.voucher_session_key').'.voucher_id');

        if (! $sessionVoucherId) {
            return [
                'discount' => $discount,
                'voucher_id' => null,
                'voucher_code' => null,
            ];
        }

        $voucher = $this->vouchers->lockById((int) $sessionVoucherId);

        if (! $voucher) {
            throw new RuntimeException('Voucher không còn tồn tại.');
        }

        $currentUses = $this->checkoutVoucher->getUserVoucherUsage($voucher, $userId);

        if (! $voucher->isValid($subtotal, $currentUses, 1)) {
            throw new RuntimeException('Voucher không hợp lệ hoặc đã vượt giới hạn sử dụng.');
        }

        $discount = min((float) $voucher->getDiscount($subtotal), $subtotal);
        $voucherId = $voucher->id;
        $voucherCode = $voucher->code;

        $voucher->decreaseQuantity();

        return [
            'discount' => $discount,
            'voucher_id' => $voucherId,
            'voucher_code' => $voucherCode,
        ];
    }

    protected function generateOrderCode(): string
    {
        do {
            $orderCode = 'OD'.now()->format('ymdhis').Str::upper(Str::random(2));
        } while ($this->orders->orderCodeExists($orderCode));

        return $orderCode;
    }
}
