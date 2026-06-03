<?php

namespace App\Contracts\Repositories;

use App\Models\Color;
use Illuminate\Database\Eloquent\Builder;

interface ColorRepositoryInterface
{
    public function query(): Builder;

    public function trashedQuery(): Builder;

    public function find(int $id): Color;

    public function findTrashed(int $id): Color;

    public function create(array $data): Color;

    public function trashedDuplicate(string $name, string $code, ?int $exceptId = null): ?Color;

    public function activeDuplicateFor(Color $color): ?Color;

    public function variantUsageCount(int $colorId): int;

    public function trashedBlockedByVariantsCount(): int;
}
