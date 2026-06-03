<?php

namespace App\Services\Client\Account;

use App\Contracts\Repositories\AddressRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class ClientAccountService
{
    public function __construct(
        protected AddressRepositoryInterface $addresses,
        protected OrderRepositoryInterface $orders,
    ) {
    }

    public function overviewData(User $user): array
    {
        return [
            'user' => $user,
            'defaultAddress' => $this->defaultAddress($user->id),
            'recentOrders' => $this->orders->recentForUser($user->id),
            'stats' => [
                'total_orders' => $this->orders->countForUser($user->id),
                'pending_orders' => $this->orders->countForUserByStatuses($user->id, ['pending', 'processing', 'waiting_for_cancellation']),
                'delivered_orders' => $this->orders->countForUserByStatus($user->id, 'delivered'),
                'cancelled_orders' => $this->orders->countForUserByStatus($user->id, 'cancelled'),
            ],
        ];
    }

    public function detailData(User $user): array
    {
        return [
            'user' => $user,
            'defaultAddress' => $this->defaultAddress($user->id),
            'addressCount' => $this->addresses->countForUser($user->id),
        ];
    }

    public function update(User $user, array $data, ?UploadedFile $avatar = null): void
    {
        $user->name = $data['name'];

        if ($avatar) {
            $this->deleteLocalAvatar($user);
            $user->avatar = $avatar->store('users', 'public');
        }

        $user->save();
    }

    protected function defaultAddress(int $userId): ?Address
    {
        return $this->addresses->defaultForUser($userId);
    }

    protected function deleteLocalAvatar(User $user): void
    {
        if (!empty($user->avatar) && !str_starts_with($user->avatar, 'http')) {
            Storage::disk('public')->delete($user->avatar);
        }
    }
}
