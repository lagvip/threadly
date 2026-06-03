<?php

namespace App\Services\Checkout;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class GhnLocationService
{
    public function provinces()
    {
        return Http::withHeaders($this->headers())
            ->get($this->baseUrl() . '/master-data/province');
    }

    public function districts(int $provinceId)
    {
        return Http::withHeaders($this->headers())
            ->post($this->baseUrl() . '/master-data/district', [
                'province_id' => $provinceId,
            ]);
    }

    public function wards(int $districtId)
    {
        return Http::withHeaders($this->headers())
            ->post($this->baseUrl() . '/master-data/ward', [
                'district_id' => $districtId,
            ]);
    }

    public function provinceData(): array
    {
        $response = $this->provinces();

        if (!$response->successful()) {
            throw new RuntimeException('Không lấy được danh sách tỉnh/thành.');
        }

        return $response->json('data', []);
    }

    public function districtData(int $provinceId): array
    {
        $response = $this->districts($provinceId);

        if (!$response->successful()) {
            throw new RuntimeException('Không lấy được danh sách quận/huyện.');
        }

        return $response->json('data', []);
    }

    public function wardData(int $districtId): array
    {
        $response = $this->wards($districtId);

        if (!$response->successful()) {
            throw new RuntimeException('Không lấy được danh sách phường/xã.');
        }

        return $response->json('data', []);
    }

    protected function headers(): array
    {
        return [
            'Token' => config('services.ghn.token'),
        ];
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('services.ghn.base_url'), '/');
    }
}
