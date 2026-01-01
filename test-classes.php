<?php

require_once __DIR__ . '/vendor/autoload.php';

echo "Testing class loading:\n";

// Test if classes exist
$classes = [
    'XPay\Types\XPayConfig',
    'XPay\Types\ApiResponse', 
    'XPay\Types\CreateCustomerRequest',
    'XPay\Types\PaymentRequest',
    'XPay\XPay'
];

foreach ($classes as $class) {
    if (class_exists($class)) {
        echo "✅ $class: exists\n";
    } else {
        echo "❌ $class: missing\n";
    }
}

echo "\nTesting basic HTTP request:\n";
try {
    $client = new GuzzleHttp\Client();
    $response = $client->request('GET', 'http://localhost:8000/v1/healthz', [
        'headers' => [
            'X-API-Key' => 'sk_sandbox_3f73759d-6de5-4102-8f29-923c935d',
            'X-Environment' => 'sandbox',
            'Content-Type' => 'application/json'
        ]
    ]);
    
    echo "✅ HTTP Status: " . $response->getStatusCode() . "\n";
    echo "✅ Response: " . $response->getBody() . "\n";
    
} catch (Exception $e) {
    echo "❌ HTTP Error: " . $e->getMessage() . "\n";
}
?>