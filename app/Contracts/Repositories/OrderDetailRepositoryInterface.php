<?php

namespace App\Contracts\Repositories;

use App\Models\OrderDetail;

interface OrderDetailRepositoryInterface
{
    public function create(array $data): OrderDetail;

    public function existsForProduct(int $productId): bool;

    public function existsForVariant(int $variantId): bool;
}
