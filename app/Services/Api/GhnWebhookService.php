<?php

namespace App\Services\Api;

use App\Contracts\Repositories\GhnWebhookLogRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\Order;
use App\Services\GhnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GhnWebhookService
{
    public function __construct(
        protected GhnService $ghn,
        protected GhnWebhookLogRepositoryInterface $webhookLogs,
        protected OrderRepositoryInterface $orders,
    ) {
    }

    public function isValidSecret(Request $request): bool
    {
        $secret = (string) config('services.ghn.webhook_secret');

        if ($secret === '') {
            return true;
        }

        $received = $request->header('X-GHN-Webhook-Secret')
            ?: $request->header('X-Webhook-Secret')
            ?: $request->query('secret')
            ?: $request->input('secret');

        return is_string($received) && hash_equals($secret, $received);
    }

    public function accept(array $payload): array
    {
        $type = $this->payloadValue($payload, ['Type', 'type']);
        $orderCode = $this->payloadValue($payload, ['OrderCode', 'order_code', 'orderCode']);
        $clientOrderCode = $this->payloadValue($payload, ['ClientOrderCode', 'client_order_code', 'clientOrderCode']);
        $status = $this->payloadValue($payload, ['Status', 'status']);

        $log = $this->webhookLogs->create([
            'order_code' => $orderCode,
            'client_order_code' => $clientOrderCode,
            'type' => $type,
            'status' => $status,
            'payload' => $payload,
            'processed' => false,
        ]);

        $order = $this->findOrder($orderCode, $clientOrderCode);

        if (!$order) {
            $log->update([
                'error_message' => 'Không tìm thấy order local tương ứng webhook GHN.',
            ]);

            return [
                'found' => false,
                'message' => 'Webhook accepted but local order not found',
            ];
        }

        try {
            DB::transaction(function () use ($order, $payload, $status, $log) {
                $data = [
                    'data' => array_merge($payload, [
                        'status' => $status ?: data_get($payload, 'status'),
                    ]),
                ];

                $this->ghn->syncOrderFromGhnInfo($order, $data, null, 'Webhook GHN');

                $log->update([
                    'processed' => true,
                    'error_message' => null,
                ]);
            });
        } catch (\Throwable $e) {
            $log->update([
                'error_message' => $e->getMessage(),
            ]);

            throw $e;
        }

        return [
            'found' => true,
            'message' => 'OK',
        ];
    }

    protected function findOrder(?string $orderCode, ?string $clientOrderCode): ?Order
    {
        if (empty($orderCode) && empty($clientOrderCode)) {
            return null;
        }

        return $this->orders->findByGhnCodes($orderCode, $clientOrderCode);
    }

    protected function payloadValue(array $payload, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($payload, $key);

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }
}
