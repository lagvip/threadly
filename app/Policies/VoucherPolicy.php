<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Voucher;

class VoucherPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, Voucher $voucher): bool
    {
        return $user->isStaff();
    }

    public function create(User $user): bool
    {
        return $user->isStaff();
    }

    public function update(User $user, Voucher $voucher): bool
    {
        return $user->isStaff();
    }

    public function delete(User $user, Voucher $voucher): bool
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
