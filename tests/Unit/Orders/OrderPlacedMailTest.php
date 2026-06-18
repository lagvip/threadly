<?php

namespace Tests\Unit\Orders;

use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Mail\OrderPlacedMail;
use App\Models\Order;
use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class OrderPlacedMailTest extends TestCase
{
    public function test_vnpay_paid_order_mail_renders_without_missing_template_variables(): void
    {
        $order = new Order([
            'order_code' => 'OD001',
            'name' => 'Customer',
            'email' => 'customer@example.test',
            'phone' => '0900000000',
            'address' => 'Ha Noi',
            'payment_method' => PaymentMethod::Vnpay->value,
            'payment_status' => OrderPaymentStatus::Paid->value,
            'order_status' => OrderStatus::Pending->value,
            'shipping_fee' => 0,
            'discount' => 0,
            'total_price' => 100000,
        ]);
        $order->setRelation('details', new Collection);

        $html = (new OrderPlacedMail($order))->render();

        $this->assertStringContainsString('OD001', $html);
    }
}
