<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTOs\Checkout\VnpayCallbackData;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Events\Sales\OrderPlaced;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VnpayCallbackService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected VnpayPaymentService $vnpay,
        protected CheckoutInventoryService $inventory,
        protected CheckoutCartService $cart,
    ) {}

    public function handleReturn(VnpayCallbackData $data): array
    {
        if (! $this->vnpay->hasValidSignature($data->payload)) {
            return ['ok' => false, 'message' => 'Chữ ký VNPay không hợp lệ.'];
        }

        $order = $this->orders->findByCode($data->txnRef());

        if (! $order) {
            return ['ok' => false, 'message' => 'Không tìm thấy đơn hàng.'];
        }

        if (! $this->vnpay->isValidAmount($order, $data->amount())) {
            Log::warning('VNPay return amount mismatch', $this->amountMismatchContext($order, $data));

            try {
                DB::transaction(fn () => $this->vnpay->updateFailureState($order, '97', $data->transactionStatus()));
            } catch (\Throwable $e) {
                Log::error('VNPay return amount mismatch process error: '.$e->getMessage());

                return ['ok' => false, 'message' => 'Phát hiện sai lệch số tiền nhưng xử lý đơn hàng gặp lỗi.'];
            }

            return ['ok' => false, 'message' => 'Số tiền thanh toán VNPay không khớp.'];
        }

        try {
            $success = $data->isSuccessful();
            $shouldSendMail = false;

            DB::transaction(function () use ($order, $data, $success, &$shouldSendMail) {
                if ($success) {
                    if ($order->payment_status !== OrderPaymentStatus::Paid->value) {
                        $this->inventory->decreaseStockFromOrder($order);
                        $this->cart->clearUserCartByOrder($order);

                        $this->orders->update($order, array_merge([
                            'payment_status' => OrderPaymentStatus::Paid->value,
                            'order_status' => OrderStatus::Pending->value,
                        ], $this->vnpay->paymentMeta($data)));

                        $shouldSendMail = true;
                    }

                    return;
                }

                $this->vnpay->updateFailureState($order, $data->responseCode(), $data->transactionStatus());
            });

            if ($success) {
                session()->forget(config('threadly.checkout.buy_now_session_key'));
                session()->forget(config('threadly.checkout.cart_session_key'));

                if ($shouldSendMail) {
                    OrderPlaced::dispatch((int) $order->id);
                }

                return ['ok' => true, 'message' => 'Thanh toán VNPay thành công.'];
            }

            return ['ok' => false, 'message' => 'Thanh toán VNPay thất bại hoặc bị hủy.'];
        } catch (\Throwable $e) {
            Log::error('VNPay return process error: '.$e->getMessage());

            return ['ok' => false, 'message' => 'Thanh toán thành công nhưng xử lý đơn hàng gặp lỗi.'];
        }
    }

    public function handleIpn(VnpayCallbackData $data): array
    {
        if (! $this->vnpay->hasValidSignature($data->payload)) {
            return ['RspCode' => '97', 'Message' => 'Invalid signature'];
        }

        $order = $this->orders->findByCode($data->txnRef());

        if (! $order) {
            return ['RspCode' => '01', 'Message' => 'Order not found'];
        }

        if (! $this->vnpay->isValidAmount($order, $data->amount())) {
            Log::warning('VNPay IPN amount mismatch', $this->amountMismatchContext($order, $data));

            try {
                DB::transaction(fn () => $this->vnpay->updateFailureState($order, '97', $data->transactionStatus()));
            } catch (\Throwable $e) {
                Log::error('VNPay IPN amount mismatch process error: '.$e->getMessage());
            }

            return ['RspCode' => '04', 'Message' => 'Invalid amount'];
        }

        try {
            $shouldSendMail = false;

            DB::transaction(function () use ($order, $data, &$shouldSendMail) {
                if ($data->isSuccessful()) {
                    if ($order->payment_status !== OrderPaymentStatus::Paid->value) {
                        $this->inventory->decreaseStockFromOrder($order);
                        $this->cart->clearUserCartByOrder($order);

                        $this->orders->update($order, array_merge([
                            'payment_status' => OrderPaymentStatus::Paid->value,
                            'order_status' => OrderStatus::Pending->value,
                        ], $this->vnpay->paymentMeta($data)));

                        $shouldSendMail = true;
                    }
                } else {
                    $this->vnpay->updateFailureState($order, $data->responseCode(), $data->transactionStatus());
                }
            });

            if ($shouldSendMail) {
                OrderPlaced::dispatch((int) $order->id);
            }

            return ['RspCode' => '00', 'Message' => 'Confirm Success'];
        } catch (\Throwable $e) {
            Log::error('VNPay IPN error: '.$e->getMessage());

            return ['RspCode' => '99', 'Message' => 'Unknown error'];
        }
    }

    protected function amountMismatchContext(Order $order, VnpayCallbackData $data): array
    {
        return [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'expected_amount' => ((int) $order->total_price) * 100,
            'received_amount' => (int) ($data->amount() ?? 0),
            'response_code' => $data->responseCode(),
            'transaction_status' => $data->transactionStatus(),
        ];
    }
}
