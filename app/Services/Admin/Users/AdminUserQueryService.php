<?php

namespace App\Services\Admin\Users;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;

class AdminUserQueryService
{
    public function __construct(
        protected UserRepositoryInterface $users,
        protected RoleRepositoryInterface $roles,
    ) {
    }

    public function indexData(array $filters): array
    {
        $role = $filters['role'] ?? null;
        $keyword = $filters['keyword'] ?? null;

        $users = $this->users->adminIndexQuery()
            ->when($role, function ($q) use ($role) {
                $q->whereHas('roles', fn ($r) => $r->where('slug', $role));
            })
            ->when($keyword, function ($q) use ($keyword) {
                $q->where(function ($qq) use ($keyword) {
                    $qq->where('name', 'LIKE', "%{$keyword}%")
                        ->orWhere('email', 'LIKE', "%{$keyword}%");
                });
            })
            ->orderByDesc('id')
            ->paginate(10)
            ->appends(array_filter([
                'role' => $role,
                'keyword' => $keyword,
            ]));

        return [
            'users' => $users,
            'role' => $role,
            'keyword' => $keyword,
            'roles' => $this->roles->ordered(),
        ];
    }

    public function trashData(): array
    {
        return [
            'users' => $this->users->trashedForAdmin()
                ->orderByDesc('deleted_at')
                ->paginate(10),
        ];
    }

    public function createData(): array
    {
        $hasAdmin = $this->countAdminUsers() >= 1;
        $roles = $this->roles->ordered();

        if ($hasAdmin) {
            $roles = $roles->reject(fn ($role) => $role->slug === 'admin');
        }

        return compact('roles', 'hasAdmin');
    }

    public function editData(int $userId): array
    {
        $user = $this->users->findWithRoles($userId);
        $roles = $this->roles->ordered();
        $hasAdmin = $this->users->adminExistsExcept($user->id);

        if ($this->countAdminUsers() >= 1 && !$user->hasRole('admin')) {
            $roles = $roles->reject(fn ($role) => $role->slug === 'admin');
        }

        return compact('user', 'roles', 'hasAdmin');
    }

    public function detailData(int $userId): array
    {
        return [
            'user' => $this->users->findWithRoles($userId),
        ];
    }

    public function countAdminUsers(): int
    {
        return $this->users->countAdmins();
    }
}
