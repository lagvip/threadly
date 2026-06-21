<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTOs\Checkout\VnpayCallbackData;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\Inventory\OrderInventoryService;

class VnpayPaymentService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected OrderInventoryService $inventory,
    ) {}

    public function hasValidSignature(array $inputData): bool
    {
        $vnpHashSecret = config('services.vnpay.hash_secret');
        $vnpSecureHash = $inputData['vnp_SecureHash'] ?? null;

        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);
        ksort($inputData);

        $hashData = [];
        foreach ($inputData as $key => $value) {
            $hashData[] = urlencode($key).'='.urlencode($value);
        }

        $secureHash = hash_hmac('sha512', implode('&', $hashData), $vnpHashSecret);

        return $secureHash === $vnpSecureHash;
    }

    public function isValidAmount(Order $order, $vnpAmount): bool
    {
        return (int) ($vnpAmount ?? 0) === ((int) $order->total_price) * 100;
    }

    public function createPaymentUrl(Order $order, ?string $clientIp = null): string
    {
        $vnpUrl = trim((string) config('services.vnpay.url'));
        $vnpReturnUrl = trim((string) config('services.vnpay.return_url'));
        $vnpTmnCode = trim((string) config('services.vnpay.tmn_code'));
        $vnpHashSecret = trim((string) config('services.vnpay.hash_secret'));

        $vnpCreateDate = now()->format('YmdHis');
        $vnpExpireDate = now()->addMinutes(15)->format('YmdHis');

        $this->saveRequestMeta($order, $vnpCreateDate, $vnpExpireDate);

        $inputData = [
            'vnp_Version' => '2.1.0',
            'vnp_TmnCode' => $vnpTmnCode,
            'vnp_Amount' => (int) round((float) $order->total_price * 100),
            'vnp_Command' => 'pay',
            'vnp_CreateDate' => $vnpCreateDate,
            'vnp_CurrCode' => 'VND',
            'vnp_IpAddr' => $clientIp ?: '127.0.0.1',
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => 'Thanh toán đơn hàng '.$order->order_code,
            'vnp_OrderType' => 'billpayment',
            'vnp_ReturnUrl' => $vnpReturnUrl,
            'vnp_TxnRef' => $order->order_code,
            'vnp_ExpireDate' => $vnpExpireDate,
        ];

        ksort($inputData);

        $query = '';
        $hashData = '';

        foreach ($inputData as $key => $value) {
            $encoded = urlencode($key).'='.urlencode($value);
            $hashData .= $hashData === '' ? $encoded : '&'.$encoded;
            $query .= $encoded.'&';
        }

        $vnpSecureHash = hash_hmac('sha512', $hashData, $vnpHashSecret);

        return $vnpUrl.'?'.$query.'vnp_SecureHash='.$vnpSecureHash;
    }

    public function updateFailureState(Order $order, string $responseCode, ?string $transactionStatus = null): void
    {
        if (
            $order->payment_status === OrderPaymentStatus::Paid->value ||
            $order->payment_status !== OrderPaymentStatus::Pending->value
        ) {
            return;
        }

        $this->inventory->releaseCancelledOrder($order);

        if ($responseCode === '97') {
            $this->orders->update($order, [
                'order_status' => OrderStatus::Pending->value,
                'payment_status' => OrderPaymentStatus::Failed->value,
                'cancel_reason' => 'Sai lệch số tiền VNPay trả về',
                'payment_response_code' => $responseCode,
                'payment_transaction_status' => $transactionStatus,
            ]);

            return;
        }

        if ($responseCode === '24') {
            $this->orders->update($order, [
                'order_status' => OrderStatus::Pending->value,
                'payment_status' => OrderPaymentStatus::Cancelled->value,
                'cancel_reason' => 'Khách hủy phiên thanh toán VNPay',
                'payment_response_code' => $responseCode,
                'payment_transaction_status' => $transactionStatus,
            ]);

            return;
        }

        if ($responseCode === '11') {
            $this->orders->update($order, [
                'previous_status' => $order->order_status,
                'order_status' => OrderStatus::Cancelled->value,
                'payment_status' => OrderPaymentStatus::Expired->value,
                'cancel_reason' => 'Quá hạn thanh toán VNPay',
                'payment_response_code' => $responseCode,
                'payment_transaction_status' => $transactionStatus,
            ]);

            return;
        }

        $this->orders->update($order, [
            'order_status' => OrderStatus::Pending->value,
            'payment_status' => OrderPaymentStatus::Failed->value,
            'cancel_reason' => 'Thanh toán VNPay thất bại',
            'payment_response_code' => $responseCode,
            'payment_transaction_status' => $transactionStatus,
        ]);
    }

    public function saveRequestMeta(Order $order, string $createDate, string $expireDate): void
    {
        $this->orders->update($order, [
            'payment_request_date' => $createDate,
            'payment_expire_date' => $expireDate,
        ]);
    }

    public function paymentMeta(VnpayCallbackData $data): array
    {
        return [
            'payment_transaction_no' => $data->transactionNo(),
            'payment_bank_code' => $data->bankCode(),
            'payment_response_code' => $data->responseCode(),
            'payment_transaction_status' => $data->transactionStatus(),
            'payment_pay_date' => $data->payDate(),
            'paid_at' => now(),
        ];
    }
}
