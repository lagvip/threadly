<?php

namespace App\Services\Client\Addresses;

use App\Contracts\Repositories\AddressRepositoryInterface;

class ClientAddressService
{
    public function __construct(protected AddressRepositoryInterface $addresses)
    {
    }

    public function indexData(int $userId): array
    {
        return [
            'addresses' => $this->addresses->forUser($userId),
        ];
    }

    public function create(int $userId, array $data, bool $isDefault): void
    {
        $data['user_id'] = $userId;
        $data['is_default'] = $isDefault;

        if ($isDefault) {
            $this->addresses->unsetDefaultForUser($userId);
        }

        $this->addresses->createForUser($userId, $data);
    }

    public function update(int $userId, int $id, array $data, bool $isDefault): void
    {
        $address = $this->addresses->findForUser($id, $userId);
        $data['is_default'] = $isDefault;

        if ($isDefault) {
            $this->addresses->unsetDefaultForUser($userId, $address->id);
        }

        $address->update($data);
    }

    public function delete(int $userId, int $id): void
    {
        $address = $this->addresses->findForUser($id, $userId);
        $wasDefault = (bool) $address->is_default;

        $address->delete();

        if (!$wasDefault) {
            return;
        }

        $next = $this->addresses->latestForUser($userId);

        if ($next) {
            $next->update(['is_default' => 1]);
        }
    }

    public function setDefault(int $userId, int $id): void
    {
        $address = $this->addresses->findForUser($id, $userId);

        $this->addresses->unsetDefaultForUser($userId);
        $address->update(['is_default' => 1]);
    }
}
