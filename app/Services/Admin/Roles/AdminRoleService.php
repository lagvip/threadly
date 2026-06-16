<?php

namespace App\Services\Admin\Roles;

use App\Contracts\Repositories\RoleRepositoryInterface;
use App\Models\Role;
use Illuminate\Support\Str;
use RuntimeException;

class AdminRoleService
{
    public function __construct(protected RoleRepositoryInterface $roles) {}

    public function indexData(): array
    {
        return [
            'roles' => $this->roles->queryWithUserCount()->latest()->paginate(10),
        ];
    }

    public function trashData(): array
    {
        return [
            'roles' => $this->roles->trashedQueryWithUserCount()
                ->latest()
                ->paginate(10),
        ];
    }

    public function find(int $id): Role
    {
        return $this->roles->find($id);
    }

    public function create(array $data): void
    {
        $this->roles->create([
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'permissions' => $data['permissions'] ?? null,
        ]);
    }

    public function update(int $id, array $data): void
    {
        $role = $this->roles->find($id);

        $this->roles->update($role, [
            'name' => $data['name'],
            'slug' => $data['slug'] ?: Str::slug($data['name']),
            'permissions' => $data['permissions'] ?? $role->permissions,
        ]);
    }

    public function softDelete(int $id): void
    {
        $role = $this->roles->queryWithUserCount()->findOrFail($id);

        if ($role->users_count > 0) {
            throw new RuntimeException('Role này vẫn còn user, không thể xóa.');
        }

        $this->roles->delete($role);
    }

    public function restore(int $id): void
    {
        $this->roles->restore($this->roles->findTrashedWithUserCount($id));
    }

    public function forceDelete(int $id): void
    {
        $role = $this->roles->findTrashedWithUserCount($id);

        if ($role->users_count > 0) {
            throw new RuntimeException('Role này vẫn còn user, không thể xóa vĩnh viễn.');
        }

        $this->roles->forceDelete($role);
    }
}
