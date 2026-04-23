<?php

declare(strict_types=1);

namespace PaymentHandlers\Handlers;

use PaymentHandlers\DTO\ProcessingResult;
use PaymentHandlers\Entity\Payment;

final class PendingPaymentHandler implements PaymentHandlerInterface
{
    /**
     * @param array<string, mixed> $gatewayResponse
     */
    public function supports(array $gatewayResponse): bool
    {
        $result = $gatewayResponse['result'] ?? null;
        $status = $gatewayResponse['status'] ?? null;

        return in_array($result, ['SUCCESS', 'UNDEFINED'], true)
            && in_array($status, ['PENDING', 'PREPARE'], true);
    }

    /**
     * @param array<string, mixed> $gatewayResponse
     */
    public function handle(Payment $payment, array $gatewayResponse): ProcessingResult
    {
        $payment->setStatus(Payment::STATUS_WAITING);
        $payment->setExternalTransactionId($gatewayResponse['trans_id'] ?? null);
        $payment->setGatewayStatus($gatewayResponse['status'] ?? null);

        return new ProcessingResult(
            Payment::STATUS_WAITING,
            'Waiting for customer action',
            $payment
        );
    }
}
