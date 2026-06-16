<?php

namespace App\Policies;

use App\Models\User;

class StaffResourcePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, $model = null): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, $model = null): bool
    {
        return $user->isStaff();
    }

    public function updateAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, $model = null): bool
    {
        return $user->isStaff();
    }

    public function deleteAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function restore(User $user): bool
    {
        return $user->isStaff();
    }

    public function forceDelete(User $user): bool
    {
        return $user->isAdmin();
    }
}
