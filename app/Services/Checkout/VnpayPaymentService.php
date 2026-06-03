<?php

namespace App\Services\Checkout;

use App\Models\Order;
use Illuminate\Http\Request;

class VnpayPaymentService
{
    public function __construct(
        protected CheckoutVoucherService $vouchers,
    ) {
    }

    public function hasValidSignature(array $inputData): bool
    {
        $vnpHashSecret = config('services.vnpay.hash_secret');
        $vnpSecureHash = $inputData['vnp_SecureHash'] ?? null;

        unset($inputData['vnp_SecureHash'], $inputData['vnp_SecureHashType']);
        ksort($inputData);

        $hashData = [];
        foreach ($inputData as $key => $value) {
            $hashData[] = urlencode($key) . '=' . urlencode($value);
        }

        $secureHash = hash_hmac('sha512', implode('&', $hashData), $vnpHashSecret);

        return $secureHash === $vnpSecureHash;
    }

    public function isValidAmount(Order $order, $vnpAmount): bool
    {
        return (int) ($vnpAmount ?? 0) === ((int) $order->total_price) * 100;
    }

    public function createPaymentUrl(Order $order): string
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
            'vnp_IpAddr' => request()->ip() ?: '127.0.0.1',
            'vnp_Locale' => 'vn',
            'vnp_OrderInfo' => 'Thanh toán đơn hàng ' . $order->order_code,
            'vnp_OrderType' => 'billpayment',
            'vnp_ReturnUrl' => $vnpReturnUrl,
            'vnp_TxnRef' => $order->order_code,
            'vnp_ExpireDate' => $vnpExpireDate,
        ];

        ksort($inputData);

        $query = '';
        $hashData = '';

        foreach ($inputData as $key => $value) {
            $encoded = urlencode($key) . '=' . urlencode($value);
            $hashData .= $hashData === '' ? $encoded : '&' . $encoded;
            $query .= $encoded . '&';
        }

        $vnpSecureHash = hash_hmac('sha512', $hashData, $vnpHashSecret);

        return $vnpUrl . '?' . $query . 'vnp_SecureHash=' . $vnpSecureHash;
    }

    public function updateFailureState(Order $order, string $responseCode, ?string $transactionStatus = null): void
    {
        if ($order->payment_status === 'paid' || $order->payment_status !== 'pending') {
            return;
        }

        $this->vouchers->restoreVoucherForOrder($order);

        if ($responseCode === '97') {
            $order->update([
                'order_status' => 'pending',
                'payment_status' => 'failed',
                'cancel_reason' => 'Sai lệch số tiền VNPay trả về',
                'payment_response_code' => $responseCode,
                'payment_transaction_status' => $transactionStatus,
            ]);

            return;
        }

        if ($responseCode === '24') {
            $order->update([
                'order_status' => 'pending',
                'payment_status' => 'cancelled',
                'cancel_reason' => 'Khách hủy phiên thanh toán VNPay',
                'payment_response_code' => $responseCode,
                'payment_transaction_status' => $transactionStatus,
            ]);

            return;
        }

        if ($responseCode === '11') {
            $order->update([
                'previous_status' => $order->order_status,
                'order_status' => 'cancelled',
                'payment_status' => 'expired',
                'cancel_reason' => 'Quá hạn thanh toán VNPay',
                'payment_response_code' => $responseCode,
                'payment_transaction_status' => $transactionStatus,
            ]);

            return;
        }

        $order->update([
            'order_status' => 'pending',
            'payment_status' => 'failed',
            'cancel_reason' => 'Thanh toán VNPay thất bại',
            'payment_response_code' => $responseCode,
            'payment_transaction_status' => $transactionStatus,
        ]);
    }

    public function saveRequestMeta(Order $order, string $createDate, string $expireDate): void
    {
        $order->update([
            'payment_request_date' => $createDate,
            'payment_expire_date' => $expireDate,
        ]);
    }

    public function paymentMeta(Request $request): array
    {
        return [
            'payment_transaction_no' => $request->vnp_TransactionNo,
            'payment_bank_code' => $request->vnp_BankCode,
            'payment_response_code' => $request->vnp_ResponseCode,
            'payment_transaction_status' => $request->vnp_TransactionStatus,
            'payment_pay_date' => $request->vnp_PayDate,
            'paid_at' => now(),
        ];
    }
}
