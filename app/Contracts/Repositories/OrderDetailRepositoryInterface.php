<?php

namespace App\Contracts\Repositories;

use App\Models\OrderDetail;
use Illuminate\Support\Collection;

interface OrderDetailRepositoryInterface
{
    public function allForAdmin(): Collection;

    public function create(array $data): OrderDetail;

    public function find(int $id): OrderDetail;

    public function existsForProduct(int $productId): bool;

    public function existsForVariant(int $variantId): bool;
}
