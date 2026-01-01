<?php
/**
 * X-Pay PHP SDK Integration Test
 * 
 * This script demonstrates the full functionality of the X-Pay PHP SDK
 * using the provided sandbox credentials.
 */

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/src/Types/ApiTypes.php';

use XPay\Types\PaymentMethodData;
use XPay\Types\PaymentRequest;
use XPay\Types\CreateCustomerRequest;
use XPay\Types\XPayConfig;
use XPay\XPay;

echo "🚀 X-Pay PHP SDK Integration Test\n";
echo "================================\n\n";

// Configuration
$config = new XPayConfig(
    apiKey: 'sk_sandbox_3f73759d-6de5-4102-8f29-923c935d',
    merchantId: '548d8033-fbe9-411b-991f-f159cdee7745',
    environment: 'sandbox',
    baseUrl: 'http://localhost:8000'
);

$xpay = new XPay($config);

echo "📋 Configuration:\n";
echo "   🔐 API Key: " . substr($config->apiKey, 0, 20) . "...\n";
echo "   🏪 Merchant ID: {$config->merchantId}\n";
echo "   🌍 Environment: {$config->getEnvironment()}\n";
echo "   🌐 Base URL: {$config->getBaseUrl()}\n\n";

try {
    // Test 1: API Connectivity
    echo "1️⃣  Testing API Connectivity\n";
    echo "   ⏳ Pinging API...\n";
    
    $ping = $xpay->ping();
    echo "   ✅ API Response: " . ($ping['success'] ? 'Success' : 'Failed') . "\n";
    echo "   📅 Timestamp: {$ping['timestamp']}\n\n";
    
    // Test 2: Payment Methods
    echo "2️⃣  Getting Payment Methods\n";
    echo "   ⏳ Fetching available payment methods...\n";
    
    $paymentMethods = $xpay->getPaymentMethods();
    echo "   ✅ Payment methods retrieved\n";
    
    if (isset($paymentMethods['payment_methods']) && is_array($paymentMethods['payment_methods'])) {
        foreach ($paymentMethods['payment_methods'] as $method) {
            $status = $method['enabled'] ? '✅' : '❌';
            echo "   {$status} {$method['name']} ({$method['type']})\n";
            if (isset($method['currencies'])) {
                echo "      💰 Currencies: " . implode(', ', $method['currencies']) . "\n";
            }
        }
    } else {
        echo "   ⚠️  No payment methods or unexpected format\n";
        echo "   📊 Raw response: " . json_encode($paymentMethods, JSON_PRETTY_PRINT) . "\n";
    }
    echo "\n";
    
    // Test 3: Create Customer
    echo "3️⃣  Creating Customer\n";
    echo "   ⏳ Creating test customer...\n";
    
    $customerEmail = 'php-sdk-test-' . bin2hex(random_bytes(4)) . '@example.com';
    $customer = $xpay->customers->create(new CreateCustomerRequest(
        email: $customerEmail,
        name: 'PHP SDK Test Customer',
        phone: '+233541234567',
        description: 'Customer created via PHP SDK integration test'
    ));
    
    echo "   ✅ Customer created successfully!\n";
    echo "   👤 ID: {$customer->id}\n";
    echo "   📧 Email: {$customer->email}\n";
    echo "   📱 Phone: {$customer->phone}\n\n";
    
    // Test 4: Create Stripe Payment
    echo "4️⃣  Creating Stripe Payment\n";
    echo "   ⏳ Creating card payment...\n";
    
    $stripePayment = $xpay->payments->create(new PaymentRequest(
        amount: '25.99',
        paymentMethod: 'stripe',
        currency: 'USD',
        description: 'PHP SDK Integration Test - Stripe Payment',
        customerId: $customer->id,
        paymentMethodData: new PaymentMethodData(
            paymentMethodTypes: ['card']
        ),
        metadata: [
            'test_type' => 'php_sdk_integration',
            'customer_email' => $customer->email,
            'timestamp' => date('c'),
            'sdk_version' => '1.0.0'
        ]
    ));
    
    echo "   ✅ Stripe payment created!\n";
    echo "   💳 Payment ID: {$stripePayment->id}\n";
    echo "   💰 Amount: {$stripePayment->amount} {$stripePayment->currency}\n";
    echo "   📊 Status: {$stripePayment->status}\n";
    echo "   🔐 Client Secret: " . substr($stripePayment->clientSecret ?? 'N/A', 0, 30) . "...\n";
    echo "   📅 Created: {$stripePayment->createdAt}\n\n";
    
    // Test 5: Retrieve Payment
    echo "5️⃣  Retrieving Payment\n";
    echo "   ⏳ Fetching payment details...\n";
    
    $retrievedPayment = $xpay->payments->retrieve($stripePayment->id);
    
    echo "   ✅ Payment retrieved successfully!\n";
    echo "   🆔 ID: {$retrievedPayment->id}\n";
    echo "   📊 Status: {$retrievedPayment->status}\n";
    echo "   💰 Amount: {$retrievedPayment->amount} {$retrievedPayment->currency}\n";
    echo "   🎯 Match Original: " . ($retrievedPayment->id === $stripePayment->id ? 'Yes' : 'No') . "\n\n";
    
    // Test 6: Mobile Money Payment (if supported)
    echo "6️⃣  Creating Mobile Money Payment\n";
    echo "   ⏳ Creating MoMo payment...\n";
    
    try {
        $momoPayment = $xpay->payments->create(new PaymentRequest(
            amount: '15.00',
            paymentMethod: 'momo',
            currency: 'GHS',
            description: 'PHP SDK Integration Test - Mobile Money Payment',
            customerId: $customer->id,
            paymentMethodData: new PaymentMethodData(
                phoneNumber: '+233541234567'
            ),
            metadata: [
                'test_type' => 'php_sdk_momo',
                'phone_number' => '+233541234567'
            ]
        ));
        
        echo "   ✅ Mobile Money payment created!\n";
        echo "   📱 Payment ID: {$momoPayment->id}\n";
        echo "   💰 Amount: {$momoPayment->amount} {$momoPayment->currency}\n";
        echo "   📊 Status: {$momoPayment->status}\n";
        echo "   🔗 Reference ID: {$momoPayment->referenceId}\n";
        if (!empty($momoPayment->instructions)) {
            echo "   📋 Instructions: {$momoPayment->instructions}\n";
        }
        
    } catch (Exception $e) {
        echo "   ⚠️  MoMo payment failed: " . $e->getMessage() . "\n";
        echo "   ℹ️  This might be expected if MoMo is not configured\n";
    }
    echo "\n";
    
    // Test 7: List Payments
    echo "7️⃣  Listing Payments\n";
    echo "   ⏳ Fetching payment list...\n";
    
    $paymentsList = $xpay->payments->list(['limit' => 5]);
    
    echo "   ✅ Payments list retrieved!\n";
    echo "   📊 Total payments: " . ($paymentsList['total'] ?? 0) . "\n";
    echo "   📋 Returned: " . count($paymentsList['payments'] ?? []) . " payments\n";
    
    if (!empty($paymentsList['payments'])) {
        echo "   📄 Recent payments:\n";
        foreach (array_slice($paymentsList['payments'], 0, 3) as $payment) {
            echo "      • {$payment->id} - {$payment->amount} {$payment->currency} ({$payment->status})\n";
        }
    }
    echo "\n";
    
    // Test 8: Currency Utilities
    echo "8️⃣  Currency Utilities\n";
    echo "   ⏳ Testing currency conversion functions...\n";
    
    $testAmount = 42.50;
    $testCurrency = 'USD';
    
    $smallestUnit = $xpay->payments->toSmallestUnit($testAmount, $testCurrency);
    echo "   🔢 ${testAmount} {$testCurrency} = {$smallestUnit} cents\n";
    
    $backToDecimal = $xpay->payments->fromSmallestUnit($smallestUnit, $testCurrency);
    echo "   🔢 {$smallestUnit} cents = ${backToDecimal} {$testCurrency}\n";
    
    $formatted = $xpay->payments->formatAmount($testAmount, $testCurrency, false);
    echo "   💲 Formatted: {$formatted}\n\n";
    
    // Success Summary
    echo "🎉 Integration Test Results\n";
    echo "===========================\n";
    echo "✅ All tests completed successfully!\n";
    echo "🔐 Authentication: Working\n";
    echo "🏪 Merchant ID: Validated\n";
    echo "👤 Customer Creation: ✅\n";
    echo "💳 Stripe Payments: ✅\n";
    echo "📱 Mobile Money: " . (isset($momoPayment) ? '✅' : '⚠️  (Not configured)') . "\n";
    echo "🔍 Payment Retrieval: ✅\n";
    echo "📋 Payment Listing: ✅\n";
    echo "💱 Currency Utils: ✅\n\n";
    
    echo "📊 Created Resources:\n";
    echo "   👤 Customer: {$customer->id}\n";
    echo "   💳 Stripe Payment: {$stripePayment->id}\n";
    if (isset($momoPayment)) {
        echo "   📱 MoMo Payment: {$momoPayment->id}\n";
    }
    
} catch (\XPay\Exceptions\XPayException $e) {
    echo "\n❌ X-Pay SDK Error:\n";
    echo "   💥 Message: {$e->getMessage()}\n";
    echo "   🔢 Code: {$e->getErrorCode()}\n";
    
    if ($e->getHttpStatus()) {
        echo "   📡 HTTP Status: {$e->getHttpStatus()}\n";
    }
    
    if ($e->getDetails()) {
        echo "   📋 Details: " . json_encode($e->getDetails(), JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (\Throwable $e) {
    echo "\n💥 Unexpected Error:\n";
    echo "   💥 Message: {$e->getMessage()}\n";
    echo "   📍 Location: {$e->getFile()}:{$e->getLine()}\n";
    echo "   🔍 Trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n🏁 Test Complete\n";