<?php

declare(strict_types=1);

namespace PaymentHandlers\Entity;

final class Payment
{
    public const STATUS_PREPARED = 'prepared';
    public const STATUS_SUCCESS = 'success';
    public const STATUS_DECLINED = 'declined';
    public const STATUS_WAITING = 'waiting';
    public const STATUS_REDIRECT = 'redirect';

    private ?string $externalTransactionId = null;
    private ?string $gatewayStatus = null;

    private int $id;
    private string $status;

    public function __construct(
        int $id,
        string $status
    ) {
        $this->id = $id;
        $this->status = $status;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    public function getExternalTransactionId(): ?string
    {
        return $this->externalTransactionId;
    }

    public function setExternalTransactionId(?string $externalTransactionId): void
    {
        $this->externalTransactionId = $externalTransactionId;
    }

    public function getGatewayStatus(): ?string
    {
        return $this->gatewayStatus;
    }

    public function setGatewayStatus(?string $gatewayStatus): void
    {
        $this->gatewayStatus = $gatewayStatus;
    }
}
