<?php

require_once __DIR__ . '/vendor/autoload.php';

// Manually require the ApiTypes.php file
require_once __DIR__ . '/src/Types/ApiTypes.php';

echo "Testing class loading after manual require:\n";

$classes = [
    'XPay\Types\ApiResponse', 
    'XPay\Types\CreateCustomerRequest',
    'XPay\Types\PaymentRequest',
    'XPay\Types\PaymentMethodData'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✅ $class: exists\n";
    } else {
        echo "❌ $class: missing\n";
    }
}

// Now test the SDK
echo "\nTesting XPay SDK:\n";

use XPay\Types\XPayConfig;
use XPay\XPay;

try {
    $config = new XPayConfig(
        apiKey: 'sk_sandbox_3f73759d-6de5-4102-8f29-923c935d',
        merchantId: '548d8033-fbe9-411b-991f-f159cdee7745',
        environment: 'sandbox',
        baseUrl: 'http://localhost:8000'
    );
    
    echo "✅ Config created\n";
    
    $xpay = new XPay($config);
    echo "✅ XPay client created\n";
    
    $ping = $xpay->ping();
    echo "✅ Ping successful: " . json_encode($ping) . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}
?>