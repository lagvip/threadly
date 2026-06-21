<?php

namespace Tests\Unit\Files;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\RefundRequestEvidenceRepositoryInterface;
use App\Contracts\Repositories\RefundRequestItemRepositoryInterface;
use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderRefundStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\RefundRequestType;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\RefundRequest;
use App\Services\Admin\Brands\AdminBrandService;
use App\Services\Client\Refunds\ClientRefundRequestService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class RollbackSafeUploadsTest extends TestCase
{
    public function test_brand_create_deletes_new_image_when_database_write_fails(): void
    {
        Storage::fake('public');
        $brands = $this->createMock(BrandRepositoryInterface::class);
        $brands->method('create')->willThrowException(new RuntimeException('DB failed'));

        try {
            (new AdminBrandService($brands))->create(
                ['name' => 'Brand'],
                UploadedFile::fake()->create('brand.jpg', 10, 'image/jpeg')
            );
            $this->fail('Expected database failure.');
        } catch (RuntimeException $e) {
            $this->assertSame('DB failed', $e->getMessage());
        }

        $this->assertSame([], Storage::disk('public')->allFiles());
    }

    public function test_refund_submit_deletes_all_evidences_when_database_write_fails(): void
    {
        Storage::fake('public');
        DB::shouldReceive('transaction')->once()->andReturnUsing(fn ($callback) => $callback());

        $detail = new OrderDetail([
            'product_name' => 'Áo',
            'quantity' => 1,
            'unit_price' => 100000,
            'total' => 100000,
        ]);
        $detail->id = 20;

        $order = new Order([
            'user_id' => 5,
            'payment_method' => PaymentMethod::Vnpay->value,
            'payment_status' => OrderPaymentStatus::Paid->value,
            'order_status' => OrderStatus::Delivered->value,
            'refund_status' => OrderRefundStatus::None->value,
            'discount' => 0,
            'refunded_amount' => 0,
        ]);
        $order->id = 10;
        $order->setRelation('details', collect([$detail]));
        $order->setRelation('refundRequests', collect());

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->method('lockForRefundRequest')->with(10)->willReturn($order);
        $refundRequests = $this->createMock(RefundRequestRepositoryInterface::class);
        $refundRequest = new RefundRequest;
        $refundRequest->id = 30;
        $refundRequests->method('create')->willReturn($refundRequest);
        $refundItems = $this->createMock(RefundRequestItemRepositoryInterface::class);
        $refundItems->method('approvedQuantitiesForOrder')->willReturn(collect());
        $refundEvidences = $this->createMock(RefundRequestEvidenceRepositoryInterface::class);
        $refundEvidences->method('create')->willThrowException(new RuntimeException('DB failed'));

        $service = new ClientRefundRequestService($orders, $refundRequests, $refundItems, $refundEvidences);

        try {
            $service->submit([
                'type' => RefundRequestType::Full->value,
                'reason' => 'Sản phẩm lỗi',
            ], [UploadedFile::fake()->create('proof.jpg', 10, 'image/jpeg')], $order, 5);
            $this->fail('Expected database failure.');
        } catch (RuntimeException $e) {
            $this->assertSame('DB failed', $e->getMessage());
        }

        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
