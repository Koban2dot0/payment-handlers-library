<?php

declare(strict_types=1);

namespace PaymentHandlers\Registry;

use PaymentHandlers\Handlers\PaymentHandlerInterface;
use RuntimeException;

final class PaymentHandlerRegistry
{
    /**
     * @var list<PaymentHandlerInterface>
     */
    private array $handlers = [];

    public function registerHandler(PaymentHandlerInterface $handler): void
    {
        $this->handlers[] = $handler;
    }

    /**
     * @param array<string, mixed> $gatewayResponse
     */
    public function resolve(array $gatewayResponse): PaymentHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->supports($gatewayResponse)) {
                return $handler;
            }
        }

        throw new RuntimeException(sprintf(
            'No payment handler found for result "%s" and status "%s".',
            $gatewayResponse['result'] ?? 'unknown',
            $gatewayResponse['status'] ?? 'unknown'
        ));
    }
}
