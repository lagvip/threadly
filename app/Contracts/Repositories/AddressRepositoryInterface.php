<?php

namespace App\Contracts\Repositories;

use App\Models\Address;
use Illuminate\Support\Collection;

interface AddressRepositoryInterface
{
    public function forUser(int $userId): Collection;

    public function defaultForUser(int $userId): ?Address;

    public function findForUser(int $addressId, int $userId): Address;

    public function findUsableGhnAddressForOrder(int $userId, ?int $addressId, ?string $fullAddress): ?Address;

    public function createForUser(int $userId, array $data): Address;

    public function unsetDefaultForUser(int $userId, ?int $exceptAddressId = null): int;

    public function latestForUser(int $userId): ?Address;

    public function countForUser(int $userId): int;
}
