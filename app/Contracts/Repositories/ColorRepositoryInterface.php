<?php

namespace App\Contracts\Repositories;

use App\Models\Color;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface ColorRepositoryInterface
{
    public function all(): Collection;

    public function paginatedForAdmin(string $keyword = '', bool $trashed = false, int $perPage = 10): LengthAwarePaginator;

    public function find(int $id): Color;

    public function findTrashed(int $id): Color;

    public function create(array $data): Color;

    public function update(Color $color, array $data): bool;

    public function delete(Color $color): bool;

    public function restore(Color $color): bool;

    public function forceDelete(Color $color): bool;

    public function forceDeleteTrashed(): int;

    public function trashedDuplicate(string $name, string $code, ?int $exceptId = null): ?Color;

    public function activeDuplicateFor(Color $color): ?Color;

    public function variantUsageCount(int $colorId): int;

    public function trashedBlockedByVariantsCount(): int;
}
