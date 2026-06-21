<?php

namespace App\Services\Client\Account;

use App\Contracts\Repositories\AddressRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Contracts\Repositories\UserRepositoryInterface;
use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ClientAccountService
{
    public function __construct(
        protected AddressRepositoryInterface $addresses,
        protected OrderRepositoryInterface $orders,
        protected UserRepositoryInterface $users,
    ) {}

    public function overviewData(User $user): array
    {
        return [
            'user' => $user,
            'defaultAddress' => $this->defaultAddress($user->id),
            'recentOrders' => $this->orders->recentForUser($user->id),
            'stats' => [
                'total_orders' => $this->orders->countForUser($user->id),
                'pending_orders' => $this->orders->countForUserByStatuses($user->id, [
                    OrderStatus::Pending->value,
                    OrderStatus::Processing->value,
                    OrderStatus::WaitingForCancellation->value,
                ]),
                'delivered_orders' => $this->orders->countForUserByStatus($user->id, OrderStatus::Delivered->value),
                'cancelled_orders' => $this->orders->countForUserByStatus($user->id, OrderStatus::Cancelled->value),
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
        $payload = [
            'name' => $data['name'],
        ];

        $oldAvatarPath = $user->avatar;
        $newAvatarPath = $avatar?->store('users', 'public') ?: null;

        if ($avatar && ! $newAvatarPath) {
            throw new \RuntimeException('Không thể lưu ảnh đại diện.');
        }

        if ($newAvatarPath) {
            $payload['avatar'] = $newAvatarPath;
        }

        try {
            DB::transaction(function () use ($user, $payload, $newAvatarPath, $oldAvatarPath) {
                if (! $this->users->update($user, $payload)) {
                    throw new \RuntimeException('Cập nhật tài khoản không thành công.');
                }

                if ($newAvatarPath) {
                    DB::afterCommit(fn () => $this->deleteLocalAvatarPath($oldAvatarPath));
                }
            });
        } catch (\Throwable $e) {
            $this->deleteLocalAvatarPath($newAvatarPath);

            throw $e;
        }
    }

    protected function defaultAddress(int $userId): ?Address
    {
        return $this->addresses->defaultForUser($userId);
    }

    protected function deleteLocalAvatarPath(?string $path): void
    {
        if (! empty($path) && ! str_starts_with($path, 'http')) {
            Storage::disk('public')->delete($path);
        }
    }
}
