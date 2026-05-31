<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use App\Models\Category;

interface CategoryRepositoryInterface
{
    public function query(): Builder;

    public function trashedQuery(): Builder;

    public function create(array $data): Category;

    public function find(int $id): Category;

    public function findWithParentAndChildren(int $id): Category;

    public function findWithTrashed(int $id): Category;

    public function rootTree(): Collection;

    public function rootTreeOrdered(): Collection;

    public function rootCategories(?int $exceptId = null): Collection;

    public function childCategories(): Collection;

    public function findWithChildren(int $id): Category;

    public function findWithChildrenOrNull(int $id): ?Category;
}
