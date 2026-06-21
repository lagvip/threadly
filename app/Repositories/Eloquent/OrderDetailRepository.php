<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Models\OrderDetail;

class OrderDetailRepository implements OrderDetailRepositoryInterface
{
    public function create(array $data): OrderDetail
    {
        return OrderDetail::create($data);
    }

    public function existsForProduct(int $productId): bool
    {
        return OrderDetail::where('product_id', $productId)->exists();
    }

    public function existsForVariant(int $variantId): bool
    {
        return OrderDetail::where('variant_id', $variantId)->exists();
    }
}
