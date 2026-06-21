<?php

namespace App\Contracts\Repositories;

use App\Models\Voucher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface VoucherRepositoryInterface
{
    public function paginatedForAdmin(array $filters, int $perPage = 10): LengthAwarePaginator;

    public function trashedPaginatedForAdmin(int $perPage = 10): LengthAwarePaginator;

    public function create(array $data): Voucher;

    public function update(Voucher $voucher, array $data): bool;

    public function delete(Voucher $voucher): bool;

    public function restore(Voucher $voucher): bool;

    public function forceDelete(Voucher $voucher): bool;

    public function findWithTrashed(int $id): Voucher;

    public function find(int $id): ?Voucher;

    public function lockById(int $id): ?Voucher;

    public function userUsage(Voucher $voucher, int $userId): int;

    public function findByCode(string $code): ?Voucher;

    public function findActiveForCheckout(float $subtotal, int $userId): Collection;

    public function incrementQuantity(Voucher $voucher): void;

    public function expireActiveEndedAt(string $now): int;
}
