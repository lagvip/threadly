<?php

namespace App\Contracts\Repositories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface BrandRepositoryInterface
{
    public function all(): Collection;

    public function ordered(): Collection;

    public function trashed(): Collection;

    public function find(int $id): Brand;

    public function findWithTrashed(int $id): Brand;

    public function create(array $data): Brand;
}
