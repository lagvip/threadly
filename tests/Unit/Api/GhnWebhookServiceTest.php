<?php

namespace Tests\Unit\Api;

use App\Contracts\Repositories\GhnWebhookLogRepositoryInterface;
use App\Contracts\Repositories\OrderRepositoryInterface;
use App\DTOs\Integrations\Ghn\GhnWebhookData;
use App\Enums\GhnOrderStatus;
use App\Models\GhnWebhookLog;
use App\Services\Api\GhnWebhookService;
use App\Services\Integrations\Ghn\GhnService;
use Tests\TestCase;

class GhnWebhookServiceTest extends TestCase
{
    public function test_valid_secret_allows_missing_secret_only_in_local_or_testing(): void
    {
        config(['services.ghn.webhook_secret' => '']);

        $this->assertTrue($this->service()->isValidSecret(GhnWebhookData::fromArray([])));
    }

    public function test_valid_secret_rejects_missing_secret_in_production(): void
    {
        config([
            'app.env' => 'production',
            'services.ghn.webhook_secret' => '',
        ]);

        $this->assertFalse($this->service()->isValidSecret(GhnWebhookData::fromArray([])));
    }

    public function test_valid_secret_accepts_matching_header(): void
    {
        config(['services.ghn.webhook_secret' => 'webhook-secret']);

        $this->assertTrue($this->service()->isValidSecret(GhnWebhookData::fromArray([], ['webhook-secret'])));
    }

    public function test_valid_secret_rejects_wrong_secret(): void
    {
        config(['services.ghn.webhook_secret' => 'webhook-secret']);

        $this->assertFalse($this->service()->isValidSecret(GhnWebhookData::fromArray([], ['wrong-secret'])));
    }

    public function test_accept_logs_payload_and_returns_not_found_when_order_is_missing(): void
    {
        $log = new FakeGhnWebhookLog;
        $payload = [
            'Type' => 'switch_status',
            'OrderCode' => 'GHN123',
            'ClientOrderCode' => 'LOCAL-OD001-1',
            'Status' => GhnOrderStatus::Delivered->value,
        ];

        $webhookLogs = $this->createMock(GhnWebhookLogRepositoryInterface::class);
        $webhookLogs->expects($this->once())
            ->method('create')
            ->with($this->callback(function (array $data) use ($payload) {
                return $data['order_code'] === 'GHN123'
                    && $data['client_order_code'] === 'LOCAL-OD001-1'
                    && $data['type'] === 'switch_status'
                    && $data['status'] === GhnOrderStatus::Delivered->value
                    && $data['payload'] === $payload
                    && $data['processed'] === false;
            }))
            ->willReturn($log);

        $webhookLogs->expects($this->once())
            ->method('update')
            ->with($log, $this->callback(fn (array $data) => ! empty($data['error_message'])))
            ->willReturn(true);

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

        $result = $service->accept(GhnWebhookData::fromArray($payload));

        $this->assertSame([
            'found' => false,
            'message' => 'Webhook accepted but local order not found',
        ], $result);
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

class FakeGhnWebhookLog extends GhnWebhookLog {}
