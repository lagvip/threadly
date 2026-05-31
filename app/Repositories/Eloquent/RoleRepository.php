<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Models\Role;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RoleRepository implements RoleRepositoryInterface
{
    public function queryWithUserCount(): Builder
    {
        return Role::withCount(['usersWithTrashed as users_count']);
    }

    public function trashedQueryWithUserCount(): Builder
    {
        return Role::onlyTrashed()->withCount(['usersWithTrashed as users_count']);
    }

    public function ordered(): Collection
    {
        return Role::orderBy('name')->get();
    }

    public function find(int $id): Role
    {
        return Role::findOrFail($id);
    }

    public function findBySlug(string $slug): ?Role
    {
        return Role::where('slug', $slug)->first();
    }

    public function findTrashedWithUserCount(int $id): Role
    {
        return Role::onlyTrashed()
            ->withCount(['usersWithTrashed as users_count'])
            ->findOrFail($id);
    }

    public function create(array $data): Role
    {
        return Role::create($data);
    }
}
