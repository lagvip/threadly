<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Policies\OrderPolicy;
use Tests\TestCase;

class OrderPolicyFeatureTest extends TestCase
{
    public function test_staff_can_manage_orders_but_customer_cannot(): void
    {
        $policy = new OrderPolicy;
        $order = $this->orderForUser(10);

        $this->assertTrue($policy->viewAny($this->staffUser()));
        $this->assertTrue($policy->update($this->staffUser(), $order));
        $this->assertTrue($policy->delete($this->staffUser(), $order));
        $this->assertTrue($policy->manageGhn($this->staffUser(), $order));

        $this->assertFalse($policy->viewAny($this->customerUser(20)));
        $this->assertFalse($policy->update($this->customerUser(20), $order));
        $this->assertFalse($policy->delete($this->customerUser(20), $order));
        $this->assertFalse($policy->manageGhn($this->customerUser(20), $order));
    }

    public function test_customer_can_view_and_request_refund_only_for_own_order(): void
    {
        $policy = new OrderPolicy;
        $owner = $this->customerUser(10);
        $otherCustomer = $this->customerUser(20);
        $order = $this->orderForUser(10);

        $this->assertTrue($policy->view($owner, $order));
        $this->assertTrue($policy->requestRefund($owner, $order));

        $this->assertFalse($policy->view($otherCustomer, $order));
        $this->assertFalse($policy->requestRefund($otherCustomer, $order));
    }

    public function test_force_delete_and_restore_are_admin_only(): void
    {
        $policy = new OrderPolicy;

        $this->assertTrue($policy->restore($this->adminUser()));
        $this->assertTrue($policy->forceDelete($this->adminUser()));

        $this->assertFalse($policy->restore($this->staffUser()));
        $this->assertFalse($policy->forceDelete($this->staffUser()));
        $this->assertFalse($policy->restore($this->customerUser(10)));
        $this->assertFalse($policy->forceDelete($this->customerUser(10)));
    }

    protected function orderForUser(int $userId): Order
    {
        $order = new Order(['user_id' => $userId]);
        $order->id = 100;
        $order->exists = true;

        return $order;
    }

    protected function adminUser(): User
    {
        $user = new class extends User
        {
            public function isAdmin(): bool
            {
                return true;
            }

            public function isStaff(): bool
            {
                return true;
            }
        };
        $user->id = 1;

        return $user;
    }

    protected function staffUser(): User
    {
        $user = new class extends User
        {
            public function isAdmin(): bool
            {
                return false;
            }

            public function isStaff(): bool
            {
                return true;
            }
        };
        $user->id = 2;

        return $user;
    }

    protected function customerUser(int $id): User
    {
        $user = new class extends User
        {
            public function isAdmin(): bool
            {
                return false;
            }

            public function isStaff(): bool
            {
                return false;
            }
        };
        $user->id = $id;

        return $user;
    }
}
