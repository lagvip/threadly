<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\OrderDetailRepositoryInterface;
use App\Models\OrderDetail;
use Illuminate\Support\Collection;

class OrderDetailRepository implements OrderDetailRepositoryInterface
{
    public function allForAdmin(): Collection
    {
        return OrderDetail::with('order', 'variant.product', 'variant.color', 'variant.size')->get();
    }

    public function create(array $data): OrderDetail
    {
        return OrderDetail::create($data);
    }

    public function find(int $id): OrderDetail
    {
        return OrderDetail::findOrFail($id);
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
