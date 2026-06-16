<?php

namespace App\DTOs\Integrations\Ghn;

class GhnWebhookData
{
    public function __construct(
        public readonly array $payload,
        protected readonly array $secretCandidates = [],
    ) {}

    public static function fromArray(array $payload, array $secretCandidates = []): self
    {
        return new self($payload, $secretCandidates);
    }

    public function secretCandidates(): array
    {
        return $this->secretCandidates;
    }

    public function type(): ?string
    {
        return $this->value(['Type', 'type']);
    }

    public function orderCode(): ?string
    {
        return $this->value(['OrderCode', 'order_code', 'orderCode']);
    }

    public function clientOrderCode(): ?string
    {
        return $this->value(['ClientOrderCode', 'client_order_code', 'clientOrderCode']);
    }

    public function status(): ?string
    {
        return $this->value(['Status', 'status']);
    }

    protected function value(array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = data_get($this->payload, $key);

            if ($value !== null && $value !== '') {
                return (string) $value;
            }
        }

        return null;
    }
}
