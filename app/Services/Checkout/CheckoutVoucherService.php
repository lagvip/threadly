<?php

namespace App\Services\Checkout;

use App\Contracts\Repositories\VoucherRepositoryInterface;
use App\Enums\VoucherType;
use App\Models\Order;
use App\Models\Voucher;

class CheckoutVoucherService
{
    public function __construct(protected VoucherRepositoryInterface $vouchers) {}

    public function getAppliedVoucherPreview(float $subtotal, ?int $userId): ?array
    {
        $voucherId = session(config('threadly.checkout.voucher_session_key').'.voucher_id');

        if (! $voucherId || ! $userId) {
            return null;
        }

        $voucher = $this->vouchers->find((int) $voucherId);

        if (! $voucher) {
            session()->forget(config('threadly.checkout.voucher_session_key'));

            return null;
        }

        $currentUses = $this->getUserVoucherUsage($voucher, $userId);

        if (! $voucher->isValid($subtotal, $currentUses, 1)) {
            session()->forget(config('threadly.checkout.voucher_session_key'));

            return null;
        }

        $discount = min((float) $voucher->getDiscount($subtotal), $subtotal);

        return [
            'voucher_id' => $voucher->id,
            'voucher_code' => $voucher->code,
            'discount' => $discount,
        ];
    }

    public function getAvailableVouchersForCheckout(float $subtotal, int $userId)
    {
        return $this->vouchers->findActiveForCheckout($subtotal, $userId)
            ->map(function ($voucher) use ($subtotal) {
                $type = VoucherType::tryFrom((string) $voucher->type);

                return [
                    'id' => $voucher->id,
                    'code' => $voucher->code,
                    'type' => $voucher->type,
                    'is_percent_type' => $type === VoucherType::Percent,
                    'type_unit' => $type?->unit() ?? '',
                    'value' => $voucher->value,
                    'max_discount' => $voucher->max_discount,
                    'min_order_value' => $voucher->min_order_value,
                    'discount_preview' => (float) $voucher->getDiscount($subtotal),
                    'end_date' => $voucher->end_date,
                ];
            })
            ->values();
    }

    public function getUserVoucherUsage(Voucher $voucher, int $userId): int
    {
        return $this->vouchers->userUsage($voucher, $userId);
    }

    public function restoreVoucherForOrder(Order $order): void
    {
        if (! $order->voucher_id) {
            return;
        }

        $voucher = $this->vouchers->lockById($order->voucher_id);

        if (! $voucher) {
            return;
        }

        $voucher->increment('quantity');
    }

    public function reserveVoucherForRepay(Order $order): void
    {
        if (! $order->voucher_id) {
            return;
        }

        $order->loadMissing('details');

        $voucher = $this->vouchers->lockById($order->voucher_id);

        if (! $voucher) {
            throw new \Exception('Voucher của đơn hàng không còn tồn tại.');
        }

        $subtotal = (float) $order->details->sum(fn ($detail) => (float) $detail->total);
        $currentUses = $this->getUserVoucherUsage($voucher, (int) $order->user_id);

        if (! $voucher->isValid($subtotal, $currentUses, 1)) {
            throw new \Exception('Voucher của đơn hàng không còn khả dụng để thanh toán lại.');
        }

        $voucher->decreaseQuantity();
    }
}
