<?php

namespace App\Contracts\Repositories;

use App\Models\Banner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface BannerRepositoryInterface
{
    public function query(): Builder;

    public function paginatedForAdmin(?string $search = null, int $perPage = 10): LengthAwarePaginator;

    public function trashedQuery(): Builder;

    public function trashedPaginatedForAdmin(int $perPage = 10): LengthAwarePaginator;

    public function find(int $id): Banner;

    public function findTrashed(int $id): Banner;

    public function create(array $data): Banner;

    public function update(Banner $banner, array $data): bool;

    public function delete(Banner $banner): bool;

    public function restore(Banner $banner): bool;

    public function softDeleteMany(array $ids): int;

    public function activeOrdered(): Collection;
}
