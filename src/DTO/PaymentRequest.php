<?php

declare(strict_types=1);

namespace PaymentHandlers\DTO;

use InvalidArgumentException;
use PaymentHandlers\Entity\Payment;

final class PaymentRequest
{
    private string $orderId;
    private float $amount;
    private string $currency;
    private string $description;
    private string $cardNumber;
    private string $cardExpMonth;
    private string $cardExpYear;
    private string $cardCvv;
    private string $customerEmail;
    private string $firstName;
    private string $lastName;
    private string $address;
    private string $country;
    private string $city;
    private string $zip;
    private string $phone;
    private string $ip;
    private string $termUrl3ds;

    public function __construct(
        string $orderId,
        float $amount,
        string $currency,
        string $description,
        string $cardNumber,
        string $cardExpMonth,
        string $cardExpYear,
        string $cardCvv,
        string $customerEmail,
        string $firstName,
        string $lastName,
        string $address,
        string $country,
        string $city,
        string $zip,
        string $phone,
        string $ip,
        string $termUrl3ds
    ) {
        $this->orderId = $orderId;
        $this->amount = $amount;
        $this->currency = self::normalizeUpper($currency);
        $this->description = $description;
        $this->cardNumber = self::normalizeCardNumber($cardNumber);
        $this->cardExpMonth = self::normalizeCardExpMonth($cardExpMonth);
        $this->cardExpYear = $cardExpYear;
        $this->cardCvv = $cardCvv;
        $this->customerEmail = $customerEmail;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->address = $address;
        $this->country = self::normalizeUpper($country);
        $this->city = $city;
        $this->zip = $zip;
        $this->phone = $phone;
        $this->ip = $ip;
        $this->termUrl3ds = $termUrl3ds;
    }

    /**
     * @param array<string, mixed> $rawData
     */
    public static function fromArray(array $rawData, Payment $payment): self
    {
        $requiredFields = [
            'cardNumber',
            'cardExpMonth',
            'cardExpYear',
            'cvv',
            'customerEmail',
            'amount',
            'currency',
            'orderDescription',
            'firstName',
            'lastName',
            'address',
            'country',
            'city',
            'zip',
            'phone',
            'ip',
            'termUrl3ds',
        ];

        foreach ($requiredFields as $field) {
            if (
                !array_key_exists($field, $rawData)
                || $rawData[$field] === null
                || (is_string($rawData[$field]) && trim($rawData[$field]) === '')
            ) {
                throw new InvalidArgumentException(sprintf('Missing required field "%s".', $field));
            }
        }

        if (!filter_var((string) $rawData['customerEmail'], FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException('Invalid customer email.');
        }

        if (!is_numeric($rawData['amount']) || (float) $rawData['amount'] <= 0) {
            throw new InvalidArgumentException('Amount must be a positive number.');
        }

        return new self(
            (string) ($rawData['orderId'] ?? sprintf('payment-%d', $payment->getId())),
            (float) $rawData['amount'],
            (string) $rawData['currency'],
            (string) $rawData['orderDescription'],
            (string) $rawData['cardNumber'],
            (string) $rawData['cardExpMonth'],
            (string) $rawData['cardExpYear'],
            (string) $rawData['cvv'],
            (string) $rawData['customerEmail'],
            (string) $rawData['firstName'],
            (string) $rawData['lastName'],
            (string) $rawData['address'],
            (string) $rawData['country'],
            (string) $rawData['city'],
            (string) $rawData['zip'],
            (string) $rawData['phone'],
            (string) $rawData['ip'],
            (string) $rawData['termUrl3ds']
        );
    }

    /**
     * @return array<string, string>
     */
    public function toGatewayPayload(): array
    {
        $payload = [
            'order_id' => $this->orderId,
            'order_amount' => number_format($this->amount, 2, '.', ''),
            'order_currency' => $this->currency,
            'order_description' => $this->description,
            'card_number' => $this->cardNumber,
            'card_exp_month' => $this->cardExpMonth,
            'card_exp_year' => $this->cardExpYear,
            'card_cvv2' => $this->cardCvv,
            'payer_first_name' => $this->firstName,
            'payer_last_name' => $this->lastName,
            'payer_address' => $this->address,
            'payer_country' => $this->country,
            'payer_city' => $this->city,
            'payer_zip' => $this->zip,
            'payer_email' => $this->customerEmail,
            'payer_phone' => $this->phone,
            'payer_ip' => $this->ip,
            'term_url_3ds' => $this->termUrl3ds,
        ];

        return $payload;
    }

    public function getOrderId(): string
    {
        return $this->orderId;
    }

    public function getAmount(): string
    {
        return number_format($this->amount, 2, '.', '');
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function getCustomerEmail(): string
    {
        return $this->customerEmail;
    }

    public function getCardNumber(): string
    {
        return $this->cardNumber;
    }

    private static function normalizeCardNumber(string $cardNumber): string
    {
        return preg_replace('/\s+/', '', $cardNumber) ?? '';
    }

    private static function normalizeCardExpMonth(string $cardExpMonth): string
    {
        return str_pad($cardExpMonth, 2, '0', STR_PAD_LEFT);
    }

    private static function normalizeUpper(string $value): string
    {
        return strtoupper($value);
    }
}
