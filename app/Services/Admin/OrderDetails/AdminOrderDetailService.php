<?php

namespace App\Services\Admin\OrderDetails;

use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Contracts\Repositories\ProductVariantRepositoryInterface;

class AdminOrderDetailService
{
    public function __construct(
        protected OrderDetailRepositoryInterface $orderDetails,
        protected ProductVariantRepositoryInterface $variants,
    ) {
    }

    public function indexData(): array
    {
        return [
            'orderDetails' => $this->orderDetails->allForAdmin(),
        ];
    }

    public function create(array $data): void
    {
        $variantId = (int) ($data['variant_id'] ?? $data['id_variant']);
        $variant = $this->variants->query()->with('product')->find($variantId);

        $this->orderDetails->create([
            'order_id' => $data['order_id'] ?? $data['id_order'],
            'product_id' => $variant?->id_product,
            'variant_id' => $variantId,
            'product_name' => $variant?->product?->name,
            'quantity' => (int) $data['quantity'],
            'unit_price' => (float) $data['unit_price'],
            'total' => (int) $data['quantity'] * (float) $data['unit_price'],
        ]);
    }

    public function delete(int $id): void
    {
        $this->orderDetails->find($id)->delete();
    }
}
