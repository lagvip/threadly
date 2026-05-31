<?php

namespace App\Contracts\Repositories;

use App\Models\Banner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface BannerRepositoryInterface
{
    public function query(): Builder;

    public function trashedQuery(): Builder;

    public function find(int $id): Banner;

    public function findTrashed(int $id): Banner;

    public function create(array $data): Banner;

    public function softDeleteMany(array $ids): int;

    public function activeOrdered(): Collection;
}
