<?php

namespace App\Services\Admin\Orders;

use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\RefundRequestItemRepositoryInterface;
use App\Enums\GhnOrderStatus;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderRefundStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Enums\VoucherType;
use App\Models\Order;
use App\Support\Pagination;

class AdminOrderQueryService
{
    public function __construct(
        protected OrderRepositoryInterface $orders,
        protected RefundRequestItemRepositoryInterface $refundItems,
    ) {}

    public function indexData(array $filters): array
    {
        return [
            'orders' => Pagination::withQueryString($this->orders->paginateForAdmin($filters, 10)),
            'orderCancel' => $this->orders->countByStatus(OrderStatus::Cancelled->value),
            'orderDelivering' => $this->orders->countByStatus(OrderStatus::Shipped->value),
            'pendingPayment' => $this->orders->countPendingPayment(),
            'orderDelivered' => $this->orders->countByStatus(OrderStatus::Delivered->value),
            'paymentStatusOptions' => $this->paymentStatusOptions(),
            'orderStatusOptions' => $this->orderStatusOptions(),
            'refundStatusOptions' => $this->refundStatusOptions(),
            'codPaymentMethod' => PaymentMethod::Cod->value,
            'paidPaymentStatus' => OrderPaymentStatus::Paid->value,
            'deliveredOrderStatus' => OrderStatus::Delivered->value,
            'cancelledOrderStatus' => OrderStatus::Cancelled->value,
            'ghnTerminalStatuses' => GhnOrderStatus::terminalValues(),
        ];
    }

    public function loadForShow(Order $order): Order
    {
        return $order->load([
            'user',
            'voucher',
            'details.variant.product',
            'details.variant.size',
            'details.variant.color',
        ]);
    }

    public function showData(Order $order): array
    {
        $order = $this->loadForShow($order);

        return [
            'order' => $order,
            'approvedRefundByDetail' => $this->refundItems->approvedSummaryForOrder((int) $order->id),
            'paymentStatusOptions' => $this->paymentStatusOptions(),
            'orderStatusOptions' => $this->orderStatusOptions(),
            'paymentMethodOptions' => $this->paymentMethodOptions(),
            'noRefundStatus' => OrderRefundStatus::None->value,
            'ghnTerminalStatuses' => GhnOrderStatus::terminalValues(),
            'ghnSimulationOptions' => GhnOrderStatus::simulationOptions(),
            'voucherTypeOptions' => $this->voucherTypeOptions(),
            'percentVoucherType' => VoucherType::Percent->value,
        ];
    }

    public function findForStatusUpdate(int $id): Order
    {
        return $this->orders->findOrFail($id);
    }

    public function trashData(): array
    {
        return [
            'orders' => $this->orders->paginateTrashedForAdmin(10),
        ];
    }

    protected function paymentStatusOptions(): array
    {
        return collect(OrderPaymentStatus::cases())
            ->mapWithKeys(fn (OrderPaymentStatus $status) => [
                $status->value => [
                    'label' => $status->label(),
                    'color' => $status->badgeClass(),
                ],
            ])
            ->all();
    }

    protected function orderStatusOptions(): array
    {
        return collect(OrderStatus::cases())
            ->mapWithKeys(fn (OrderStatus $status) => [
                $status->value => [
                    'label' => $status->label(),
                    'color' => 'bg-'.$status->badge(),
                ],
            ])
            ->all();
    }

    protected function refundStatusOptions(): array
    {
        return collect(OrderRefundStatus::cases())
            ->mapWithKeys(fn (OrderRefundStatus $status) => [
                $status->value => [
                    'label' => $status->label(),
                    'class' => $status->badgeClass(),
                ],
            ])
            ->all();
    }

    protected function paymentMethodOptions(): array
    {
        return collect(PaymentMethod::cases())
            ->mapWithKeys(fn (PaymentMethod $method) => [$method->value => $method->label()])
            ->all();
    }

    protected function voucherTypeOptions(): array
    {
        return collect(VoucherType::cases())
            ->mapWithKeys(fn (VoucherType $type) => [$type->value => $type->label()])
            ->all();
    }
}
