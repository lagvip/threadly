<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GhnWebhookRequest;
use App\Services\Api\GhnWebhookService;
use Illuminate\Support\Facades\Log;

class GhnWebhookController extends Controller
{
    public function __construct(protected GhnWebhookService $webhooks) {}

    public function handle(GhnWebhookRequest $request)
    {
        $data = $request->toDTO();

        if (! $this->webhooks->isValidSecret($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook secret',
            ], 401);
        }

        try {
            $result = $this->webhooks->accept($data);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        } catch (\Throwable $e) {
            Log::error('GHN webhook process failed: '.$e->getMessage(), [
                'payload' => $data->payload,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook process failed',
            ], 500);
        }
    }
}
