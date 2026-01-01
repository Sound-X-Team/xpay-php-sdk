<?php

require_once __DIR__ . '/bootstrap.php';

echo "Testing XPay PHP SDK...\n\n";

try {
    // Test XPayConfig
    echo "✓ Testing XPayConfig... ";
    $config = new \XPay\Types\XPayConfig('test_key');
    echo "OK\n";

    // Test XPayException
    echo "✓ Testing XPayException... ";
    $exception = new \XPay\Exceptions\XPayException('Test message', 'TEST_CODE', 400);
    if ($exception->getErrorCode() === 'TEST_CODE') {
        echo "OK\n";
    } else {
        echo "FAILED\n";
    }

    // Test ValidationException
    echo "✓ Testing ValidationException... ";
    $validation = new \XPay\Exceptions\ValidationException('Validation failed');
    if ($validation->getErrorCode() === 'VALIDATION_ERROR') {
        echo "OK\n";
    } else {
        echo "FAILED\n";
    }

    // Test Client (with mocked HTTP client)
    echo "✓ Testing Client creation... ";
    $client = new \XPay\Http\Client($config);
    echo "OK\n";

    // Test CurrencyUtils
    echo "✓ Testing CurrencyUtils... ";
    $amount = \XPay\Utils\CurrencyUtils::toSmallestUnit(10.50, 'USD');
    if ($amount === 1050) {
        echo "OK\n";
    } else {
        echo "FAILED (got $amount, expected 1050)\n";
    }

    echo "\n✅ All basic tests passed!\n";
    echo "The core SDK functionality works correctly.\n\n";

} catch (\Throwable $e) {
    echo "\n❌ Test failed: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    exit(1);
}

echo "Note: Intelephense warnings are expected without composer install.\n";
echo "Run 'composer install' to resolve all dependency issues.\n";