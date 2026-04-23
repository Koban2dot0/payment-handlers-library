<?php

declare(strict_types=1);

namespace PaymentHandlers\Services;

use PaymentHandlers\DTO\PaymentRequest;
use RuntimeException;

final class PaymentGatewayClient
{
    private string $paymentUrl;
    private string $clientKey;
    private string $password;
    private ?string $channelId;

    public function __construct(
        string $paymentUrl,
        string $clientKey,
        string $password,
        ?string $channelId = null
    ) {
        $this->paymentUrl = $paymentUrl;
        $this->clientKey = $clientKey;
        $this->password = $password;
        $this->channelId = $channelId;
    }

    /**
     * @return array<string, mixed>
     */
    public function sale(PaymentRequest $request): array
    {
        $payload = $request->toGatewayPayload();
        $payload['action'] = 'SALE';
        $payload['client_key'] = $this->clientKey;

        if ($this->channelId !== null) {
            $payload['channel_id'] = $this->channelId;
        }

        $payload['hash'] = $this->signSaleRequest(
            $request->getCustomerEmail(),
            $request->getCardNumber()
        );

        return $this->sendRequest($payload);
    }

    /**
     * @param array<string, string> $payload
     *
     * @return array<string, mixed>
     */
    private function sendRequest(array $payload): array
    {
        if (function_exists('curl_init')) {
            [$rawResponse, $httpCode] = $this->sendRequestWithCurl($payload);
        } else {
            [$rawResponse, $httpCode] = $this->sendRequestWithStream($payload);
        }

        $decoded = json_decode($rawResponse, true);
        if (!is_array($decoded)) {
            throw new RuntimeException(sprintf('Invalid gateway response: %s', $rawResponse));
        }

        if ($httpCode >= 400) {
            throw new RuntimeException(sprintf(
                'Gateway returned HTTP %d: %s',
                $httpCode,
                $decoded['error_message'] ?? $rawResponse
            ));
        }

        if (($decoded['result'] ?? null) === 'ERROR') {
            throw new RuntimeException((string) ($decoded['error_message'] ?? 'Gateway validation error.'));
        }

        return $decoded;
    }

    /**
     * @param array<string, string> $payload
     *
     * @return array{0: string, 1: int}
     */
    private function sendRequestWithCurl(array $payload): array
    {
        $ch = curl_init($this->paymentUrl);
        if ($ch === false) {
            throw new RuntimeException('Unable to initialize cURL.');
        }

        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_POSTFIELDS => http_build_query($payload),
            CURLOPT_TIMEOUT => 30,
        ]);

        $rawResponse = curl_exec($ch);
        if ($rawResponse === false) {
            $error = curl_error($ch);
            curl_close($ch);
            throw new RuntimeException(sprintf('Gateway request failed: %s', $error));
        }

        $httpCode = (int) curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        return [(string) $rawResponse, $httpCode];
    }

    /**
     * @param array<string, string> $payload
     *
     * @return array{0: string, 1: int}
     */
    private function sendRequestWithStream(array $payload): array
    {
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query($payload),
                'timeout' => 30,
                'ignore_errors' => true,
            ],
        ]);

        $rawResponse = @file_get_contents($this->paymentUrl, false, $context);
        if ($rawResponse === false) {
            $error = error_get_last();
            throw new RuntimeException(sprintf(
                'Gateway request failed: %s',
                is_array($error) ? ($error['message'] ?? 'unknown error') : 'unknown error'
            ));
        }

        $httpCode = $this->extractHttpStatusCode($http_response_header ?? []);

        return [$rawResponse, $httpCode];
    }

    /**
     * @param array<int, string> $headers
     */
    private function extractHttpStatusCode(array $headers): int
    {
        foreach ($headers as $headerLine) {
            if (preg_match('/^HTTP\/\d\.\d\s+(\d{3})/', $headerLine, $matches) === 1) {
                return (int) $matches[1];
            }
        }

        return 0;
    }

    private function signSaleRequest(
        string $email,
        string $cardNumber
    ): string {
        /**
         * Formula 1:
         * hash for SALE, RETRY is calculated by the formula:
         *
         * md5(strtoupper(strrev(email).PASSWORD.strrev(substr(card_number,0,6).substr(card_number,-4))))
         */
        $cardSignaturePart = substr($cardNumber, 0, 6) . substr($cardNumber, -4);

        return md5(strtoupper(strrev($email) . $this->password . strrev($cardSignaturePart)));
    }
}
