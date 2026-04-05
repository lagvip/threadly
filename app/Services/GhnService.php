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
        $weight = max($weight, 100);

        if (empty($this->token) || empty($this->shopId) || $fromDistrictId <= 0) {
            Log::error('GHN config missing for fee calculation', [
                'token_exists' => !empty($this->token),
                'shop_id' => $this->shopId,
                'from_district_id' => $fromDistrictId,
            ]);

            return 0;
        }

        $serviceId = $this->getAvailableServiceId($fromDistrictId, $toDistrictId);

        if (!$serviceId) {
            $fallbackServiceId = (int) config('services.ghn.service_id', 0);

            if ($fallbackServiceId > 0) {
                Log::warning('GHN available service not found, fallback to configured service_id', [
                    'fallback_service_id' => $fallbackServiceId,
                    'from_district_id' => $fromDistrictId,
                    'to_district_id' => $toDistrictId,
                    'to_ward_code' => $toWardCode,
                ]);

                $serviceId = $fallbackServiceId;
            }
        }

        if (!$serviceId) {
            Log::error('GHN cannot calculate fee because no valid service_id was resolved', [
                'from_district_id' => $fromDistrictId,
                'to_district_id' => $toDistrictId,
                'to_ward_code' => $toWardCode,
                'weight' => $weight,
            ]);

            return 0;
        }

        Log::info('GHN fee request payload', [
            'token_exists' => !empty($this->token),
            'shop_id' => $this->shopId,
            'from_district_id' => $fromDistrictId,
            'to_district_id' => $toDistrictId,
            'to_ward_code' => $toWardCode,
            'weight' => $weight,
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
            'weight' => $weight,
            'width' => 20,
        ]);

        $json = $response->json();

        Log::info('GHN fee response', [
            'status' => $response->status(),
            'body' => $json,
            'raw' => $response->body(),
        ]);

        if (!$response->successful()) {
            Log::error('GHN fee request failed', [
                'status' => $response->status(),
                'body' => $json,
                'to_district_id' => $toDistrictId,
                'to_ward_code' => $toWardCode,
                'service_id' => $serviceId,
            ]);

            return 0;
        }

        $total = (int) data_get($json, 'data.total', 0);

        if ($total <= 0) {
            Log::warning('GHN fee returned empty or zero total', [
                'body' => $json,
                'to_district_id' => $toDistrictId,
                'to_ward_code' => $toWardCode,
                'service_id' => $serviceId,
            ]);
        }

        return $total;
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

        $json = $response->json();

        Log::info('GHN available-services response', [
            'status' => $response->status(),
            'body' => $json,
        ]);

        if (!$response->successful()) {
            return null;
        }

        if ((int) data_get($json, 'code', 500) !== 200) {
            return null;
        }

        $services = data_get($json, 'data', []);

        if (empty($services)) {
            return null;
        }

        foreach ($services as $service) {
            $serviceId = (int) ($service['service_id'] ?? 0);

            if ($serviceId > 0) {
                return $serviceId;
            }
        }

        return null;
    }
}
