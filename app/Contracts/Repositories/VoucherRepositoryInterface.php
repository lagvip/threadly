<?php

namespace App\Contracts\Repositories;

use App\Models\Voucher;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface VoucherRepositoryInterface
{
    public function query(): Builder;

    public function trashedQuery(): Builder;

    public function create(array $data): Voucher;

    public function findWithTrashed(int $id): Voucher;

    public function find(int $id): ?Voucher;

    public function lockById(int $id): ?Voucher;

    public function userUsage(Voucher $voucher, int $userId): int;

    public function findByCode(string $code): ?Voucher;

    public function findActiveForCheckout(float $subtotal, int $userId): Collection;
}
