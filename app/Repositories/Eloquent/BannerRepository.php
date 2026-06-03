<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\BannerRepositoryInterface;
use App\Models\Banner;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class BannerRepository implements BannerRepositoryInterface
{
    public function query(): Builder
    {
        return Banner::query();
    }

    public function trashedQuery(): Builder
    {
        return Banner::onlyTrashed();
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

    public function softDeleteMany(array $ids): int
    {
        return Banner::whereIn('id', $ids)->delete();
    }

    public function activeOrdered(): Collection
    {
        return Banner::where('is_active', 1)->orderBy('position', 'asc')->get();
    }
}
