<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VnpayCallbackService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected VnpayPaymentService $vnpay,
        protected CheckoutInventoryService $inventory,
        protected CheckoutCartService $cart,
        protected OrderNotificationService $notifications,
    ) {
    }

    public function handleReturn(Request $request): array
    {
        if (!$this->vnpay->hasValidSignature($request->all())) {
            return ['ok' => false, 'message' => 'Chữ ký VNPay không hợp lệ.'];
        }

        $order = $this->orders->findByCode((string) $request->vnp_TxnRef);

        if (!$order) {
            return ['ok' => false, 'message' => 'Không tìm thấy đơn hàng.'];
        }

        if (!$this->vnpay->isValidAmount($order, $request->vnp_Amount)) {
            Log::warning('VNPay return amount mismatch', $this->amountMismatchContext($order, $request));

            try {
                DB::transaction(fn () => $this->vnpay->updateFailureState($order, '97', $request->vnp_TransactionStatus));
            } catch (\Throwable $e) {
                Log::error('VNPay return amount mismatch process error: ' . $e->getMessage());

                return ['ok' => false, 'message' => 'Phát hiện sai lệch số tiền nhưng xử lý đơn hàng gặp lỗi.'];
            }

            return ['ok' => false, 'message' => 'Số tiền thanh toán VNPay không khớp.'];
        }

        try {
            $success = $request->vnp_ResponseCode === '00' && $request->vnp_TransactionStatus === '00';
            $shouldSendMail = false;

            DB::transaction(function () use ($order, $request, $success, &$shouldSendMail) {
                if ($success) {
                    if ($order->payment_status !== 'paid') {
                        $this->inventory->decreaseStockFromOrder($order);
                        $this->cart->clearUserCartByOrder($order);

                        $order->update(array_merge([
                            'payment_status' => 'paid',
                            'order_status' => 'pending',
                        ], $this->vnpay->paymentMeta($request)));

                        $shouldSendMail = true;
                    }

                    return;
                }

                $this->vnpay->updateFailureState($order, (string) $request->vnp_ResponseCode, $request->vnp_TransactionStatus);
            });

            if ($success) {
                session()->forget(config('threadly.checkout.buy_now_session_key'));
                session()->forget(config('threadly.checkout.cart_session_key'));

                if ($shouldSendMail) {
                    $this->notifications->sendOrderPlacedMail($order);
                }

                return ['ok' => true, 'message' => 'Thanh toán VNPay thành công.'];
            }

            return ['ok' => false, 'message' => 'Thanh toán VNPay thất bại hoặc bị hủy.'];
        } catch (\Throwable $e) {
            Log::error('VNPay return process error: ' . $e->getMessage());

            return ['ok' => false, 'message' => 'Thanh toán thành công nhưng xử lý đơn hàng gặp lỗi.'];
        }
    }

    public function handleIpn(Request $request): array
    {
        if (!$this->vnpay->hasValidSignature($request->all())) {
            return ['RspCode' => '97', 'Message' => 'Invalid signature'];
        }

        $order = $this->orders->findByCode((string) $request->vnp_TxnRef);

        if (!$order) {
            return ['RspCode' => '01', 'Message' => 'Order not found'];
        }

        if (!$this->vnpay->isValidAmount($order, $request->vnp_Amount)) {
            Log::warning('VNPay IPN amount mismatch', $this->amountMismatchContext($order, $request));

            try {
                DB::transaction(fn () => $this->vnpay->updateFailureState($order, '97', $request->vnp_TransactionStatus));
            } catch (\Throwable $e) {
                Log::error('VNPay IPN amount mismatch process error: ' . $e->getMessage());
            }

            return ['RspCode' => '04', 'Message' => 'Invalid amount'];
        }

        try {
            $shouldSendMail = false;

            DB::transaction(function () use ($order, $request, &$shouldSendMail) {
                if ($request->vnp_ResponseCode === '00' && $request->vnp_TransactionStatus === '00') {
                    if ($order->payment_status !== 'paid') {
                        $this->inventory->decreaseStockFromOrder($order);
                        $this->cart->clearUserCartByOrder($order);

                        $order->update(array_merge([
                            'payment_status' => 'paid',
                            'order_status' => 'pending',
                        ], $this->vnpay->paymentMeta($request)));

                        $shouldSendMail = true;
                    }
                } else {
                    $this->vnpay->updateFailureState($order, (string) $request->vnp_ResponseCode, $request->vnp_TransactionStatus);
                }
            });

            if ($shouldSendMail) {
                $this->notifications->sendOrderPlacedMail($order);
            }

            return ['RspCode' => '00', 'Message' => 'Confirm Success'];
        } catch (\Throwable $e) {
            Log::error('VNPay IPN error: ' . $e->getMessage());

            return ['RspCode' => '99', 'Message' => 'Unknown error'];
        }
    }

    protected function amountMismatchContext(Order $order, Request $request): array
    {
        return [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'expected_amount' => ((int) $order->total_price) * 100,
            'received_amount' => (int) ($request->vnp_Amount ?? 0),
            'response_code' => $request->vnp_ResponseCode,
            'transaction_status' => $request->vnp_TransactionStatus,
        ];
    }
}
