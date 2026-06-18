<?php

namespace App\Contracts\Repositories;

use App\Models\Size;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface SizeRepositoryInterface
{
    public function all(): Collection;

    public function paginatedForAdmin(string $keyword = '', bool $trashed = false, int $perPage = 10): LengthAwarePaginator;

    public function find(int $id): Size;

    public function findTrashed(int $id): Size;

    public function create(array $data): Size;

    public function update(Size $size, array $data): bool;

    public function delete(Size $size): bool;

    public function restore(Size $size): bool;

    public function forceDelete(Size $size): bool;

    public function activeNameExists(string $name): bool;

    public function trashedNameExists(string $name, ?int $exceptId = null): bool;

    public function variantUsageCount(int $sizeId): int;
}
