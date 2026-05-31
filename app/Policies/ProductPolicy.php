<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Product $product): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Product $product): bool
    {
        return $user->isStaff();
    }

    public function updateAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Product $product): bool
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

    public function manageVariants(User $user): bool
    {
        return $user->isStaff();
    }
}
