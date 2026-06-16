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

    public function update(Role $role, array $data): bool
    {
        return $role->update($data);
    }

    public function delete(Role $role): bool
    {
        return (bool) $role->delete();
    }

    public function restore(Role $role): bool
    {
        return (bool) $role->restore();
    }

    public function forceDelete(Role $role): bool
    {
        return (bool) $role->forceDelete();
    }
}
