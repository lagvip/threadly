<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Api\GhnWebhookService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GhnWebhookController extends Controller
{
    public function __construct(protected GhnWebhookService $webhooks)
    {
    }

    public function handle(Request $request)
    {
        $payload = $request->all();

        if (!$this->webhooks->isValidSecret($request)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid webhook secret',
            ], 401);
        }

        try {
            $result = $this->webhooks->accept($payload);

            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        } catch (\Throwable $e) {
            Log::error('GHN webhook process failed: ' . $e->getMessage(), [
                'payload' => $payload,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook process failed',
            ], 500);
        }
    }
}
