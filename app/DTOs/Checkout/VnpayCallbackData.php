<?php

namespace App\DTOs\Checkout;

class VnpayCallbackData
{
    public function __construct(
        public readonly array $payload,
    ) {}

    public static function fromArray(array $payload): self
    {
        return new self($payload);
    }

    public function txnRef(): string
    {
        return (string) ($this->payload['vnp_TxnRef'] ?? '');
    }

    public function amount(): mixed
    {
        return $this->payload['vnp_Amount'] ?? null;
    }

    public function responseCode(): string
    {
        return (string) ($this->payload['vnp_ResponseCode'] ?? '');
    }

    public function transactionStatus(): ?string
    {
        return $this->payload['vnp_TransactionStatus'] ?? null;
    }

    public function transactionNo(): ?string
    {
        return $this->payload['vnp_TransactionNo'] ?? null;
    }

    public function bankCode(): ?string
    {
        return $this->payload['vnp_BankCode'] ?? null;
    }

    public function payDate(): ?string
    {
        return $this->payload['vnp_PayDate'] ?? null;
    }

    public function isSuccessful(): bool
    {
        return $this->responseCode() === '00' && $this->transactionStatus() === '00';
    }
}
