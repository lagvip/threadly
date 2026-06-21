<?php

namespace App\Contracts\Repositories;

use App\Models\Role;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

interface RoleRepositoryInterface
{
    public function paginateForAdmin(int $perPage = 10): LengthAwarePaginator;

    public function paginateTrashedForAdmin(int $perPage = 10): LengthAwarePaginator;

    public function ordered(): Collection;

    public function find(int $id): Role;

    public function findWithUserCount(int $id): Role;

    public function findBySlug(string $slug): ?Role;

    public function lockBySlug(string $slug): ?Role;

    public function findTrashedWithUserCount(int $id): Role;

    public function create(array $data): Role;

    public function update(Role $role, array $data): bool;

    public function delete(Role $role): bool;

    public function restore(Role $role): bool;

    public function forceDelete(Role $role): bool;
}
