<?php

namespace App\Services\Api;

use App\Contracts\Repositories\GhnWebhookLogRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTOs\Integrations\Ghn\GhnWebhookData;
use App\Models\Order;
use App\Services\Integrations\Ghn\GhnService;
use Illuminate\Support\Facades\DB;

class GhnWebhookService
{
    public function __construct(
        protected GhnService $ghn,
        protected GhnWebhookLogRepositoryInterface $webhookLogs,
        protected OrderRepositoryInterface $orders,
    ) {}

    public function isValidSecret(GhnWebhookData $data): bool
    {
        $secret = (string) config('services.ghn.webhook_secret');

        if ($secret === '') {
            return in_array((string) config('app.env'), ['local', 'testing'], true);
        }

        $received = collect($data->secretCandidates())
            ->first(fn ($value) => is_string($value) && $value !== '');

        return is_string($received) && hash_equals($secret, $received);
    }

    public function accept(GhnWebhookData $data): array
    {
        $log = $this->webhookLogs->create([
            'order_code' => $data->orderCode(),
            'client_order_code' => $data->clientOrderCode(),
            'type' => $data->type(),
            'status' => $data->status(),
            'payload' => $data->payload,
            'processed' => false,
        ]);

        $order = $this->findOrder($data->orderCode(), $data->clientOrderCode());

        if (! $order) {
            $this->webhookLogs->update($log, [
                'error_message' => 'Không tìm thấy order local tương ứng webhook GHN.',
            ]);

            return [
                'found' => false,
                'message' => 'Webhook accepted but local order not found',
            ];
        }

        try {
            DB::transaction(function () use ($order, $data, $log) {
                $syncPayload = [
                    'data' => array_merge($data->payload, [
                        'status' => $data->status() ?: data_get($data->payload, 'status'),
                    ]),
                ];

                $this->ghn->syncOrderFromGhnInfo($order, $syncPayload, null, 'Webhook GHN');

                $this->webhookLogs->update($log, [
                    'processed' => true,
                    'error_message' => null,
                ]);
            });
        } catch (\Throwable $e) {
            $this->webhookLogs->update($log, [
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
}
