<?php

declare(strict_types=1);

namespace PaymentHandlers\Handlers;

use PaymentHandlers\DTO\ProcessingResult;
use PaymentHandlers\Entity\Payment;

interface PaymentHandlerInterface
{
    /**
     * @param array<string, mixed> $gatewayResponse
     */
    public function supports(array $gatewayResponse): bool;

    /**
     * @param array<string, mixed> $gatewayResponse
     */
    public function handle(Payment $payment, array $gatewayResponse): ProcessingResult;
}
