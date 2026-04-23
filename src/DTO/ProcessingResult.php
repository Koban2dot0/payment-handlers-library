<?php

declare(strict_types=1);

namespace PaymentHandlers\DTO;

use PaymentHandlers\Entity\Payment;

final class ProcessingResult
{
    public string $status;
    public string $message;
    public Payment $payment;
    public ?string $redirectUrl;
    public ?string $redirectMethod;

    /**
     * @var array<string, string>
     */
    public array $redirectParams;

    /**
     * @param array<string, string> $redirectParams
     */
    public function __construct(
        string $status,
        string $message,
        Payment $payment,
        ?string $redirectUrl = null,
        ?string $redirectMethod = null,
        array $redirectParams = []
    ) {
        $this->status = $status;
        $this->message = $message;
        $this->payment = $payment;
        $this->redirectUrl = $redirectUrl;
        $this->redirectMethod = $redirectMethod;
        $this->redirectParams = $redirectParams;
    }

    public function isRedirect(): bool
    {
        return $this->status === Payment::STATUS_REDIRECT;
    }
}
