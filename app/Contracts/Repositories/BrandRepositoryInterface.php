<?php

namespace App\Contracts\Repositories;

use App\Models\Brand;
use Illuminate\Support\Collection;

interface BrandRepositoryInterface
{
    public function all(): Collection;

    public function ordered(): Collection;

    public function trashed(): Collection;

    public function find(int $id): Brand;

    public function findWithTrashed(int $id): Brand;

    public function create(array $data): Brand;

    public function update(Brand $brand, array $data): bool;

    public function delete(Brand $brand): bool;

    public function restore(Brand $brand): bool;

    public function forceDelete(Brand $brand): bool;
}
