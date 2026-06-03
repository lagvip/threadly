<?php

namespace App\Services\Client\Refunds;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\RefundRequestEvidenceRepositoryInterface;
use App\Contracts\Repositories\RefundRequestItemRepositoryInterface;
use App\Contracts\Repositories\RefundRequestRepositoryInterface;
use App\Http\Requests\Client\Refunds\StoreRefundRequest;
use App\Models\Order;
use App\Models\RefundRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class ClientRefundRequestService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected RefundRequestRepositoryInterface $refundRequests,
        protected RefundRequestItemRepositoryInterface $refundItems,
        protected RefundRequestEvidenceRepositoryInterface $refundEvidences,
    ) {
    }

    public function submit(StoreRefundRequest $request, Order $order, int $userId): void
    {
        DB::transaction(function () use ($request, $order, $userId) {
            $order = $this->orders->lockForRefundRequest($order->id);

            $this->assertOrderOwner($order, $userId);

            if (!$order->can_request_refund) {
                throw new RuntimeException('Đơn hàng này chưa đủ điều kiện hoặc không còn số tiền để yêu cầu hoàn.');
            }

            [$selectedItems, $requestedAmount] = $this->resolveRefundSelection(
                $request->input('items', []),
                $order,
                (string) $request->input('type')
            );

            if ($requestedAmount <= 0 || $requestedAmount > (float) $order->refundable_amount) {
                throw new RuntimeException('Số tiền yêu cầu hoàn không hợp lệ.');
            }

            $refundRequest = $this->refundRequests->create([
                'order_id' => $order->id,
                'user_id' => $userId,
                'type' => $request->input('type'),
                'requested_amount' => $requestedAmount,
                'reason' => trim((string) $request->input('reason')),
                'status' => RefundRequest::STATUS_PENDING,
            ]);

            $this->storeItems($refundRequest, $selectedItems);
            $this->storeEvidences($refundRequest, $request->file('evidences', []));

            $order->update([
                'refund_status' => Order::REFUND_REQUESTED,
                'last_refund_requested_at' => now(),
            ]);
        });
    }

    public function buildRefundableItems(Order $order): array
    {
        $approvedRefundedQuantities = $this->refundItems->approvedQuantitiesForOrder($order->id);

        $productSubtotal = max((float) $order->details->sum(fn ($detail) => (float) $detail->total), 0);
        $discount = min(max((float) ($order->discount ?? 0), 0), $productSubtotal);
        $remainingDiscount = $discount;
        $details = $order->details->values();
        $items = [];

        foreach ($details as $index => $detail) {
            $quantity = max((int) $detail->quantity, 1);
            $refundedQuantity = (int) ($approvedRefundedQuantities[$detail->id] ?? 0);
            $availableQuantity = max(0, $quantity - $refundedQuantity);
            $lineTotal = max((float) ($detail->total ?? ((float) $detail->unit_price * $quantity)), 0);
            $lineDiscount = $this->lineDiscount($discount, $lineTotal, $productSubtotal, $remainingDiscount, $index, $details->count());

            $remainingDiscount = max($remainingDiscount - $lineDiscount, 0);
            $refundableLineTotal = max($lineTotal - $lineDiscount, 0);
            $unitAmount = $quantity > 0
                ? round($refundableLineTotal / $quantity, 2)
                : (float) $detail->unit_price;

            $items[] = [
                'order_detail_id' => $detail->id,
                'product_name_snapshot' => $detail->product_name ?: optional($detail->product)->name ?: 'Sản phẩm',
                'variant_snapshot' => $this->variantSnapshot($detail),
                'ordered_quantity' => $quantity,
                'refunded_quantity' => $refundedQuantity,
                'available_quantity' => $availableQuantity,
                'unit_amount' => $unitAmount,
                'line_amount' => round($unitAmount * $availableQuantity, 2),
            ];
        }

        return $items;
    }

    protected function resolveRefundSelection(array $requestItems, Order $order, string $type): array
    {
        $refundableItems = $this->buildRefundableItems($order);
        $selectedItems = [];

        if ($type === RefundRequest::TYPE_FULL) {
            foreach ($refundableItems as $item) {
                if ($item['available_quantity'] <= 0) {
                    continue;
                }

                $selectedItems[] = $this->selectedItemPayload($item, $item['available_quantity']);
            }

            if (empty($selectedItems)) {
                throw new RuntimeException('Đơn hàng này không còn sản phẩm nào có thể hoàn.');
            }

            $requestedAmount = round(array_sum(array_column($selectedItems, 'line_amount')), 2);

            return [$selectedItems, min($requestedAmount, (float) $order->refundable_amount)];
        }

        foreach ($refundableItems as $item) {
            $detailId = (string) $item['order_detail_id'];
            $input = $requestItems[$detailId] ?? null;

            if (!$input || (string) ($input['selected'] ?? '') !== '1') {
                continue;
            }

            $quantity = (int) ($input['quantity'] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            if ($quantity > $item['available_quantity']) {
                throw new RuntimeException('Số lượng hoàn của sản phẩm "' . $item['product_name_snapshot'] . '" vượt quá số lượng còn có thể hoàn.');
            }

            $selectedItems[] = $this->selectedItemPayload($item, $quantity);
        }

        if (empty($selectedItems)) {
            throw new RuntimeException('Vui lòng chọn ít nhất một sản phẩm cần hoàn tiền.');
        }

        return [$selectedItems, round(array_sum(array_column($selectedItems, 'line_amount')), 2)];
    }

    protected function selectedItemPayload(array $item, int $quantity): array
    {
        return [
            'order_detail_id' => $item['order_detail_id'],
            'product_name_snapshot' => $item['product_name_snapshot'],
            'variant_snapshot' => $item['variant_snapshot'],
            'quantity' => $quantity,
            'unit_amount' => $item['unit_amount'],
            'line_amount' => round($item['unit_amount'] * $quantity, 2),
        ];
    }

    protected function storeItems(RefundRequest $refundRequest, array $selectedItems): void
    {
        foreach ($selectedItems as $item) {
            $this->refundItems->create([
                'refund_request_id' => $refundRequest->id,
                'order_detail_id' => $item['order_detail_id'],
                'product_name_snapshot' => $item['product_name_snapshot'],
                'variant_snapshot' => $item['variant_snapshot'],
                'quantity' => $item['quantity'],
                'unit_amount' => $item['unit_amount'],
                'line_amount' => $item['line_amount'],
            ]);
        }
    }

    protected function storeEvidences(RefundRequest $refundRequest, array $files): void
    {
        foreach ($files as $file) {
            $mime = (string) $file->getMimeType();

            $this->refundEvidences->create([
                'refund_request_id' => $refundRequest->id,
                'file_type' => Str::startsWith($mime, 'video/') ? 'video' : 'image',
                'file_path' => $file->store('refund-evidences/' . now()->format('Y/m'), 'public'),
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $mime,
                'file_size' => $file->getSize() ?: 0,
            ]);
        }
    }

    protected function lineDiscount(float $discount, float $lineTotal, float $productSubtotal, float $remainingDiscount, int $index, int $count): float
    {
        if ($discount <= 0 || $lineTotal <= 0 || $productSubtotal <= 0) {
            return 0;
        }

        if ($index === $count - 1) {
            return $remainingDiscount;
        }

        return min(round($discount * ($lineTotal / $productSubtotal), 2), $remainingDiscount);
    }

    protected function variantSnapshot($detail): string
    {
        $variantParts = [];

        if (optional($detail->variant?->color)->name) {
            $variantParts[] = 'Màu: ' . $detail->variant->color->name;
        }

        if (optional($detail->variant?->size)->name) {
            $variantParts[] = 'Size: ' . $detail->variant->size->name;
        }

        return implode(' | ', $variantParts);
    }

    protected function assertOrderOwner(Order $order, int $userId): void
    {
        if ((int) $order->user_id !== $userId) {
            abort(403);
        }
    }
}
