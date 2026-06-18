<?php

namespace App\Contracts\Repositories;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function paginateForAdmin(array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function create(array $data): Order;

    public function update(Order $order, array $data): bool;

    public function delete(Order $order): bool;

    public function restore(Order $order): bool;

    public function forceDelete(Order $order): bool;

    public function findOrFail(int $id): Order;

    public function findByCode(string $orderCode): ?Order;

    public function orderCodeExists(string $orderCode): bool;

    public function findByGhnCodes(?string $orderCode, ?string $clientOrderCode): ?Order;

    public function lockById(int $id): Order;

    public function paginateTrashedForAdmin(int $perPage = 10): LengthAwarePaginator;

    public function countByStatus(string $status): int;

    public function countPendingPayment(): int;

    public function recentForUser(int $userId, int $limit = 5);

    public function recentForUserWithDetails(int $userId, int $limit = 3): Collection;

    public function countForUser(int $userId): int;

    public function countForUserByStatuses(int $userId, array $statuses): int;

    public function countForUserByStatus(int $userId, string $status): int;

    public function paginateForUser(int $userId, array $filters = [], int $perPage = 10): LengthAwarePaginator;

    public function findForUserWithDetail(int $id, int $userId): Order;

    public function findForUserWithDetails(int $id, int $userId): Order;

    public function findForUserWithReviewDetails(int $id, int $userId): Order;

    public function findForUser(int $id, int $userId): Order;

    public function lockForUserCancellation(int $id, int $userId): Order;

    public function lockForRefundRequest(int $id): Order;

    public function restoreManyWithTrashed(array $ids): int;

    public function forceDeleteManyWithTrashed(array $ids): int;
}
