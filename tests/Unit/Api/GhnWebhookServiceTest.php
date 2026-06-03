<?php

namespace Tests\Unit\Api;

use App\Contracts\Repositories\GhnWebhookLogRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\Models\GhnWebhookLog;
use App\Services\Api\GhnWebhookService;
use App\Services\GhnService;
use Illuminate\Http\Request;
use Tests\TestCase;

class GhnWebhookServiceTest extends TestCase
{
    public function test_valid_secret_returns_true_when_secret_is_not_configured(): void
    {
        config(['services.ghn.webhook_secret' => '']);

        $request = Request::create('/api/ghn/webhook', 'POST');

        $this->assertTrue($this->service()->isValidSecret($request));
    }

    public function test_valid_secret_accepts_matching_header(): void
    {
        config(['services.ghn.webhook_secret' => 'webhook-secret']);

        $request = Request::create('/api/ghn/webhook', 'POST');
        $request->headers->set('X-GHN-Webhook-Secret', 'webhook-secret');

        $this->assertTrue($this->service()->isValidSecret($request));
    }

    public function test_valid_secret_rejects_wrong_secret(): void
    {
        config(['services.ghn.webhook_secret' => 'webhook-secret']);

        $request = Request::create('/api/ghn/webhook', 'POST', [
            'secret' => 'wrong-secret',
        ]);

        $this->assertFalse($this->service()->isValidSecret($request));
    }

    public function test_accept_logs_payload_and_returns_not_found_when_order_is_missing(): void
    {
        $log = new FakeGhnWebhookLog();
        $payload = [
            'Type' => 'switch_status',
            'OrderCode' => 'GHN123',
            'ClientOrderCode' => 'LOCAL-OD001-1',
            'Status' => 'delivered',
        ];

        $webhookLogs = $this->createMock(GhnWebhookLogRepositoryInterface::class);
        $webhookLogs->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $data) use ($payload) {
                return $data['order_code'] === 'GHN123'
                    && $data['client_order_code'] === 'LOCAL-OD001-1'
                    && $data['type'] === 'switch_status'
                    && $data['status'] === 'delivered'
                    && $data['payload'] === $payload
                    && $data['processed'] === false;
            }))
            ->willReturn($log);

        $orders = $this->createMock(OrderRepositoryInterface::class);
        $orders->expects($this->once())
            ->method('findByGhnCodes')
            ->with('GHN123', 'LOCAL-OD001-1')
            ->willReturn(null);

        $service = new GhnWebhookService(
            $this->createMock(GhnService::class),
            $webhookLogs,
            $orders
        );

        $result = $service->accept($payload);

        $this->assertSame([
            'found' => false,
            'message' => 'Webhook accepted but local order not found',
        ], $result);

        $this->assertSame(
            'Không tìm thấy order local tương ứng webhook GHN.',
            $log->updates['error_message']
        );
    }

    protected function service(): GhnWebhookService
    {
        return new GhnWebhookService(
            $this->createMock(GhnService::class),
            $this->createMock(GhnWebhookLogRepositoryInterface::class),
            $this->createMock(OrderRepositoryInterface::class)
        );
    }
}

class FakeGhnWebhookLog extends GhnWebhookLog
{
    public array $updates = [];

    public function update(array $attributes = [], array $options = []): bool
    {
        $this->updates = $attributes;

        return true;
    }
}
