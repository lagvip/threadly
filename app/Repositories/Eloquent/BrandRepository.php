<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\BrandRepositoryInterface;
use App\Models\Brand;
use Illuminate\Support\Collection;

class BrandRepository implements BrandRepositoryInterface
{
    public function all(): Collection
    {
        return Brand::all();
    }

    public function ordered(): Collection
    {
        return Brand::orderBy('name')->get();
    }

    public function trashed(): Collection
    {
        return Brand::onlyTrashed()->get();
    }

    public function find(int $id): Brand
    {
        return Brand::findOrFail($id);
    }

    public function findWithTrashed(int $id): Brand
    {
        return Brand::withTrashed()->findOrFail($id);
    }

    public function create(array $data): Brand
    {
        return Brand::create($data);
    }

    public function update(Brand $brand, array $data): bool
    {
        return $brand->update($data);
    }

    public function delete(Brand $brand): bool
    {
        return (bool) $brand->delete();
    }

    public function restore(Brand $brand): bool
    {
        return (bool) $brand->restore();
    }

    public function forceDelete(Brand $brand): bool
    {
        return (bool) $brand->forceDelete();
    }
}
