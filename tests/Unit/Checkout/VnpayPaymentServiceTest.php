<?php

namespace Tests\Unit\Checkout;

use App\Models\Order;
use App\Services\Checkout\CheckoutVoucherService;
use App\Services\Checkout\VnpayPaymentService;
use Tests\TestCase;

class VnpayPaymentServiceTest extends TestCase
{
    public function test_has_valid_signature_accepts_matching_hash(): void
    {
        config(['services.vnpay.hash_secret' => 'test-secret']);

        $payload = [
            'vnp_TxnRef' => 'ORD-1001',
            'vnp_Amount' => '15000000',
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
        ];

        $payload['vnp_SecureHash'] = $this->hashPayload($payload, 'test-secret');

        $this->assertTrue($this->service()->hasValidSignature($payload));
    }

    public function test_has_valid_signature_rejects_bad_hash(): void
    {
        config(['services.vnpay.hash_secret' => 'test-secret']);

        $payload = [
            'vnp_TxnRef' => 'ORD-1001',
            'vnp_Amount' => '15000000',
            'vnp_ResponseCode' => '00',
            'vnp_TransactionStatus' => '00',
            'vnp_SecureHash' => 'bad-hash',
        ];

        $this->assertFalse($this->service()->hasValidSignature($payload));
    }

    public function test_is_valid_amount_compares_vnpay_smallest_unit(): void
    {
        $order = new Order(['total_price' => 150000]);

        $this->assertTrue($this->service()->isValidAmount($order, 15000000));
        $this->assertFalse($this->service()->isValidAmount($order, 14990000));
    }

    protected function service(): VnpayPaymentService
    {
        return new VnpayPaymentService($this->createMock(CheckoutVoucherService::class));
    }

    protected function hashPayload(array $payload, string $secret): string
    {
        unset($payload['vnp_SecureHash'], $payload['vnp_SecureHashType']);
        ksort($payload);

        $hashData = [];
        foreach ($payload as $key => $value) {
            $hashData[] = urlencode($key) . '=' . urlencode($value);
        }

        return hash_hmac('sha512', implode('&', $hashData), $secret);
    }
}
