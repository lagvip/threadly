<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;


class GhnService
{
    protected string $token;
    protected string $shopId;
    protected string $baseUrl;

    public function __construct()
    {
        $this->token = (string) config('services.ghn.token');
        $this->shopId = (string) config('services.ghn.shop_id');
        $this->baseUrl = (string) config('services.ghn.base_url', 'https://online-gateway.ghn.vn/shiip/public-api');
    }

    public function calculateFee(int $toDistrictId, string $toWardCode, int $weight = 500): int
    {
        $fromDistrictId = (int) config('services.ghn.from_district_id');
        $serviceId = (int) config('services.ghn.service_id', 53321);

        Log::info('GHN fee request payload', [
            'token_exists' => !empty($this->token),
            'shop_id' => $this->shopId,
            'from_district_id' => $fromDistrictId,
            'to_district_id' => $toDistrictId,
            'to_ward_code' => $toWardCode,
            'weight' => max($weight, 100),
            'service_id' => $serviceId,
            'base_url' => $this->baseUrl,
        ]);

        $response = Http::withHeaders([
            'Token' => $this->token,
            'ShopId' => $this->shopId,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/v2/shipping-order/fee', [
            'service_id' => $serviceId,
            'insurance_value' => 0,
            'coupon' => null,
            'from_district_id' => $fromDistrictId,
            'to_district_id' => $toDistrictId,
            'to_ward_code' => $toWardCode,
            'height' => 10,
            'length' => 20,
            'weight' => max($weight, 100),
            'width' => 20,
        ]);

        Log::info('GHN fee response', [
            'status' => $response->status(),
            'body' => $response->json(),
            'raw' => $response->body(),
        ]);

        if (!$response->successful()) {
            return 0;
        }

        $json = $response->json();

        return (int) data_get($json, 'data.total', 0);
    }

    protected function getAvailableServiceId(int $fromDistrictId, int $toDistrictId): ?int
    {
        $response = Http::withHeaders([
            'Token' => $this->token,
            'ShopId' => $this->shopId,
            'Content-Type' => 'application/json',
        ])->post($this->baseUrl . '/v2/shipping-order/available-services', [
            'shop_id' => (int) $this->shopId,
            'from_district' => $fromDistrictId,
            'to_district' => $toDistrictId,
        ]);

        Log::info('GHN available-services response', [
            'status' => $response->status(),
            'body' => $response->json(),
        ]);

        if (!$response->successful()) {
            return null;
        }

        $json = $response->json();

        if ((int) data_get($json, 'code', 500) !== 200) {
            return null;
        }

        $services = data_get($json, 'data', []);

        if (empty($services) || !isset($services[0]['service_id'])) {
            return null;
        }

        return (int) $services[0]['service_id'];
    }
}
