<?php

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Review $review): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Review $review): bool
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
