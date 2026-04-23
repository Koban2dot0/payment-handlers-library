<?php

declare(strict_types=1);

namespace PaymentHandlers\Handlers;

use PaymentHandlers\DTO\ProcessingResult;
use PaymentHandlers\Entity\Payment;

final class FailedPaymentHandler implements PaymentHandlerInterface
{
    /**
     * @param array<string, mixed> $gatewayResponse
     */
    public function supports(array $gatewayResponse): bool
    {
        return ($gatewayResponse['result'] ?? null) === 'DECLINED'
            && ($gatewayResponse['status'] ?? null) === 'DECLINED';
    }

    /**
     * @param array<string, mixed> $gatewayResponse
     */
    public function handle(Payment $payment, array $gatewayResponse): ProcessingResult
    {
        $payment->setStatus(Payment::STATUS_DECLINED);
        $payment->setExternalTransactionId($gatewayResponse['trans_id'] ?? null);
        $payment->setGatewayStatus($gatewayResponse['status'] ?? null);

        return new ProcessingResult(
            Payment::STATUS_DECLINED,
            (string) ($gatewayResponse['decline_reason'] ?? 'Payment was declined.'),
            $payment
        );
    }
}
