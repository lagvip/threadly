<?php

namespace App\Services\Admin\Inventory;

use App\Contracts\Repositories\InventoryReceiptRepositoryInterface;
use App\Contracts\Repositories\InventoryReceiptItemRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;
use App\Models\InventoryReceipt;
use App\Models\StockMovement;
use App\Services\Inventory\StockMovementService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class AdminInventoryReceiptService
{
    public function __construct(
        protected InventoryReceiptRepositoryInterface $receipts,
        protected InventoryReceiptItemRepositoryInterface $receiptItems,
        protected ProductVariantRepositoryInterface $variants,
        protected StockMovementService $stockMovements
    ) {
    }

    public function create(array $data, int $userId, bool $postNow = false): InventoryReceipt
    {
        return DB::transaction(function () use ($data, $userId, $postNow) {
            $receipt = $this->receipts->create([
                'receipt_code' => $this->generateReceiptCode(),
                'created_by' => $userId,
                'status' => InventoryReceipt::STATUS_DRAFT,
                'note' => trim((string) ($data['note'] ?? '')) ?: null,
            ]);

            foreach ($data['items'] as $item) {
                $this->receiptItems->create([
                    'inventory_receipt_id' => $receipt->id,
                    'product_variant_id' => (int) $item['product_variant_id'],
                    'quantity' => (int) $item['quantity'],
                    'unit_cost' => isset($item['unit_cost']) && $item['unit_cost'] !== ''
                        ? (float) $item['unit_cost']
                        : null,
                    'note' => trim((string) ($item['note'] ?? '')) ?: null,
                ]);
            }

            if ($postNow) {
                $this->postLocked($receipt, $userId);
            }

            return $receipt->fresh(['items']);
        });
    }

    public function post(InventoryReceipt $receipt, int $userId): void
    {
        DB::transaction(fn () => $this->postLocked($receipt, $userId));
    }

    public function cancel(InventoryReceipt $receipt, int $userId): void
    {
        DB::transaction(function () use ($receipt, $userId) {
            $receipt = $this->receipts->lockById($receipt->id);

            if ($receipt->status !== InventoryReceipt::STATUS_DRAFT) {
                throw new RuntimeException('Chỉ có thể hủy phiếu nhập đang nháp.');
            }

            $this->receipts->update($receipt, [
                'status' => InventoryReceipt::STATUS_CANCELLED,
                'cancelled_at' => now(),
                'cancelled_by' => $userId,
            ]);
        });
    }

    protected function postLocked(InventoryReceipt $receipt, int $userId): void
    {
        $receipt = $this->receipts->lockById($receipt->id);
        $receipt = $this->receipts->loadItems($receipt);

        if ($receipt->status !== InventoryReceipt::STATUS_DRAFT) {
            throw new RuntimeException('Chỉ có thể xác nhận phiếu nhập đang nháp.');
        }

        if ($receipt->items->isEmpty()) {
            throw new RuntimeException('Phiếu nhập chưa có sản phẩm.');
        }

        foreach ($receipt->items as $item) {
            $variant = $this->variants->lockById($item->product_variant_id);

            if (!$variant) {
                throw new RuntimeException('Không tìm thấy biến thể sản phẩm trong phiếu nhập.');
            }

            $stockBefore = (int) $variant->quantity;
            $quantity = (int) $item->quantity;
            $stockAfter = $stockBefore + $quantity;

            $this->variants->update($variant, ['quantity' => $stockAfter]);

            $this->receiptItems->update($item, [
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
            ]);

            $this->stockMovements->record(
                $variant,
                StockMovement::TYPE_IMPORT,
                $quantity,
                $stockBefore,
                $stockAfter,
                InventoryReceipt::class,
                $receipt->id,
                $userId,
                'Nhập kho từ phiếu ' . $receipt->receipt_code,
                $item->unit_cost !== null ? (float) $item->unit_cost : null
            );
        }

        $this->receipts->update($receipt, [
            'status' => InventoryReceipt::STATUS_POSTED,
            'posted_at' => now(),
            'posted_by' => $userId,
        ]);
    }

    protected function generateReceiptCode(): string
    {
        do {
            $code = 'IR' . now()->format('ymdHis') . Str::upper(Str::random(3));
        } while ($this->receipts->receiptCodeExists($code));

        return $code;
    }
}
