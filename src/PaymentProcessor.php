<?php

declare(strict_types=1);

namespace PaymentHandlers;

use PaymentHandlers\DTO\PaymentRequest;
use PaymentHandlers\DTO\ProcessingResult;
use PaymentHandlers\Entity\Payment;
use PaymentHandlers\Registry\PaymentHandlerRegistry;
use PaymentHandlers\Services\PaymentGatewayClient;
use RuntimeException;

final class PaymentProcessor
{
    private PaymentHandlerRegistry $registry;
    private PaymentGatewayClient $gatewayClient;

    public function __construct(
        PaymentHandlerRegistry $registry,
        PaymentGatewayClient $gatewayClient
    ) {
        $this->registry = $registry;
        $this->gatewayClient = $gatewayClient;
    }

    /**
     * @param array<string, mixed> $rawData
     */
    public function process(Payment $payment, array $rawData): ProcessingResult
    {
        if ($payment->getStatus() !== Payment::STATUS_PREPARED) {
            throw new RuntimeException('Only payments in "prepared" status can be processed.');
        }

        $request = PaymentRequest::fromArray($rawData, $payment);
        $gatewayResponse = $this->gatewayClient->sale($request);
        $handler = $this->registry->resolve($gatewayResponse);

        return $handler->handle($payment, $gatewayResponse);
    }
}
