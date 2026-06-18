<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\BannerRepositoryInterface;
use App\Models\Banner;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BannerRepository implements BannerRepositoryInterface
{
    protected function query(): Builder
    {
        return Banner::query();
    }

    public function paginatedForAdmin(?string $search = null, int $perPage = 10): LengthAwarePaginator
    {
        return Banner::query()
            ->when($search !== null && $search !== '', fn ($query) => $query->where('title', 'like', '%'.$search.'%'))
            ->latest('id')
            ->paginate($perPage);
    }

    protected function trashedQuery(): Builder
    {
        return Banner::onlyTrashed();
    }

    public function trashedPaginatedForAdmin(int $perPage = 10): LengthAwarePaginator
    {
        return Banner::onlyTrashed()->latest('id')->paginate($perPage);
    }

    public function find(int $id): Banner
    {
        return Banner::findOrFail($id);
    }

    public function findTrashed(int $id): Banner
    {
        return Banner::onlyTrashed()->findOrFail($id);
    }

    public function create(array $data): Banner
    {
        return Banner::create($data);
    }

    public function update(Banner $banner, array $data): bool
    {
        return $banner->update($data);
    }

    public function delete(Banner $banner): bool
    {
        return (bool) $banner->delete();
    }

    public function restore(Banner $banner): bool
    {
        return (bool) $banner->restore();
    }

    public function softDeleteMany(array $ids): int
    {
        return Banner::whereIn('id', $ids)->delete();
    }

    public function activeOrdered(): Collection
    {
        return Banner::where('is_active', 1)->orderBy('position', 'asc')->get();
    }
}
