<?php

namespace App\Repositories\Eloquent;

use App\Contracts\Repositories\AddressRepositoryInterface;
use App\Models\Address;
use Illuminate\Support\Collection;

class AddressRepository implements AddressRepositoryInterface
{
    public function forUser(int $userId): Collection
    {
        return Address::where('user_id', $userId)
            ->orderByDesc('is_default')
            ->orderByDesc('id')
            ->get();
    }

    public function defaultForUser(int $userId): ?Address
    {
        $addresses = $this->forUser($userId);

        return $addresses->firstWhere('is_default', 1) ?? $addresses->first();
    }

    public function findForUser(int $addressId, int $userId): Address
    {
        return Address::where('id', $addressId)
            ->where('user_id', $userId)
            ->firstOrFail();
    }

    public function findUsableGhnAddressForOrder(int $userId, ?int $addressId, ?string $fullAddress): ?Address
    {
        if ($addressId) {
            $address = Address::where('id', $addressId)
                ->where('user_id', $userId)
                ->whereNotNull('ghn_district_id')
                ->whereNotNull('ghn_ward_code')
                ->first();

            if ($address) {
                return $address;
            }
        }

        if (!empty($fullAddress)) {
            $address = Address::where('user_id', $userId)
                ->whereRaw(
                    "CONCAT(detailed_address, ', ', ward, ', ', district, ', ', province) = ?",
                    [$fullAddress]
                )
                ->whereNotNull('ghn_district_id')
                ->whereNotNull('ghn_ward_code')
                ->first();

            if ($address) {
                return $address;
            }
        }

        return Address::where('user_id', $userId)
            ->where('is_default', 1)
            ->whereNotNull('ghn_district_id')
            ->whereNotNull('ghn_ward_code')
            ->first();
    }

    public function createForUser(int $userId, array $data): Address
    {
        $data['user_id'] = $userId;

        return Address::create($data);
    }

    public function unsetDefaultForUser(int $userId, ?int $exceptAddressId = null): int
    {
        return Address::where('user_id', $userId)
            ->when($exceptAddressId, fn ($query) => $query->where('id', '!=', $exceptAddressId))
            ->update(['is_default' => 0]);
    }

    public function latestForUser(int $userId): ?Address
    {
        return Address::where('user_id', $userId)->latest('id')->first();
    }

    public function countForUser(int $userId): int
    {
        return Address::where('user_id', $userId)->count();
    }
}
