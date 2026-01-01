<?php

require_once __DIR__ . '/vendor/autoload.php';

// Temporarily override the GuzzleHttp Client stub to use the real one
class_alias(\GuzzleHttp\Client::class, 'GuzzleHttp\ClientReal');

use XPay\Types\PaymentMethodData;
use XPay\Types\PaymentRequest;
use XPay\Types\CreateCustomerRequest;
use XPay\Types\XPayConfig;
use XPay\XPay;

echo "=== Direct PHP SDK Test ===\n";

// Test with your credentials
$config = new XPayConfig(
    apiKey: 'sk_sandbox_3f73759d-6de5-4102-8f29-923c935d',
    merchantId: '548d8033-fbe9-411b-991f-f159cdee7745',
    environment: 'sandbox',
    baseUrl: 'http://localhost:8000'
);

echo "✅ Configuration created\n";
echo "🔐 API Key: " . substr($config->apiKey, 0, 20) . "...\n";
echo "🏪 Merchant ID: {$config->merchantId}\n";
echo "🌍 Environment: {$config->getEnvironment()}\n";
echo "🌐 Base URL: {$config->getBaseUrl()}\n\n";

// Test configuration before creating client
echo "📡 Testing server connectivity...\n";
$response = @file_get_contents('http://localhost:8000/v1/healthz');
if ($response === false) {
    echo "❌ Server is not running on localhost:8000\n";
    exit(1);
}
echo "✅ Server is running\n\n";

try {
    // Create XPay client - this may fail due to stub issues
    echo "🔧 Creating XPay client...\n";
    $xpay = new XPay($config);
    echo "✅ XPay client created successfully\n\n";
    
    echo "🏪 Merchant ID from client: " . $xpay->getMerchantId() . "\n\n";
    
    // Test ping
    echo "📞 Testing API ping...\n";
    $ping = $xpay->ping();
    echo "Response: " . json_encode($ping, JSON_PRETTY_PRINT) . "\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n\n";
    
    // If it's a stub issue, let's do a direct HTTP test
    if (str_contains($e->getMessage(), 'Stub implementation')) {
        echo "🔧 Attempting direct HTTP test...\n";
        
        // Direct Guzzle HTTP test
        try {
            $client = new GuzzleHttp\Client();
            $response = $client->get('http://localhost:8000/v1/healthz', [
                'headers' => [
                    'Authorization' => 'Bearer sk_sandbox_3f73759d-6de5-4102-8f29-923c935d',
                    'X-Environment' => 'sandbox',
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            echo "✅ Direct HTTP request successful!\n";
            echo "Status: " . $response->getStatusCode() . "\n";
            echo "Response: " . $response->getBody() . "\n\n";
            
            // Try payment methods endpoint
            $response = $client->get('http://localhost:8000/v1/api/merchants/548d8033-fbe9-411b-991f-f159cdee7745/payment-methods', [
                'headers' => [
                    'Authorization' => 'Bearer sk_sandbox_3f73759d-6de5-4102-8f29-923c935d',
                    'X-Environment' => 'sandbox',
                    'Content-Type' => 'application/json'
                ]
            ]);
            
            echo "✅ Payment methods request successful!\n";
            echo "Status: " . $response->getStatusCode() . "\n";
            echo "Response: " . $response->getBody() . "\n\n";
            
        } catch (Exception $httpError) {
            echo "❌ Direct HTTP error: " . $httpError->getMessage() . "\n";
        }
    }
}

echo "=== Test Complete ===\n";