<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Order $order): bool
    {
        return $user->isStaff() || (int) $order->user_id === (int) $user->id;
    }

    public function update(User $user, Order $order): bool
    {
        return $user->isStaff();
    }

    public function updateAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Order $order): bool
    {
        return $user->isStaff();
    }

    public function restore(User $user): bool
    {
        return $user->isAdmin();
    }

    public function forceDelete(User $user): bool
    {
        return $user->isAdmin();
    }

    public function manageGhn(User $user, Order $order): bool
    {
        return $user->isStaff();
    }

    public function requestRefund(User $user, Order $order): bool
    {
        return (int) $order->user_id === (int) $user->id;
    }
}
