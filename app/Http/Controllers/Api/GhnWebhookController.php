<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\GhnWebhookLog;
use App\Models\Order;
use App\Services\GhnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GhnWebhookController extends Controller
{
    public function handle(Request $request, GhnService $ghnService)
    {
        $payload = $request->all();

        if (!$this->isValidSecret($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook secret',
            ], 401);
        }

        $type = $this->payloadValue($payload, ['Type', 'type']);
        $orderCode = $this->payloadValue($payload, ['OrderCode', 'order_code', 'orderCode']);
        $clientOrderCode = $this->payloadValue($payload, ['ClientOrderCode', 'client_order_code', 'clientOrderCode']);
        $status = $this->payloadValue($payload, ['Status', 'status']);

        $log = GhnWebhookLog::create([
            'order_code' => $orderCode,
            'client_order_code' => $clientOrderCode,
            'type' => $type,
            'status' => $status,
            'payload' => $payload,
            'processed' => false,
        ]);

        try {
            $order = $this->findOrder($orderCode, $clientOrderCode);

            if (!$order) {
                $log->update([
                    'error_message' => 'Không tìm thấy order local tương ứng webhook GHN.',
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Webhook accepted but local order not found',
                ]);
            }

            DB::transaction(function () use ($order, $payload, $status, $ghnService, $log) {
                $data = [
                    'data' => array_merge($payload, [
                        'status' => $status ?: data_get($payload, 'status'),
                    ]),
                ];

                $ghnService->syncOrderFromGhnInfo($order, $data, null, 'Webhook GHN');

                $log->update([
                    'processed' => true,
                    'error_message' => null,
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'OK',
            ]);
        } catch (\Throwable $e) {
            Log::error('GHN webhook process failed: ' . $e->getMessage(), [
                'payload' => $payload,
            ]);

            $log->update([
                'error_message' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook process failed',
            ], 500);
        }
    }

    protected function findOrder(?string $orderCode, ?string $clientOrderCode): ?Order
    {
        if (empty($orderCode) && empty($clientOrderCode)) {
            return null;
        }

        return Order::query()
            ->when($orderCode, fn ($q) => $q->orWhere('ghn_order_code', $orderCode))
            ->when($clientOrderCode, function ($q) use ($clientOrderCode) {
                $q->orWhere('ghn_client_order_code', $clientOrderCode)
                  ->orWhere('order_code', $clientOrderCode);
            })
            ->first();
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

    protected function isValidSecret(Request $request): bool
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
}
