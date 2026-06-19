<?php

namespace Tests\Unit\Admin;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Contracts\Repositories\RefundRequestItemRepositoryInterface;
use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Contracts\Repositories\WalletRepositoryInterface;
use App\Contracts\Repositories\WalletTransactionRepositoryInterface;
use App\Enums\RefundRequestStatus;
use App\Events\Inventory\StockMovementRecorded;
use App\Models\OrderDetail;
use App\Models\ProductVariant;
use App\Models\RefundRequest;
use App\Models\RefundRequestItem;
use App\Services\Admin\Refunds\AdminRefundWorkflowService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class AdminRefundWorkflowServiceTest extends TestCase
{
    public function test_restock_updates_variant_stock_and_marks_refund_items_restocked(): void
    {
        Event::fake([StockMovementRecorded::class]);

        $refundRequest = new RefundRequest([
            'status' => RefundRequestStatus::Approved->value,
        ]);
        $refundRequest->id = 77;
        $refundRequest->exists = true;

        $detail = new OrderDetail(['variant_id' => 55]);
        $detail->id = 100;
        $detail->exists = true;

        $item = new RefundRequestItem([
            'order_detail_id' => 100,
            'product_name_snapshot' => 'Áo demo',
            'quantity' => 3,
            'restocked_quantity' => 1,
        ]);
        $item->id = 88;
        $item->exists = true;
        $item->setRelation('orderDetail', $detail);

        $refundRequest->setRelation('items', new Collection([$item]));

        $variant = new ProductVariant(['quantity' => 10]);
        $variant->id = 55;
        $variant->exists = true;

        $refundRequests = $this->createMock(RefundRequestRepositoryInterface::class);
        $refundRequests->expects($this->once())
            ->method('lockWithItemsAndOrderDetail')
            ->with(77)
            ->willReturn($refundRequest);
        $refundRequests->expects($this->once())
            ->method('update')
            ->with($refundRequest, $this->callback(fn (array $data) => $data['restocked_by'] === 5
                && $data['restock_note'] === 'Nhập lại hàng demo'
                && array_key_exists('restocked_at', $data)))
            ->willReturn(true);

        $variants = $this->createMock(ProductVariantRepositoryInterface::class);
        $variants->expects($this->once())
            ->method('lockById')
            ->with(55)
            ->willReturn($variant);
        $variants->expects($this->once())
            ->method('update')
            ->with($variant, ['quantity' => 12])
            ->willReturn(true);

        $refundItems = $this->createMock(RefundRequestItemRepositoryInterface::class);
        $refundItems->expects($this->once())
            ->method('update')
            ->with($item, $this->callback(fn (array $data) => $data['restocked_quantity'] === 3
                && array_key_exists('restocked_at', $data)))
            ->willReturn(true);

        $this->service($refundRequests, $variants, $refundItems)
            ->restock($refundRequest, 5, 'Nhập lại hàng demo');
    }

    protected function service(
        RefundRequestRepositoryInterface $refundRequests,
        ProductVariantRepositoryInterface $variants,
        RefundRequestItemRepositoryInterface $refundItems,
    ): AdminRefundWorkflowService {
        return new AdminRefundWorkflowService(
            $this->createMock(OrderRepositoryInterface::class),
            $variants,
            $refundRequests,
            $refundItems,
            $this->createMock(WalletRepositoryInterface::class),
            $this->createMock(WalletTransactionRepositoryInterface::class),
        );
    }
}
