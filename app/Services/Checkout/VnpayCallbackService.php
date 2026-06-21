<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTOs\Checkout\VnpayCallbackData;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Events\Sales\OrderPlaced;
use App\Exceptions\OrderNotAwaitingVnpayPaymentException;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VnpayCallbackService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected VnpayPaymentService $vnpay,
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
                $this->markFailureWithCode($order, '97', $data->transactionStatus());
            } catch (\Throwable $e) {
                Log::error('VNPay return amount mismatch process error: '.$e->getMessage());

                return ['ok' => false, 'message' => 'Phát hiện sai lệch số tiền nhưng xử lý đơn hàng gặp lỗi.'];
            }

            return ['ok' => false, 'message' => 'Số tiền thanh toán VNPay không khớp.'];
        }

        try {
            if ($data->isSuccessful()) {
                $this->markPaidOnce($order, $data);

                session()->forget(config('threadly.checkout.buy_now_session_key'));
                session()->forget(config('threadly.checkout.cart_session_key'));

                return ['ok' => true, 'message' => 'Thanh toán VNPay thành công.'];
            }

            $this->markFailedOnce($order, $data);

            return ['ok' => false, 'message' => 'Thanh toán VNPay thất bại hoặc bị hủy.'];
        } catch (OrderNotAwaitingVnpayPaymentException $e) {
            try {
                $this->recordReconciliationRequired($order, $data, $e->getMessage());
            } catch (\Throwable $recordingError) {
                Log::error('VNPay return reconciliation recording error: '.$recordingError->getMessage());

                return ['ok' => false, 'message' => 'Thanh toán đã được ghi nhận nhưng đối soát đơn hàng gặp lỗi.'];
            }

            return [
                'ok' => false,
                'message' => 'VNPay đã ghi nhận thanh toán nhưng đơn hàng không còn chờ thanh toán. Giao dịch đã được chuyển sang đối soát.',
            ];
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
                $this->markFailureWithCode($order, '97', $data->transactionStatus());
            } catch (\Throwable $e) {
                Log::error('VNPay IPN amount mismatch process error: '.$e->getMessage());
            }

            return ['RspCode' => '04', 'Message' => 'Invalid amount'];
        }

        try {
            if ($data->isSuccessful()) {
                $this->markPaidOnce($order, $data);
            } else {
                $this->markFailedOnce($order, $data);
            }

            return ['RspCode' => '00', 'Message' => 'Confirm Success'];
        } catch (OrderNotAwaitingVnpayPaymentException $e) {
            try {
                $this->recordReconciliationRequired($order, $data, $e->getMessage());
            } catch (\Throwable $recordingError) {
                Log::error('VNPay IPN reconciliation recording error: '.$recordingError->getMessage());

                return ['RspCode' => '99', 'Message' => 'Reconciliation recording failed'];
            }

            Log::warning('VNPay successful callback received for terminal order', [
                'order_id' => $order->id,
                'order_code' => $order->order_code,
                'payment_status' => $order->payment_status,
                'order_status' => $order->order_status,
            ]);

            return ['RspCode' => '02', 'Message' => 'Order already confirmed'];
        } catch (\Throwable $e) {
            Log::error('VNPay IPN error: '.$e->getMessage());

            return ['RspCode' => '99', 'Message' => 'Unknown error'];
        }
    }

    protected function markPaidOnce(Order $order, VnpayCallbackData $data): void
    {
        DB::transaction(function () use ($order, $data) {
            $lockedOrder = $this->orders->lockById((int) $order->id);

            if ($lockedOrder->payment_status === OrderPaymentStatus::Paid->value) {
                return;
            }

            if (
                $lockedOrder->payment_status !== OrderPaymentStatus::Pending->value
                || $lockedOrder->order_status !== OrderStatus::Pending->value
            ) {
                throw new OrderNotAwaitingVnpayPaymentException(
                    'Đơn hàng không còn ở trạng thái chờ thanh toán VNPay.'
                );
            }

            if (! $lockedOrder->stock_deducted_at || $lockedOrder->stock_released_at) {
                throw new OrderNotAwaitingVnpayPaymentException(
                    'Đơn hàng không có reservation tồn kho hợp lệ khi VNPay báo thanh toán thành công.'
                );
            }

            $this->orders->update($lockedOrder, array_merge([
                'payment_status' => OrderPaymentStatus::Paid->value,
                'order_status' => OrderStatus::Pending->value,
                'payment_reconciliation_required_at' => null,
                'payment_reconciliation_note' => null,
            ], $this->vnpay->paymentMeta($data)));

            DB::afterCommit(fn () => OrderPlaced::dispatch((int) $lockedOrder->id));
        });
    }

    protected function markFailedOnce(Order $order, VnpayCallbackData $data): void
    {
        $this->markFailureWithCode($order, $data->responseCode(), $data->transactionStatus());
    }

    protected function markFailureWithCode(Order $order, string $responseCode, ?string $transactionStatus): void
    {
        DB::transaction(function () use ($order, $responseCode, $transactionStatus) {
            $lockedOrder = $this->orders->lockById((int) $order->id);

            $this->vnpay->updateFailureState($lockedOrder, $responseCode, $transactionStatus);
        });
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

    protected function recordReconciliationRequired(Order $order, VnpayCallbackData $data, string $reason): void
    {
        DB::transaction(function () use ($order, $data, $reason) {
            $lockedOrder = $this->orders->lockById((int) $order->id);

            if ($lockedOrder->payment_status === OrderPaymentStatus::Paid->value) {
                return;
            }

            $this->orders->update($lockedOrder, [
                'payment_transaction_no' => $data->transactionNo(),
                'payment_bank_code' => $data->bankCode(),
                'payment_response_code' => $data->responseCode(),
                'payment_transaction_status' => $data->transactionStatus(),
                'payment_pay_date' => $data->payDate(),
                'payment_reconciliation_required_at' => now(),
                'payment_reconciliation_note' => $reason,
            ]);
        });
    }
}
