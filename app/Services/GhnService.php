<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Order;
use App\Models\OrderStatusLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class GhnService
{
    protected string $token;
    protected string $shopId;
    protected string $baseUrl;

    public function __construct()
    {
        $this->token = (string) config('services.ghn.token');
        $this->shopId = (string) config('services.ghn.shop_id');
        $this->baseUrl = rtrim(
            (string) config('services.ghn.base_url', 'https://online-gateway.ghn.vn/shiip/public-api'),
            '/'
        );
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

        $response = Http::withHeaders($this->headers())->post($this->baseUrl . '/v2/shipping-order/fee', [
            'service_id' => $serviceId,
            'insurance_value' => 0,
            'coupon' => null,
            'from_district_id' => $fromDistrictId,
            'to_district_id' => $toDistrictId,
            'to_ward_code' => $toWardCode,
            'height' => (int) config('services.ghn.default_height', 10),
            'length' => (int) config('services.ghn.default_length', 20),
            'weight' => $weight,
            'width' => (int) config('services.ghn.default_width', 20),
        ]);

        $json = $response->json();

        Log::info('GHN fee response', [
            'status' => $response->status(),
            'body' => $json,
        ]);

        if (!$response->successful()) {
            return 0;
        }

        return (int) data_get($json, 'data.total', 0);
    }

    public function createOrder(Order $order): array
    {
        $this->ensureConfigured();

        $order = $this->fillMissingGhnAddressCodes($order);

        $order->loadMissing([
            'details.variant.product',
            'details.variant.color',
            'details.variant.size',
        ]);

        if (!empty($order->ghn_order_code)) {
            throw new RuntimeException('Đơn này đã có mã vận đơn GHN: ' . $order->ghn_order_code);
        }

        if (in_array($order->order_status, [OrderStatus::Delivered->value, OrderStatus::Cancelled->value], true)) {
            throw new RuntimeException('Không thể tạo vận đơn GHN cho đơn đã giao hoặc đã hủy.');
        }

        if ($order->payment_method === Order::PAYMENT_METHOD_VNPAY && $order->payment_status !== Order::PAYMENT_PAID) {
            throw new RuntimeException('Đơn VNPay chưa thanh toán thành công, không thể tạo vận đơn GHN.');
        }

        if (in_array($order->payment_status, [
            Order::PAYMENT_FAILED,
            Order::PAYMENT_CANCELLED,
            Order::PAYMENT_EXPIRED,
        ], true)) {
            throw new RuntimeException('Đơn có trạng thái thanh toán không hợp lệ, không thể tạo vận đơn GHN.');
        }

        if (empty($order->ghn_to_district_id) || empty($order->ghn_to_ward_code)) {
            throw new RuntimeException('Đơn hàng thiếu mã quận/huyện hoặc phường/xã GHN. Đơn cũ cần cập nhật lại địa chỉ trước khi gửi GHN.');
        }

        if ($order->details->isEmpty()) {
            throw new RuntimeException('Đơn hàng không có sản phẩm để tạo vận đơn GHN.');
        }

        $payload = $this->buildCreateOrderPayload($order);

        Log::info('GHN create order payload', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'payload' => $payload,
        ]);

        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/v2/shipping-order/create', $payload);

        $json = $response->json();

        Log::info('GHN create order response', [
            'order_id' => $order->id,
            'status' => $response->status(),
            'body' => $json,
        ]);

        if (!$response->successful() || (int) data_get($json, 'code') !== 200) {
            throw new RuntimeException(data_get($json, 'message') ?: 'GHN tạo vận đơn thất bại.');
        }

        $data = (array) data_get($json, 'data', []);
        $ghnOrderCode = (string) data_get($data, 'order_code');
        $ghnStatus = (string) data_get($data, 'status', 'ready_to_pick');

        if ($ghnOrderCode === '') {
            throw new RuntimeException('GHN không trả về mã vận đơn.');
        }

        $order->update([
            'ghn_order_code' => $ghnOrderCode,
            'ghn_client_order_code' => $payload['client_order_code'],
            'ghn_status' => $ghnStatus,
            'ghn_status_name' => $this->statusName($ghnStatus),
            'ghn_service_id' => data_get($data, 'service_id') ?: ($payload['service_id'] ?: null),
            'ghn_service_type_id' => $payload['service_type_id'],
            'ghn_expected_delivery_time' => $this->parseDateTime(
                data_get($data, 'expected_delivery_time') ?: data_get($data, 'leadtime')
            ),
            'ghn_raw_response' => $json,
            'ghn_synced_at' => now(),
            'order_status' => OrderStatus::Processing->value,
        ]);

        OrderStatusLog::create([
            'order_id' => $order->id,
            'status' => OrderStatus::Processing->value,
            'note' => 'Đã tạo vận đơn GHN: ' . $ghnOrderCode . ' - ' . $this->statusGroup($ghnStatus),
            'changed_by' => auth()->id(),
        ]);

        return $json;
    }

    public function getOrderInfo(string $ghnOrderCode): array
    {
        $this->ensureConfigured(false);

        $response = Http::withHeaders($this->headers(false))
            ->post($this->baseUrl . '/v2/shipping-order/detail', [
                'order_code' => $ghnOrderCode,
            ]);

        $json = $response->json();

        Log::info('GHN order detail response', [
            'ghn_order_code' => $ghnOrderCode,
            'status' => $response->status(),
            'body' => $json,
        ]);

        if (!$response->successful() || (int) data_get($json, 'code') !== 200) {
            throw new RuntimeException(data_get($json, 'message') ?: 'Không lấy được thông tin đơn GHN.');
        }

        return $json;
    }

    public function cancelOrder(string $ghnOrderCode): array
    {
        $this->ensureConfigured();

        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl . '/v2/switch-status/cancel', [
                'order_codes' => [$ghnOrderCode],
            ]);

        $json = $response->json();

        if (!$response->successful() || (int) data_get($json, 'code') !== 200) {
            throw new RuntimeException(data_get($json, 'message') ?: 'GHN hủy vận đơn thất bại.');
        }

        return $json;
    }

    public function printOrderUrl(string $ghnOrderCode, string $paper = 'a5'): string
    {
        $this->ensureConfigured(false);

        $response = Http::withHeaders($this->headers(false))
            ->post($this->baseUrl . '/v2/a5/gen-token', [
                'order_codes' => [$ghnOrderCode],
            ]);

        $json = $response->json();

        if (!$response->successful() || (int) data_get($json, 'code') !== 200) {
            throw new RuntimeException(data_get($json, 'message') ?: 'Không tạo được token in vận đơn GHN.');
        }

        $printToken = (string) data_get($json, 'data.token');

        if ($printToken === '') {
            throw new RuntimeException('GHN không trả về token in vận đơn.');
        }

        $printHost = str_contains($this->baseUrl, 'dev-online-gateway')
            ? 'https://dev-online-gateway.ghn.vn'
            : 'https://online-gateway.ghn.vn';

        $path = match ($paper) {
            '80x80' => '/a5/public-api/print80x80',
            '52x70' => '/a5/public-api/print52x70',
            default => '/a5/public-api/printA5',
        };

        return $printHost . $path . '?token=' . urlencode($printToken);
    }

    public function syncOrderFromGhnInfo(
        Order $order,
        array $ghnInfo,
        ?int $changedBy = null,
        string $notePrefix = 'Đồng bộ GHN'
    ): void {
        $data = (array) data_get($ghnInfo, 'data', []);

        $status = (string) data_get(
            $data,
            'status',
            data_get($ghnInfo, 'Status', data_get($ghnInfo, 'status', ''))
        );

        if ($status === '') {
            return;
        }

        $localStatus = $this->mapStatusToOrderStatus($status);
        $oldOrderStatus = $order->order_status;
        $oldGhnStatus = $order->ghn_status;

        $updates = [
            'ghn_status' => $status,
            'ghn_status_name' => $this->statusName($status),
            'ghn_expected_delivery_time' => $this->parseDateTime(
                data_get($data, 'leadtime') ?: data_get($data, 'expected_delivery_time')
            ),
            'ghn_raw_response' => $ghnInfo,
            'ghn_synced_at' => now(),
        ];

        if ($localStatus && !$this->isLocalTerminalStatus($oldOrderStatus)) {
            $updates['order_status'] = $localStatus;
        }

        if (
            $localStatus === OrderStatus::Delivered->value
            && $order->payment_method === Order::PAYMENT_METHOD_COD
        ) {
            $updates['payment_status'] = Order::PAYMENT_PAID;
            $updates['paid_at'] = $order->paid_at ?: now();
        }

        if (
            $localStatus === OrderStatus::Cancelled->value
            && $order->payment_method === Order::PAYMENT_METHOD_COD
            && $order->payment_status !== Order::PAYMENT_PAID
        ) {
            $updates['payment_status'] = Order::PAYMENT_CANCELLED;
        }

        $order->update($updates);

        if ($oldGhnStatus !== $status || (!empty($updates['order_status']) && $updates['order_status'] !== $oldOrderStatus)) {
            OrderStatusLog::create([
                'order_id' => $order->id,
                'status' => $updates['order_status'] ?? $order->order_status,
                'note' => $notePrefix . ': ' . $this->statusGroup($status) . ' - ' . $this->statusName($status) . ' (' . $status . ')',
                'changed_by' => $changedBy,
            ]);
        }
    }

    public function mapStatusToOrderStatus(string $ghnStatus): ?string
    {
        return match ($ghnStatus) {
            'ready_to_pick',
            'picking',
            'money_collect_picking' => OrderStatus::Processing->value,

            'picked',
            'storing',
            'transporting',
            'sorting',
            'delivering',
            'money_collect_delivering',
            'delivery_fail',
            'waiting_to_return',
            'return',
            'return_transporting',
            'return_sorting',
            'returning',
            'return_fail' => OrderStatus::Shipped->value,

            'delivered' => OrderStatus::Delivered->value,

            'cancel',
            'returned',
            'exception',
            'damage',
            'lost' => OrderStatus::Cancelled->value,

            default => null,
        };
    }

    public function statusGroup(string $ghnStatus): string
    {
        return match ($ghnStatus) {
            'ready_to_pick',
            'picking',
            'money_collect_picking' => 'Chờ bàn giao',

            'picked',
            'storing',
            'transporting',
            'sorting',
            'delivering',
            'money_collect_delivering' => 'Đã bàn giao - Đang giao',

            'delivery_fail' => 'Chờ xác nhận giao lại',

            'waiting_to_return',
            'return',
            'return_transporting',
            'return_sorting',
            'returning',
            'return_fail',
            'returned' => 'Đã bàn giao - đang hoàn hàng',

            'delivered' => 'Hoàn tất',

            'cancel' => 'Đơn hủy',

            'exception',
            'damage',
            'lost' => 'Hàng thất lạc - hư hỏng',

            default => 'Không xác định',
        };
    }

    public function statusGroupBadge(string $ghnStatus): string
    {
        return match ($ghnStatus) {
            'ready_to_pick',
            'picking',
            'money_collect_picking' => 'bg-primary',

            'picked',
            'storing',
            'transporting',
            'sorting',
            'delivering',
            'money_collect_delivering' => 'bg-info',

            'delivery_fail' => 'bg-warning text-dark',

            'waiting_to_return',
            'return',
            'return_transporting',
            'return_sorting',
            'returning',
            'return_fail',
            'returned' => 'bg-secondary',

            'delivered' => 'bg-success',

            'cancel' => 'bg-danger',

            'exception',
            'damage',
            'lost' => 'bg-dark',

            default => 'bg-light text-dark',
        };
    }

    public function statusName(string $ghnStatus): string
    {
        return match ($ghnStatus) {
            'ready_to_pick' => 'Mới tạo đơn hàng / Chờ lấy hàng',
            'picking' => 'Nhân viên đang lấy hàng',
            'money_collect_picking' => 'Nhân viên đang thu tiền người gửi',
            'picked' => 'Nhân viên đã lấy hàng',
            'storing' => 'Hàng đang ở kho GHN',
            'transporting' => 'Đang luân chuyển hàng',
            'sorting' => 'Đang phân loại hàng hóa',
            'delivering' => 'Nhân viên đang giao cho người nhận',
            'money_collect_delivering' => 'Nhân viên đang thu tiền người nhận',
            'delivered' => 'Đã giao hàng thành công',
            'delivery_fail' => 'Giao hàng thất bại',
            'waiting_to_return' => 'Đang đợi trả hàng về người gửi',
            'return' => 'Trả hàng',
            'return_transporting' => 'Đang luân chuyển hàng trả',
            'return_sorting' => 'Đang phân loại hàng trả',
            'returning' => 'Nhân viên đang đi trả hàng',
            'return_fail' => 'Trả hàng thất bại',
            'returned' => 'Trả hàng thành công',
            'cancel' => 'Đã hủy đơn hàng',
            'exception' => 'Đơn hàng ngoại lệ',
            'damage' => 'Hàng bị hư hỏng',
            'lost' => 'Hàng bị mất',
            default => $ghnStatus,
        };
    }

    protected function buildCreateOrderPayload(Order $order): array
    {
        $fromDistrictId = (int) config('services.ghn.from_district_id');
        $toDistrictId = (int) $order->ghn_to_district_id;
        $serviceId = $this->getAvailableServiceId($fromDistrictId, $toDistrictId) ?: 0;
        $package = $this->buildPackageInfo($order);
        $clientOrderCode = $order->ghn_client_order_code ?: $this->buildClientOrderCode($order);

        $codAmount = $order->payment_method === Order::PAYMENT_METHOD_COD
            ? (int) round((float) $order->total_price)
            : 0;

        return [
            'payment_type_id' => (int) config('services.ghn.payment_type_id', 1),
            'note' => (string) ($order->customer_note ?: 'Đơn hàng ' . $order->order_code),
            'required_note' => (string) config('services.ghn.required_note', 'KHONGCHOXEMHANG'),

            'from_name' => (string) config('services.ghn.from_name'),
            'from_phone' => (string) config('services.ghn.from_phone'),
            'from_address' => (string) config('services.ghn.from_address'),
            'from_ward_name' => (string) config('services.ghn.from_ward_name'),
            'from_district_name' => (string) config('services.ghn.from_district_name'),
            'from_province_name' => (string) config('services.ghn.from_province_name'),

            'return_name' => (string) config('services.ghn.from_name'),
            'return_phone' => (string) config('services.ghn.from_phone'),
            'return_address' => (string) config('services.ghn.from_address'),
            'return_ward_code' => (string) config('services.ghn.from_ward_code'),
            'return_district_id' => $fromDistrictId,

            'client_order_code' => $clientOrderCode,
            'to_name' => (string) $order->name,
            'to_phone' => (string) $order->phone,
            'to_address' => (string) $order->address,
            'to_ward_code' => (string) $order->ghn_to_ward_code,
            'to_district_id' => $toDistrictId,

            'cod_amount' => $codAmount,
            'content' => 'Đơn hàng ' . $order->order_code,
            'weight' => $package['weight'],
            'length' => $package['length'],
            'width' => $package['width'],
            'height' => $package['height'],
            'insurance_value' => min((int) round((float) $order->total_price), 5000000),
            'service_id' => $serviceId,
            'service_type_id' => (int) config('services.ghn.service_type_id', 2),
            'coupon' => null,
            'items' => $package['items'],
        ];
    }

    protected function buildPackageInfo(Order $order): array
    {
        $items = [];
        $totalWeight = 0;
        $maxLength = 1;
        $maxWidth = 1;
        $totalHeight = 0;

        foreach ($order->details as $detail) {
            $product = $detail->variant?->product;
            $quantity = max((int) $detail->quantity, 1);

            $weight = max((int) ($product->weight ?? config('services.ghn.default_weight', 500)), 1);
            $length = max((int) ($product->length ?? config('services.ghn.default_length', 20)), 1);
            $width = max((int) ($product->width ?? config('services.ghn.default_width', 20)), 1);
            $height = max((int) ($product->height ?? config('services.ghn.default_height', 10)), 1);

            $totalWeight += $weight * $quantity;
            $maxLength = max($maxLength, $length);
            $maxWidth = max($maxWidth, $width);
            $totalHeight += $height * $quantity;

            $items[] = [
                'name' => Str::limit((string) ($detail->product_name ?: $product?->name ?: 'Sản phẩm'), 100, ''),
                'code' => (string) ($detail->variant_id ?: $detail->product_id ?: $detail->id),
                'quantity' => $quantity,
                'price' => (int) round((float) $detail->unit_price),
                'length' => $length,
                'width' => $width,
                'height' => $height,
                'weight' => $weight,
                'category' => [
                    'level1' => 'Thời trang',
                ],
            ];
        }

        return [
            'weight' => max($totalWeight, 100),
            'length' => min(max($maxLength, 1), 150),
            'width' => min(max($maxWidth, 1), 150),
            'height' => min(max($totalHeight, 1), 150),
            'items' => $items,
        ];
    }

    protected function fillMissingGhnAddressCodes(Order $order): Order
    {
        if (!empty($order->ghn_to_district_id) && !empty($order->ghn_to_ward_code)) {
            return $order;
        }

        $address = null;

        if (!empty($order->shipping_address_id)) {
            $address = Address::where('id', $order->shipping_address_id)
                ->where('user_id', $order->user_id)
                ->whereNotNull('ghn_district_id')
                ->whereNotNull('ghn_ward_code')
                ->first();
        }

        if (!$address && !empty($order->address)) {
            $address = Address::where('user_id', $order->user_id)
                ->whereRaw(
                    "CONCAT(detailed_address, ', ', ward, ', ', district, ', ', province) = ?",
                    [$order->address]
                )
                ->whereNotNull('ghn_district_id')
                ->whereNotNull('ghn_ward_code')
                ->first();
        }

        if (!$address) {
            $address = Address::where('user_id', $order->user_id)
                ->where('is_default', 1)
                ->whereNotNull('ghn_district_id')
                ->whereNotNull('ghn_ward_code')
                ->first();
        }

        if (!$address) {
            return $order;
        }

        $order->forceFill([
            'shipping_address_id' => $address->id,
            'ghn_to_province_id' => $address->ghn_province_id,
            'ghn_to_district_id' => $address->ghn_district_id,
            'ghn_to_ward_code' => $address->ghn_ward_code,
        ])->save();

        return $order->fresh();
    }

    protected function getAvailableServiceId(int $fromDistrictId, int $toDistrictId): ?int
    {
        if ($fromDistrictId <= 0 || $toDistrictId <= 0 || empty($this->token) || empty($this->shopId)) {
            return null;
        }

        $response = Http::withHeaders($this->headers(false))
            ->post($this->baseUrl . '/v2/shipping-order/available-services', [
                'shop_id' => (int) $this->shopId,
                'from_district' => $fromDistrictId,
                'to_district' => $toDistrictId,
            ]);

        $json = $response->json();

        Log::info('GHN available-services response', [
            'status' => $response->status(),
            'body' => $json,
        ]);

        if (!$response->successful() || (int) data_get($json, 'code', 500) !== 200) {
            return null;
        }

        foreach ((array) data_get($json, 'data', []) as $service) {
            $serviceId = (int) ($service['service_id'] ?? 0);

            if ($serviceId > 0) {
                return $serviceId;
            }
        }

        return null;
    }

    protected function headers(bool $withShopId = true): array
    {
        $headers = [
            'Token' => $this->token,
            'Content-Type' => 'application/json',
        ];

        if ($withShopId) {
            $headers['ShopId'] = $this->shopId;
        }

        return $headers;
    }

    protected function ensureConfigured(bool $requireShopId = true): void
    {
        if ($this->token === '') {
            throw new RuntimeException('Thiếu GHN_TOKEN.');
        }

        if ($requireShopId && $this->shopId === '') {
            throw new RuntimeException('Thiếu GHN_SHOP_ID.');
        }

        foreach ([
            'from_district_id' => config('services.ghn.from_district_id'),
            'from_ward_code' => config('services.ghn.from_ward_code'),
            'from_name' => config('services.ghn.from_name'),
            'from_phone' => config('services.ghn.from_phone'),
            'from_address' => config('services.ghn.from_address'),
            'from_ward_name' => config('services.ghn.from_ward_name'),
            'from_district_name' => config('services.ghn.from_district_name'),
            'from_province_name' => config('services.ghn.from_province_name'),
        ] as $key => $value) {
            if ($value === null || $value === '') {
                throw new RuntimeException('Thiếu cấu hình GHN: GHN_' . strtoupper($key));
            }
        }
    }

    protected function buildClientOrderCode(Order $order): string
    {
        return 'LOCAL-' . $order->order_code . '-' . $order->id;
    }

    protected function parseDateTime($value): mixed
    {
        if (empty($value)) {
            return null;
        }

        try {
            return \Carbon\Carbon::parse($value);
        } catch (\Throwable) {
            return null;
        }
    }

    protected function isLocalTerminalStatus(?string $status): bool
    {
        return in_array($status, [
            OrderStatus::Delivered->value,
            OrderStatus::Cancelled->value,
        ], true);
    }
}
