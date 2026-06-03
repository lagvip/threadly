<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\RefundRequest;
use App\Models\User;

class RefundRequestPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->isStaff();
    }

    public function view(User $user, RefundRequest $refundRequest): bool
    {
        return $user->isStaff() || (int) $refundRequest->user_id === (int) $user->id;
    }

    public function createForOrder(User $user, Order $order): bool
    {
        return (int) $order->user_id === (int) $user->id;
    }

    public function approve(User $user, RefundRequest $refundRequest): bool
    {
        return $user->isStaff();
    }

    public function reject(User $user, RefundRequest $refundRequest): bool
    {
        return $user->isStaff();
    }

    public function restock(User $user, RefundRequest $refundRequest): bool
    {
        return $user->isStaff();
    }

    public function forceDelete(User $user): bool
    {
        return $user->isAdmin();
    }
}
