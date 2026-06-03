<?php

namespace App\Contracts\Repositories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface OrderRepositoryInterface
{
    public function adminIndexQuery(): Builder;

    public function create(array $data): Order;

    public function findOrFail(int $id): Order;

    public function findByCode(string $orderCode): ?Order;

    public function orderCodeExists(string $orderCode): bool;

    public function findByGhnCodes(?string $orderCode, ?string $clientOrderCode): ?Order;

    public function lockById(int $id): Order;

    public function trashedForAdmin(): Builder;

    public function countByStatus(string $status): int;

    public function countPendingPayment(): int;

    public function recentForUser(int $userId, int $limit = 5);

    public function recentForUserWithDetails(int $userId, int $limit = 3): Collection;

    public function countForUser(int $userId): int;

    public function countForUserByStatuses(int $userId, array $statuses): int;

    public function countForUserByStatus(int $userId, string $status): int;

    public function clientIndexQuery(int $userId): Builder;

    public function findForUserWithDetail(int $id, int $userId): Order;

    public function findForUserWithDetails(int $id, int $userId): Order;

    public function findForUserWithReviewDetails(int $id, int $userId): Order;

    public function findForUser(int $id, int $userId): Order;

    public function lockForUserCancellation(int $id, int $userId): Order;

    public function lockForRefundRequest(int $id): Order;

    public function restoreManyWithTrashed(array $ids): int;

    public function forceDeleteManyWithTrashed(array $ids): int;
}
