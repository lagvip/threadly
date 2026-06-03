<?php

namespace App\Contracts\Repositories;

use App\Models\Size;
use Illuminate\Database\Eloquent\Builder;

interface SizeRepositoryInterface
{
    public function query(): Builder;

    public function trashedQuery(): Builder;

    public function find(int $id): Size;

    public function findTrashed(int $id): Size;

    public function create(array $data): Size;

    public function activeNameExists(string $name): bool;

    public function trashedNameExists(string $name, ?int $exceptId = null): bool;

    public function variantUsageCount(int $sizeId): int;
}
