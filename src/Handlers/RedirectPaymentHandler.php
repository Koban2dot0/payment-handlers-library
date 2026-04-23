<?php

declare(strict_types=1);

namespace PaymentHandlers\Handlers;

use PaymentHandlers\DTO\ProcessingResult;
use PaymentHandlers\Entity\Payment;

final class RedirectPaymentHandler implements PaymentHandlerInterface
{
    /**
     * @param array<string, mixed> $gatewayResponse
     */
    public function supports(array $gatewayResponse): bool
    {
        $result = $gatewayResponse['result'] ?? null;
        $status = $gatewayResponse['status'] ?? null;

        return $result === 'REDIRECT'
            && in_array($status, ['3DS', 'REDIRECT'], true);
    }

    /**
     * @param array<string, mixed> $gatewayResponse
     */
    public function handle(Payment $payment, array $gatewayResponse): ProcessingResult
    {
        $payment->setStatus(Payment::STATUS_REDIRECT);
        $payment->setExternalTransactionId($gatewayResponse['trans_id'] ?? null);
        $payment->setGatewayStatus($gatewayResponse['status'] ?? null);

        return new ProcessingResult(
            Payment::STATUS_REDIRECT,
            'Customer redirect is required.',
            $payment,
            isset($gatewayResponse['redirect_url']) ? (string) $gatewayResponse['redirect_url'] : null,
            strtoupper((string) ($gatewayResponse['redirect_method'] ?? 'POST')),
            $this->normalizeRedirectParams($gatewayResponse['redirect_params'] ?? [])
        );
    }

    /**
     * @param array<mixed, mixed> $redirectParams
     *
     * @return array<string, string>
     */
    private function normalizeRedirectParams(array $redirectParams): array
    {
        $normalized = [];

        foreach ($redirectParams as $key => $value) {
            if (is_array($value) && array_key_exists('name', $value) && array_key_exists('value', $value)) {
                $normalized[(string) $value['name']] = (string) $value['value'];
                continue;
            }

            if (is_string($key)) {
                $normalized[$key] = is_scalar($value) ? (string) $value : '';
            }
        }

        return $normalized;
    }
}
