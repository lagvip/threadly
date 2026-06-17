<?php

namespace App\Services\Integrations\Ghn;

use App\Contracts\Repositories\AddressRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Enums\GhnOrderStatus;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentMethod;
use App\Events\Sales\OrderStatusChanged;
use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use RuntimeException;

class GhnService
{
    protected string $token;

    protected string $shopId;

    protected string $baseUrl;

    public function __construct(
        protected AddressRepositoryInterface $addresses,
        protected OrderRepositoryInterface $orders,
    ) {
        // Lấy cấu hình GHN từ config/services.php, thường lấy từ file .env.
        $this->token = (string) config('services.ghn.token');
        $this->shopId = (string) config('services.ghn.shop_id');
        $this->baseUrl = rtrim(
            (string) config('services.ghn.base_url', 'https://online-gateway.ghn.vn/shiip/public-api'),
            '/'
        );
    }

    public function calculateFee(int $toDistrictId, string $toWardCode, int $weight = 500): int
    {
        // Tính phí ship GHN theo quận/huyện, phường/xã và cân nặng.
        $fromDistrictId = (int) config('services.ghn.from_district_id');
        $weight = max($weight, 100);

        // Nếu thiếu cấu hình GHN thì không gọi API, trả phí ship 0.
        if (empty($this->token) || empty($this->shopId) || $fromDistrictId <= 0) {
            Log::error('GHN config missing for fee calculation', [
                'token_exists' => ! empty($this->token),
                'shop_id' => $this->shopId,
                'from_district_id' => $fromDistrictId,
            ]);

            return 0;
        }

        // Lấy service_id hợp lệ từ GHN theo tuyến gửi - nhận.
        $serviceId = $this->getAvailableServiceId($fromDistrictId, $toDistrictId);

        // Nếu GHN không trả service_id thì dùng service_id cấu hình sẵn nếu có.
        if (! $serviceId) {
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

        // Không có service_id thì không tính được phí ship.
        if (! $serviceId) {
            Log::error('GHN cannot calculate fee because no valid service_id was resolved', [
                'from_district_id' => $fromDistrictId,
                'to_district_id' => $toDistrictId,
                'to_ward_code' => $toWardCode,
                'weight' => $weight,
            ]);

            return 0;
        }

        // Gọi API GHN để tính phí vận chuyển.
        $response = Http::withHeaders($this->headers())->post($this->baseUrl.'/v2/shipping-order/fee', [
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

        // Log response để debug khi GHN lỗi hoặc phí ship trả về sai.
        Log::info('GHN fee response', [
            'status' => $response->status(),
            'body' => $json,
        ]);

        // Nếu API lỗi thì trả 0 để không làm vỡ checkout.
        if (! $response->successful()) {
            return 0;
        }

        // GHN trả phí ship ở data.total.
        return (int) data_get($json, 'data.total', 0);
    }

    public function createOrder(Order $order): array
    {
        // Kiểm tra đã cấu hình đủ GHN chưa.
        $this->ensureConfigured();

        // Nếu đơn cũ thiếu mã địa chỉ GHN thì thử tự lấy lại từ bảng addresses.
        $order = $this->fillMissingGhnAddressCodes($order);

        // Load chi tiết đơn để gửi danh sách sản phẩm sang GHN.
        $order->loadMissing([
            'details.variant.product',
            'details.variant.color',
            'details.variant.size',
        ]);

        // Một đơn chỉ được tạo một mã vận đơn GHN.
        if (! empty($order->ghn_order_code)) {
            throw new RuntimeException('Đơn này đã có mã vận đơn GHN: '.$order->ghn_order_code);
        }

        // Không tạo vận đơn cho đơn đã giao hoặc đã hủy.
        if (in_array($order->order_status, [OrderStatus::Delivered->value, OrderStatus::Cancelled->value], true)) {
            throw new RuntimeException('Không thể tạo vận đơn GHN cho đơn đã giao hoặc đã hủy.');
        }

        // VNPay phải paid mới được tạo vận đơn.
        if ($order->payment_method === PaymentMethod::Vnpay->value && $order->payment_status !== OrderPaymentStatus::Paid->value) {
            throw new RuntimeException('Đơn VNPay chưa thanh toán thành công, không thể tạo vận đơn GHN.');
        }

        // Đơn thanh toán lỗi/hủy/hết hạn thì không gửi GHN.
        if (in_array($order->payment_status, [
            OrderPaymentStatus::Failed->value,
            OrderPaymentStatus::Cancelled->value,
            OrderPaymentStatus::Expired->value,
        ], true)) {
            throw new RuntimeException('Đơn có trạng thái thanh toán không hợp lệ, không thể tạo vận đơn GHN.');
        }

        // Địa chỉ nhận bắt buộc có mã quận/huyện và phường/xã GHN.
        if (empty($order->ghn_to_district_id) || empty($order->ghn_to_ward_code)) {
            throw new RuntimeException('Đơn hàng thiếu mã quận/huyện hoặc phường/xã GHN. Đơn cũ cần cập nhật lại địa chỉ trước khi gửi GHN.');
        }

        // Không có sản phẩm thì không tạo vận đơn.
        if ($order->details->isEmpty()) {
            throw new RuntimeException('Đơn hàng không có sản phẩm để tạo vận đơn GHN.');
        }

        // Tạo payload theo format GHN yêu cầu.
        $payload = $this->buildCreateOrderPayload($order);

        Log::info('GHN create order payload', [
            'order_id' => $order->id,
            'order_code' => $order->order_code,
            'payload' => $payload,
        ]);

        // Gọi API GHN tạo vận đơn.
        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl.'/v2/shipping-order/create', $payload);

        $json = $response->json();

        Log::info('GHN create order response', [
            'order_id' => $order->id,
            'status' => $response->status(),
            'body' => $json,
        ]);

        // Nếu GHN trả lỗi thì ném exception để controller báo admin.
        if (! $response->successful() || (int) data_get($json, 'code') !== 200) {
            throw new RuntimeException(data_get($json, 'message') ?: 'GHN tạo vận đơn thất bại.');
        }

        $data = (array) data_get($json, 'data', []);
        $ghnOrderCode = (string) data_get($data, 'order_code');
        $ghnStatus = (string) data_get($data, 'status', GhnOrderStatus::ReadyToPick->value);

        // Tạo vận đơn thành công thì bắt buộc phải có order_code từ GHN.
        if ($ghnOrderCode === '') {
            throw new RuntimeException('GHN không trả về mã vận đơn.');
        }

        // Lưu thông tin vận đơn GHN vào order local.
        $this->orders->update($order, [
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

        // Ghi log lịch sử đơn sau khi tạo vận đơn.
        OrderStatusChanged::dispatch(
            (int) $order->id,
            OrderStatus::Processing->value,
            'Đã tạo vận đơn GHN: '.$ghnOrderCode.' - '.$this->statusGroup($ghnStatus),
            auth()->id()
        );

        return $json;
    }

    public function getOrderInfo(string $ghnOrderCode): array
    {
        // Lấy chi tiết vận đơn từ GHN để đồng bộ trạng thái.
        $this->ensureConfigured(false);

        $response = Http::withHeaders($this->headers(false))
            ->post($this->baseUrl.'/v2/shipping-order/detail', [
                'order_code' => $ghnOrderCode,
            ]);

        $json = $response->json();

        Log::info('GHN order detail response', [
            'ghn_order_code' => $ghnOrderCode,
            'status' => $response->status(),
            'body' => $json,
        ]);

        if (! $response->successful() || (int) data_get($json, 'code') !== 200) {
            throw new RuntimeException(data_get($json, 'message') ?: 'Không lấy được thông tin đơn GHN.');
        }

        return $json;
    }

    public function cancelOrder(string $ghnOrderCode): array
    {
        // Gửi yêu cầu hủy vận đơn sang GHN.
        $this->ensureConfigured();

        $response = Http::withHeaders($this->headers())
            ->post($this->baseUrl.'/v2/switch-status/cancel', [
                'order_codes' => [$ghnOrderCode],
            ]);

        $json = $response->json();

        if (! $response->successful() || (int) data_get($json, 'code') !== 200) {
            throw new RuntimeException(data_get($json, 'message') ?: 'GHN hủy vận đơn thất bại.');
        }

        return $json;
    }

    public function printOrderUrl(string $ghnOrderCode, string $paper = 'a5'): string
    {
        // Lấy token in vận đơn từ GHN.
        $this->ensureConfigured(false);

        $response = Http::withHeaders($this->headers(false))
            ->post($this->baseUrl.'/v2/a5/gen-token', [
                'order_codes' => [$ghnOrderCode],
            ]);

        $json = $response->json();

        if (! $response->successful() || (int) data_get($json, 'code') !== 200) {
            throw new RuntimeException(data_get($json, 'message') ?: 'Không tạo được token in vận đơn GHN.');
        }

        $printToken = (string) data_get($json, 'data.token');

        if ($printToken === '') {
            throw new RuntimeException('GHN không trả về token in vận đơn.');
        }

        // Chọn host in theo môi trường dev hoặc production.
        $printHost = str_contains($this->baseUrl, 'dev-online-gateway')
            ? 'https://dev-online-gateway.ghn.vn'
            : 'https://online-gateway.ghn.vn';

        // Chọn khổ giấy in vận đơn.
        $path = match ($paper) {
            '80x80' => '/a5/public-api/print80x80',
            '52x70' => '/a5/public-api/print52x70',
            default => '/a5/public-api/printA5',
        };

        return $printHost.$path.'?token='.urlencode($printToken);
    }

    public function syncOrderFromGhnInfo(
        Order $order,
        array $ghnInfo,
        ?int $changedBy = null,
        string $notePrefix = 'Đồng bộ GHN'
    ): void {
        // Đồng bộ response GHN về đơn hàng local.
        $data = (array) data_get($ghnInfo, 'data', []);

        // Lấy status từ nhiều format khác nhau vì API detail và webhook có thể khác nhau.
        $status = (string) data_get(
            $data,
            'status',
            data_get($ghnInfo, 'Status', data_get($ghnInfo, 'status', ''))
        );

        // Không có status thì không cập nhật gì.
        if ($status === '') {
            return;
        }

        // Map trạng thái GHN sang trạng thái đơn hàng trong hệ thống.
        $localStatus = $this->mapStatusToOrderStatus($status);
        $oldOrderStatus = $order->order_status;
        $oldGhnStatus = $order->ghn_status;

        // Chuẩn bị dữ liệu GHN cần lưu vào order.
        $updates = [
            'ghn_status' => $status,
            'ghn_status_name' => $this->statusName($status),
            'ghn_expected_delivery_time' => $this->parseDateTime(
                data_get($data, 'leadtime') ?: data_get($data, 'expected_delivery_time')
            ),
            'ghn_raw_response' => $ghnInfo,
            'ghn_synced_at' => now(),
        ];

        // Nếu đơn local chưa kết thúc thì GHN được cập nhật order_status.
        if ($localStatus && ! $this->isLocalTerminalStatus($oldOrderStatus)) {
            $updates['order_status'] = $localStatus;
        }

        // COD giao thành công thì coi như đã thu tiền.
        if (
            $localStatus === OrderStatus::Delivered->value
            && $order->payment_method === PaymentMethod::Cod->value
        ) {
            $updates['payment_status'] = OrderPaymentStatus::Paid->value;
            $updates['paid_at'] = $order->paid_at ?: now();
        }

        // COD bị hủy khi chưa paid thì cập nhật payment_status cancelled.
        if (
            $localStatus === OrderStatus::Cancelled->value
            && $order->payment_method === PaymentMethod::Cod->value
            && $order->payment_status !== OrderPaymentStatus::Paid->value
        ) {
            $updates['payment_status'] = OrderPaymentStatus::Cancelled->value;
        }

        // Cập nhật order theo dữ liệu GHN.
        $this->orders->update($order, $updates);

        // Nếu trạng thái GHN hoặc trạng thái đơn thay đổi thì ghi log.
        if ($oldGhnStatus !== $status || (! empty($updates['order_status']) && $updates['order_status'] !== $oldOrderStatus)) {
            OrderStatusChanged::dispatch(
                (int) $order->id,
                (string) ($updates['order_status'] ?? $order->order_status),
                $notePrefix.': '.$this->statusGroup($status).' - '.$this->statusName($status).' ('.$status.')',
                $changedBy
            );
        }
    }

    public function mapStatusToOrderStatus(string $ghnStatus): ?string
    {
        return GhnOrderStatus::toOrderStatusValue($ghnStatus);
    }

    public function statusGroup(string $ghnStatus): string
    {
        return GhnOrderStatus::groupFor($ghnStatus);
    }

    public function statusGroupBadge(string $ghnStatus): string
    {
        return GhnOrderStatus::badgeFor($ghnStatus);
    }

    public function statusName(string $ghnStatus): string
    {
        return GhnOrderStatus::labelFor($ghnStatus);
    }

    protected function buildCreateOrderPayload(Order $order): array
    {
        // Chuẩn bị dữ liệu tạo vận đơn đúng format GHN yêu cầu.
        $fromDistrictId = (int) config('services.ghn.from_district_id');
        $toDistrictId = (int) $order->ghn_to_district_id;
        $serviceId = $this->getAvailableServiceId($fromDistrictId, $toDistrictId) ?: 0;
        $package = $this->buildPackageInfo($order);
        $clientOrderCode = $order->ghn_client_order_code ?: $this->buildClientOrderCode($order);

        // COD thì GHN thu hộ tổng tiền đơn, VNPay thì không thu hộ.
        $codAmount = $order->payment_method === PaymentMethod::Cod->value
            ? (int) round((float) $order->total_price)
            : 0;

        return [
            'payment_type_id' => (int) config('services.ghn.payment_type_id', 1),
            (string) ($order->customer_note ?: 'Đơn hàng '.$order->order_code),
            'required_note' => (string) config('services.ghn.required_note', 'KHONGCHOXEMHANG'),

            // Thông tin người gửi lấy từ cấu hình shop.
            'from_name' => (string) config('services.ghn.from_name'),
            'from_phone' => (string) config('services.ghn.from_phone'),
            'from_address' => (string) config('services.ghn.from_address'),
            'from_ward_name' => (string) config('services.ghn.from_ward_name'),
            'from_district_name' => (string) config('services.ghn.from_district_name'),
            'from_province_name' => (string) config('services.ghn.from_province_name'),

            // Thông tin trả hàng cũng lấy theo địa chỉ shop.
            'return_name' => (string) config('services.ghn.from_name'),
            'return_phone' => (string) config('services.ghn.from_phone'),
            'return_address' => (string) config('services.ghn.from_address'),
            'return_ward_code' => (string) config('services.ghn.from_ward_code'),
            'return_district_id' => $fromDistrictId,

            // Thông tin người nhận lấy từ order.
            'client_order_code' => $clientOrderCode,
            'to_name' => (string) $order->name,
            'to_phone' => (string) $order->phone,
            'to_address' => (string) $order->address,
            'to_ward_code' => (string) $order->ghn_to_ward_code,
            'to_district_id' => $toDistrictId,

            // Thông tin tiền thu hộ và kiện hàng.
            'cod_amount' => $codAmount,
            'content' => 'Đơn hàng '.$order->order_code,
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
        // Tính cân nặng, kích thước kiện hàng và danh sách item gửi sang GHN.
        $items = [];
        $totalWeight = 0;
        $maxLength = 1;
        $maxWidth = 1;
        $totalHeight = 0;

        foreach ($order->details as $detail) {
            $product = $detail->variant?->product;
            $quantity = max((int) $detail->quantity, 1);

            // Lấy cân nặng/kích thước từ product, nếu thiếu thì dùng default trong config.
            $weight = max((int) ($product->weight ?? config('services.ghn.default_weight', 500)), 1);
            $length = max((int) ($product->length ?? config('services.ghn.default_length', 20)), 1);
            $width = max((int) ($product->width ?? config('services.ghn.default_width', 20)), 1);
            $height = max((int) ($product->height ?? config('services.ghn.default_height', 10)), 1);

            // Tổng cân nặng tính theo số lượng sản phẩm.
            $totalWeight += $weight * $quantity;

            // Lấy chiều dài/rộng lớn nhất, chiều cao cộng dồn theo số lượng.
            $maxLength = max($maxLength, $length);
            $maxWidth = max($maxWidth, $width);
            $totalHeight += $height * $quantity;

            // Tạo item theo format GHN yêu cầu.
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

        // Giới hạn kích thước gửi sang GHN, tránh gửi giá trị quá nhỏ hoặc quá lớn.
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
        // Nếu order đã có mã GHN địa chỉ thì dùng luôn.
        if (! empty($order->ghn_to_district_id) && ! empty($order->ghn_to_ward_code)) {
            return $order;
        }

        $address = $this->addresses->findUsableGhnAddressForOrder(
            (int) $order->user_id,
            $order->shipping_address_id ? (int) $order->shipping_address_id : null,
            $order->address
        );

        // Không tìm được địa chỉ phù hợp thì trả order cũ.
        if (! $address) {
            return $order;
        }

        // Bổ sung mã GHN địa chỉ vào order.
        $this->orders->update($order, [
            'shipping_address_id' => $address->id,
            'ghn_to_province_id' => $address->ghn_province_id,
            'ghn_to_district_id' => $address->ghn_district_id,
            'ghn_to_ward_code' => $address->ghn_ward_code,
        ]);

        return $order->fresh();
    }

    protected function getAvailableServiceId(int $fromDistrictId, int $toDistrictId): ?int
    {
        // Thiếu dữ liệu tuyến gửi/nhận hoặc cấu hình GHN thì không lấy service_id.
        if ($fromDistrictId <= 0 || $toDistrictId <= 0 || empty($this->token) || empty($this->shopId)) {
            return null;
        }

        // Gọi API GHN để lấy dịch vụ vận chuyển khả dụng cho tuyến này.
        $response = Http::withHeaders($this->headers(false))
            ->post($this->baseUrl.'/v2/shipping-order/available-services', [
                'shop_id' => (int) $this->shopId,
                'from_district' => $fromDistrictId,
                'to_district' => $toDistrictId,
            ]);

        $json = $response->json();

        Log::info('GHN available-services response', [
            'status' => $response->status(),
            'body' => $json,
        ]);

        // Nếu API lỗi thì trả null để bên ngoài fallback hoặc báo lỗi.
        if (! $response->successful() || (int) data_get($json, 'code', 500) !== 200) {
            return null;
        }

        // Lấy service_id đầu tiên hợp lệ.
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
        // Header chuẩn khi gọi API GHN.
        $headers = [
            'Token' => $this->token,
            'Content-Type' => 'application/json',
        ];

        // Một số API cần ShopId, một số API không cần nên cho truyền option.
        if ($withShopId) {
            $headers['ShopId'] = $this->shopId;
        }

        return $headers;
    }

    protected function ensureConfigured(bool $requireShopId = true): void
    {
        // Kiểm tra token GHN.
        if ($this->token === '') {
            throw new RuntimeException('Thiếu GHN_TOKEN.');
        }

        // Một số API yêu cầu ShopId.
        if ($requireShopId && $this->shopId === '') {
            throw new RuntimeException('Thiếu GHN_SHOP_ID.');
        }

        // Kiểm tra thông tin địa chỉ shop gửi hàng.
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
                throw new RuntimeException('Thiếu cấu hình GHN: GHN_'.strtoupper($key));
            }
        }
    }

    protected function buildClientOrderCode(Order $order): string
    {
        // Tạo mã đơn client gửi sang GHN để map với đơn local.
        return 'LOCAL-'.$order->order_code.'-'.$order->id;
    }

    protected function parseDateTime($value): mixed
    {
        // Parse thời gian GHN trả về, lỗi thì trả null.
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
        // Nếu đơn local đã delivered/cancelled thì không cho GHN ghi đè order_status nữa.
        return in_array($status, [
            OrderStatus::Delivered->value,
            OrderStatus::Cancelled->value,
        ], true);
    }
}
