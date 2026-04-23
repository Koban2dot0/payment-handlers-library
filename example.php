<?php

declare(strict_types=1);

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} else {
    spl_autoload_register(static function (string $class): void {
        $prefix = 'PaymentHandlers\\';
        $baseDir = __DIR__ . '/src/';

        if (strpos($class, $prefix) !== 0) {
            return;
        }

        $relativeClass = substr($class, strlen($prefix));
        $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

        if (file_exists($file)) {
            require_once $file;
        }
    });
}

use PaymentHandlers\Entity\Payment;
use PaymentHandlers\Handlers\FailedPaymentHandler;
use PaymentHandlers\Handlers\PendingPaymentHandler;
use PaymentHandlers\Handlers\RedirectPaymentHandler;
use PaymentHandlers\Handlers\SuccessPaymentHandler;
use PaymentHandlers\PaymentProcessor;
use PaymentHandlers\Registry\PaymentHandlerRegistry;
use PaymentHandlers\Services\PaymentGatewayClient;

$registry = new PaymentHandlerRegistry();
$registry->registerHandler(new SuccessPaymentHandler());
$registry->registerHandler(new PendingPaymentHandler());
$registry->registerHandler(new FailedPaymentHandler());
$registry->registerHandler(new RedirectPaymentHandler());

$paymentUrl = getenv('PAYMENT_URL') ?: '';
$publicKey = getenv('PUBLIC_KEY') ?: '';
$pass = getenv('PASS') ?: '';

if ($paymentUrl === '' || $publicKey === '' || $pass === '') {
    throw new RuntimeException('Set PAYMENT_URL, PUBLIC_KEY and PASS environment variables.');
}

$gatewayClient = new PaymentGatewayClient(
    $paymentUrl,
    $publicKey,
    $pass
);

$processor = new PaymentProcessor($registry, $gatewayClient);

$paymentFromDb = new Payment(1, Payment::STATUS_PREPARED);
$rawData = [
    'orderId' => 'ORDER-' . time(),
    'cardNumber' => '4111111111111111',
    'cardExpMonth' => '01',
    'cardExpYear' => '2038',
    'cvv' => '123',
    'customerEmail' => 'success@gmail.com',
    'amount' => 1500.50,
    'currency' => 'USD',
    'orderDescription' => 'Test payment',
    'firstName' => 'John',
    'lastName' => 'Doe',
    'address' => 'Main street 1',
    'country' => 'US',
    'city' => 'New York',
    'zip' => '10001',
    'phone' => '15551234567',
    'ip' => '8.8.8.8',
    'termUrl3ds' => 'https://merchant.example.com/3ds-return',
];

$result = $processor->process($paymentFromDb, $rawData);

echo $result->status . PHP_EOL;
echo $result->message . PHP_EOL;

if ($result->isRedirect()) {
    var_dump([
        'url' => $result->redirectUrl,
        'method' => $result->redirectMethod,
        'params' => $result->redirectParams,
    ]);
}
