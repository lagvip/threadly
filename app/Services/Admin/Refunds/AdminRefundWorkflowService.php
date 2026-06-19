<?php

namespace App\Services\Admin\Refunds;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Contracts\Repositories\RefundRequestItemRepositoryInterface;
use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Contracts\Repositories\WalletRepositoryInterface;
use App\Contracts\Repositories\WalletTransactionRepositoryInterface;
use App\Enums\GhnOrderStatus;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderRefundStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\RefundRequestStatus;
use App\Enums\RefundRequestType;
use App\Enums\StockMovementType;
use App\Enums\WalletTransactionType;
use App\Events\Inventory\StockMovementRecorded;
use App\Events\Sales\RefundApproved;
use App\Events\Sales\RefundRejected;
use App\Models\Order;
use App\Models\RefundRequest;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class AdminRefundWorkflowService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected ProductVariantRepositoryInterface $variants,
        protected RefundRequestRepositoryInterface $refundRequests,
        protected RefundRequestItemRepositoryInterface $refundItems,
        protected WalletRepositoryInterface $wallets,
        protected WalletTransactionRepositoryInterface $walletTransactions,
    ) {}

    public function approve(RefundRequest $refundRequest, int $adminId, ?string $adminNote = null): void
    {
        $this->ensurePending($refundRequest);

        $approvedEvent = null;

        DB::transaction(function () use ($refundRequest, $adminId, $adminNote, &$approvedEvent) {
            $refundRequest = $this->refundRequests->lockWithItems($refundRequest->id);

            $this->ensurePending($refundRequest);

            $order = $this->orders->lockById($refundRequest->order_id);

            $this->assertRefundCanBeApproved($refundRequest, $order);

            if ($this->walletTransactions->refundCreditExists($refundRequest->id)) {
                throw new RuntimeException('Yêu cầu hoàn tiền này đã được cộng ví trước đó.');
            }

            $approvedAmount = (float) $refundRequest->requested_amount;
            $maxAmount = (float) $order->refundable_amount;

            if ($approvedAmount <= 0 || $approvedAmount > $maxAmount) {
                throw new RuntimeException('Số tiền duyệt hoàn không được vượt quá số tiền còn lại có thể hoàn.');
            }

            if ($refundRequest->type === RefundRequestType::Partial->value && $refundRequest->items->isEmpty()) {
                throw new RuntimeException('Yêu cầu hoàn theo sản phẩm không có sản phẩm nào được chọn.');
            }

            $wallet = $this->wallets->firstOrCreateForUser($refundRequest->user_id);
            $wallet = $this->wallets->lockById($wallet->id);

            $balanceBefore = (float) $wallet->balance;
            $balanceAfter = $balanceBefore + $approvedAmount;

            $this->wallets->update($wallet, ['balance' => $balanceAfter]);

            $this->walletTransactions->create([
                'wallet_id' => $wallet->id,
                'user_id' => $refundRequest->user_id,
                'order_id' => $order->id,
                'refund_request_id' => $refundRequest->id,
                'type' => WalletTransactionType::RefundCredit->value,
                'amount' => $approvedAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'description' => 'Hoàn tiền demo vào ví cho đơn #'.$order->order_code,
            ]);

            $newRefundedAmount = (float) $order->refunded_amount + $approvedAmount;
            $isFullyRefunded = $newRefundedAmount >= ((float) $order->refundable_product_amount - 0.01);

            $this->orders->update($order, [
                'refunded_amount' => $newRefundedAmount,
                'refund_status' => $isFullyRefunded
                    ? OrderRefundStatus::Refunded->value
                    : OrderRefundStatus::PartiallyRefunded->value,
                'last_refunded_at' => now(),
            ]);

            $this->refundRequests->update($refundRequest, [
                'approved_amount' => $approvedAmount,
                'status' => RefundRequestStatus::Approved->value,
                'admin_id' => $adminId,
                'admin_note' => trim((string) $adminNote) ?: null,
                'approved_at' => now(),
            ]);

            $approvedEvent = new RefundApproved(
                (int) $refundRequest->id,
                (int) $order->id,
                (int) $refundRequest->user_id,
                $adminId,
                $approvedAmount,
            );
        });

        if ($approvedEvent) {
            event($approvedEvent);
        }
    }

    public function restock(RefundRequest $refundRequest, int $adminId, ?string $restockNote = null): void
    {
        DB::transaction(function () use ($refundRequest, $adminId, $restockNote) {
            $refundRequest = $this->refundRequests->lockWithItemsAndOrderDetail($refundRequest->id);

            if ($refundRequest->status !== RefundRequestStatus::Approved->value) {
                throw new RuntimeException('Chỉ được nhập kho sau khi yêu cầu hoàn tiền đã được duyệt.');
            }

            if ($refundRequest->restocked_at) {
                throw new RuntimeException('Yêu cầu hoàn này đã được nhập lại kho trước đó.');
            }

            if ($refundRequest->items->isEmpty()) {
                throw new RuntimeException('Yêu cầu hoàn không có dòng sản phẩm để nhập kho.');
            }

            foreach ($refundRequest->items as $item) {
                $restockedQuantity = (int) ($item->restocked_quantity ?? 0);
                $quantityToRestock = max((int) $item->quantity - $restockedQuantity, 0);

                if ($quantityToRestock <= 0) {
                    continue;
                }

                $detail = $item->orderDetail;

                if (! $detail || ! $detail->variant_id) {
                    throw new RuntimeException('Không tìm thấy biến thể sản phẩm để nhập lại kho cho dòng hoàn: '.$item->product_name_snapshot);
                }

                $variant = $this->variants->lockById($detail->variant_id);

                if (! $variant) {
                    throw new RuntimeException('Biến thể sản phẩm không còn tồn tại: '.$item->product_name_snapshot);
                }

                $stockBefore = (int) $variant->quantity;
                $stockAfter = $stockBefore + $quantityToRestock;

                $this->variants->update($variant, ['quantity' => $stockAfter]);

                StockMovementRecorded::dispatch(
                    (int) $variant->id,
                    StockMovementType::RefundRestock->value,
                    $quantityToRestock,
                    $stockBefore,
                    $stockAfter,
                    RefundRequest::class,
                    $refundRequest->id,
                    $adminId,
                    'Nhập lại kho từ yêu cầu hoàn #'.$refundRequest->id
                );

                $this->refundItems->update($item, [
                    'restocked_quantity' => $restockedQuantity + $quantityToRestock,
                    'restocked_at' => now(),
                ]);
            }

            $this->refundRequests->update($refundRequest, [
                'restocked_at' => now(),
                'restocked_by' => $adminId,
                'restock_note' => trim((string) $restockNote) ?: null,
            ]);
        });
    }

    public function reject(RefundRequest $refundRequest, int $adminId, string $adminNote): void
    {
        $this->ensurePending($refundRequest);

        $rejectedEvent = null;

        DB::transaction(function () use ($refundRequest, $adminId, $adminNote, &$rejectedEvent) {
            $refundRequest = $this->refundRequests->lockById($refundRequest->id);

            $this->ensurePending($refundRequest);

            $order = $this->orders->lockById($refundRequest->order_id);

            $this->refundRequests->update($refundRequest, [
                'status' => RefundRequestStatus::Rejected->value,
                'admin_id' => $adminId,
                'admin_note' => trim($adminNote) ?: null,
                'rejected_at' => now(),
            ]);

            $this->orders->update($order, [
                'refund_status' => ((float) $order->refunded_amount) > 0
                    ? OrderRefundStatus::PartiallyRefunded->value
                    : OrderRefundStatus::Rejected->value,
            ]);

            $rejectedEvent = new RefundRejected(
                (int) $refundRequest->id,
                (int) $order->id,
                (int) $refundRequest->user_id,
                $adminId,
                trim($adminNote) ?: null,
            );
        });

        if ($rejectedEvent) {
            event($rejectedEvent);
        }
    }

    protected function ensurePending(RefundRequest $refundRequest): void
    {
        if ($refundRequest->status !== RefundRequestStatus::Pending->value) {
            throw new RuntimeException('Yêu cầu hoàn tiền này đã được xử lý trước đó.');
        }
    }

    protected function assertRefundCanBeApproved(RefundRequest $refundRequest, Order $order): void
    {
        if (! in_array($order->payment_method, [PaymentMethod::Vnpay->value, PaymentMethod::Cod->value], true)) {
            throw new RuntimeException('Phương thức thanh toán của đơn hàng không hỗ trợ hoàn tiền demo.');
        }

        $isDeliveredRefund = $order->payment_status === OrderPaymentStatus::Paid->value
            && $order->order_status === OrderStatus::Delivered->value;

        $isPaidVnpayCancelledRefund = $order->payment_method === PaymentMethod::Vnpay->value
            && $order->payment_status === OrderPaymentStatus::Paid->value
            && $order->order_status === OrderStatus::Cancelled->value
            && ($order->refund_status ?? OrderRefundStatus::None->value) === OrderRefundStatus::Requested->value
            && empty($order->ghn_order_code);

        if (! $isDeliveredRefund && ! $isPaidVnpayCancelledRefund) {
            throw new RuntimeException('Chỉ duyệt hoàn tiền cho đơn đã giao thành công hoặc đơn VNPay đã thanh toán nhưng hủy trước khi xử lý.');
        }

        if ($isDeliveredRefund && $order->ghn_order_code && $order->ghn_status !== GhnOrderStatus::Delivered->value) {
            throw new RuntimeException('Đơn có vận đơn GHN nhưng GHN chưa xác nhận giao thành công.');
        }
    }
}
