<?php

namespace App\Contracts\Repositories;

use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

interface RoleRepositoryInterface
{
    public function queryWithUserCount(): Builder;

    public function trashedQueryWithUserCount(): Builder;

    public function ordered(): Collection;

    public function find(int $id): Role;

    public function findBySlug(string $slug): ?Role;

    public function findTrashedWithUserCount(int $id): Role;

    public function create(array $data): Role;

    public function update(Role $role, array $data): bool;

    public function delete(Role $role): bool;

    public function restore(Role $role): bool;

    public function forceDelete(Role $role): bool;
}
