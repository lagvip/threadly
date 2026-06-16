<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\VoucherRepositoryInterface;
use App\Models\Order;
use App\Models\Voucher;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class VoucherRepository implements VoucherRepositoryInterface
{
    public function query(): Builder
    {
        return Voucher::query();
    }

    public function trashedQuery(): Builder
    {
        return Voucher::onlyTrashed();
    }

    public function paginatedForAdmin(array $filters, int $perPage = 10): LengthAwarePaginator
    {
        return Voucher::query()
            ->orderBy('id', 'desc')
            ->when(! empty($filters['search']), fn ($query) => $query->where('code', 'like', '%'.$filters['search'].'%'))
            ->when(! empty($filters['type']), fn ($query) => $query->where('type', $filters['type']))
            ->when(! empty($filters['status']), fn ($query) => $query->where('status', $filters['status']))
            ->paginate($perPage);
    }

    public function trashedPaginatedForAdmin(int $perPage = 10): LengthAwarePaginator
    {
        return Voucher::onlyTrashed()->paginate($perPage);
    }

    public function create(array $data): Voucher
    {
        return Voucher::create($data);
    }

    public function update(Voucher $voucher, array $data): bool
    {
        return $voucher->update($data);
    }

    public function delete(Voucher $voucher): bool
    {
        return (bool) $voucher->delete();
    }

    public function restore(Voucher $voucher): bool
    {
        return (bool) $voucher->restore();
    }

    public function forceDelete(Voucher $voucher): bool
    {
        return (bool) $voucher->forceDelete();
    }

    public function findWithTrashed(int $id): Voucher
    {
        return Voucher::withTrashed()->findOrFail($id);
    }

    public function find(int $id): ?Voucher
    {
        return Voucher::find($id);
    }

    public function lockById(int $id): ?Voucher
    {
        return Voucher::lockForUpdate()->find($id);
    }

    public function userUsage(Voucher $voucher, int $userId): int
    {
        return $this->countUserUsage($voucher, $userId);
    }

    public function findByCode(string $code): ?Voucher
    {
        return Voucher::whereRaw('UPPER(code) = ?', [Str::upper(trim($code))])->first();
    }

    public function findActiveForCheckout(float $subtotal, int $userId): Collection
    {
        return Voucher::query()
            ->where('status', 'active')
            ->where('quantity', '>', 0)
            ->where('start_date', '<=', now())
            ->where('end_date', '>=', now())
            ->orderByDesc('value')
            ->get()
            ->filter(function (Voucher $voucher) use ($subtotal, $userId) {
                return $voucher->isValid($subtotal, $this->countUserUsage($voucher, $userId), 1);
            })
            ->values();
    }

    protected function countUserUsage(Voucher $voucher, int $userId): int
    {
        return Order::where('user_id', $userId)
            ->where('voucher_id', $voucher->id)
            ->where('order_status', '!=', 'cancelled')
            ->whereNotIn('payment_status', ['failed', 'expired', 'cancelled'])
            ->count();
    }
}
