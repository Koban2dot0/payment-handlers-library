<?php

declare(strict_types=1);

namespace PaymentHandlers\Handlers;

use PaymentHandlers\DTO\ProcessingResult;
use PaymentHandlers\Entity\Payment;

final class SuccessPaymentHandler implements PaymentHandlerInterface
{
    /**
     * @param array<string, mixed> $gatewayResponse
     */
    public function supports(array $gatewayResponse): bool
    {
        return ($gatewayResponse['result'] ?? null) === 'SUCCESS'
            && ($gatewayResponse['status'] ?? null) === 'SETTLED';
    }

    /**
     * @param array<string, mixed> $gatewayResponse
     */
    public function handle(Payment $payment, array $gatewayResponse): ProcessingResult
    {
        $payment->setStatus(Payment::STATUS_SUCCESS);
        $payment->setExternalTransactionId($gatewayResponse['trans_id'] ?? null);
        $payment->setGatewayStatus($gatewayResponse['status'] ?? null);

        return new ProcessingResult(
            Payment::STATUS_SUCCESS,
            'Payment completed successfully.',
            $payment
        );
    }
}
