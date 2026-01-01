<?php

// This is a simple test script to validate the PHP SDK structure
// Run: php test-structure.php

require_once __DIR__ . '/src/Types/XPayConfig.php';
require_once __DIR__ . '/src/Types/ApiTypes.php';
require_once __DIR__ . '/src/Exceptions/XPayExceptions.php';
require_once __DIR__ . '/src/Utils/CurrencyUtils.php';
require_once __DIR__ . '/src/Utils/WebhookUtils.php';

use XPay\Types\XPayConfig;
use XPay\Types\PaymentRequest;
use XPay\Types\PaymentMethodData;
use XPay\Utils\CurrencyUtils;
use XPay\Utils\WebhookUtils;

echo "Testing X-Pay PHP SDK Structure...\n\n";

// Test 1: XPayConfig
echo "1. Testing XPayConfig...\n";
try {
    $config = new XPayConfig('xpay_sandbox_test123');
    echo "   ✅ Config created successfully\n";
    echo "   ✅ Environment: " . $config->getEnvironment() . "\n";
    echo "   ✅ Base URL: " . $config->getBaseUrl() . "\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 2: PaymentRequest
echo "\n2. Testing PaymentRequest...\n";
try {
    $paymentRequest = new PaymentRequest(
        amount: '10.00',
        paymentMethod: 'stripe',
        currency: 'USD',
        paymentMethodData: new PaymentMethodData(paymentMethodTypes: ['card'])
    );
    $array = $paymentRequest->toArray();
    echo "   ✅ PaymentRequest created successfully\n";
    echo "   ✅ Array conversion works: " . count($array) . " fields\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 3: CurrencyUtils
echo "\n3. Testing CurrencyUtils...\n";
try {
    $currencies = CurrencyUtils::getSupportedCurrencies('stripe');
    echo "   ✅ Stripe currencies: " . implode(', ', $currencies) . "\n";
    
    $amount = CurrencyUtils::toSmallestUnit(10.50, 'USD');
    echo "   ✅ $10.50 = {$amount} cents\n";
    
    $formatted = CurrencyUtils::formatAmount(10.50, 'USD', false);
    echo "   ✅ Formatted: {$formatted}\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 4: WebhookUtils
echo "\n4. Testing WebhookUtils...\n";
try {
    $payload = '{"test": "data"}';
    $secret = 'test_secret';
    $signature = WebhookUtils::generateSignature($payload, $secret);
    $isValid = WebhookUtils::verifySignature($payload, $signature, $secret);
    echo "   ✅ Signature generation and verification: " . ($isValid ? 'PASS' : 'FAIL') . "\n";
    
    $events = WebhookUtils::getSupportedEvents();
    echo "   ✅ Supported events: " . count($events) . " events\n";
} catch (Exception $e) {
    echo "   ❌ Error: " . $e->getMessage() . "\n";
}

// Test 5: Environment Detection
echo "\n5. Testing Environment Detection...\n";
$testKeys = [
    'xpay_sandbox_test123' => 'sandbox',
    'xpay_live_test123' => 'live',
    'pk_sandbox_test123' => 'sandbox',
    'pk_live_test123' => 'live',
    'random_key' => 'sandbox'
];

foreach ($testKeys as $apiKey => $expectedEnv) {
    try {
        $config = new XPayConfig($apiKey);
        $actualEnv = $config->getEnvironment();
        $result = $actualEnv === $expectedEnv ? '✅' : '❌';
        echo "   {$result} {$apiKey} → {$actualEnv} (expected: {$expectedEnv})\n";
    } catch (Exception $e) {
        echo "   ❌ Error with {$apiKey}: " . $e->getMessage() . "\n";
    }
}

echo "\n🎉 PHP SDK structure validation complete!\n";
echo "\nNext steps:\n";
echo "1. Run: composer install\n";
echo "2. Run: composer test\n";
echo "3. Test with a real API key\n";