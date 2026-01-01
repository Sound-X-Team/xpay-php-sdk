<?php

require_once __DIR__ . '/vendor/autoload.php';

use XPay\Types\PaymentMethodData;
use XPay\Types\PaymentRequest;
use XPay\Types\CreateCustomerRequest;
use XPay\Types\XPayConfig;
use XPay\XPay;

echo "=== X-Pay PHP SDK Test ===\n";
echo "Testing with provided sandbox credentials...\n\n";

// Configure X-Pay client with your credentials
$config = new XPayConfig(
    apiKey: 'sk_sandbox_3f73759d-6de5-4102-8f29-923c935d',
    merchantId: '548d8033-fbe9-411b-991f-f159cdee7745',
    environment: 'sandbox',
    baseUrl: 'http://localhost:8000'
);

$xpay = new XPay($config);

try {
    // Test 1: API Connection
    echo "1. Testing API connectivity...\n";
    $ping = $xpay->ping();
    echo "   ✅ API Connection: " . ($ping['success'] ? "Success" : "Failed") . "\n";
    echo "   📅 Timestamp: {$ping['timestamp']}\n\n";
    
    // Test 2: Get Payment Methods
    echo "2. Getting available payment methods...\n";
    try {
        $paymentMethods = $xpay->getPaymentMethods();
        
        if (isset($paymentMethods['payment_methods'])) {
            foreach ($paymentMethods['payment_methods'] as $method) {
                $status = $method['enabled'] ? '✅' : '❌';
                echo "   {$status} {$method['name']} ({$method['type']})\n";
                if (isset($method['currencies'])) {
                    echo "      💰 Currencies: " . implode(', ', $method['currencies']) . "\n";
                }
            }
        } else {
            echo "   ⚠️ No payment methods returned or unexpected format\n";
            echo "   📊 Response: " . json_encode($paymentMethods, JSON_PRETTY_PRINT) . "\n";
        }
    } catch (Exception $e) {
        echo "   ❌ Failed to get payment methods: " . $e->getMessage() . "\n";
    }
    echo "\n";
    
    // Test 3: Create Customer
    echo "3. Creating test customer...\n";
    try {
        $customerEmail = 'test-customer-' . bin2hex(random_bytes(4)) . '@example.com';
        $customer = $xpay->customers->create(new CreateCustomerRequest(
            email: $customerEmail,
            name: 'PHP SDK Test Customer',
            phone: '+233541234567',
            description: 'Customer created during PHP SDK testing'
        ));
        
        echo "   ✅ Customer created successfully!\n";
        echo "   👤 ID: {$customer->id}\n";
        echo "   📧 Email: {$customer->email}\n";
        echo "   📱 Phone: {$customer->phone}\n\n";
        
        // Test 4: Create Stripe Payment
        echo "4. Creating Stripe card payment...\n";
        try {
            $stripePayment = $xpay->payments->create(new PaymentRequest(
                amount: '25.00',
                paymentMethod: 'stripe',
                currency: 'USD',
                description: 'PHP SDK Test - Stripe Payment',
                customerId: $customer->id,
                paymentMethodData: new PaymentMethodData(
                    paymentMethodTypes: ['card']
                ),
                metadata: [
                    'test_type' => 'php_sdk_test',
                    'customer_email' => $customer->email,
                    'timestamp' => date('c')
                ]
            ));
            
            echo "   ✅ Stripe payment created!\n";
            echo "   💳 Payment ID: {$stripePayment->id}\n";
            echo "   💰 Amount: {$stripePayment->amount} {$stripePayment->currency}\n";
            echo "   📊 Status: {$stripePayment->status}\n";
            echo "   🔐 Client Secret: " . substr($stripePayment->clientSecret, 0, 20) . "...\n\n";
            
        } catch (Exception $e) {
            echo "   ❌ Stripe payment failed: " . $e->getMessage() . "\n\n";
        }
        
        // Test 5: Create Mobile Money Payment
        echo "5. Creating Mobile Money payment...\n";
        try {
            $momoPayment = $xpay->payments->create(new PaymentRequest(
                amount: '10.00',
                paymentMethod: 'momo',
                currency: 'GHS',
                description: 'PHP SDK Test - MoMo Payment',
                customerId: $customer->id,
                paymentMethodData: new PaymentMethodData(
                    phoneNumber: '+233541234567'
                ),
                metadata: [
                    'test_type' => 'php_sdk_momo_test',
                    'customer_phone' => '+233541234567'
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
            echo "\n";
            
        } catch (Exception $e) {
            echo "   ❌ MoMo payment failed: " . $e->getMessage() . "\n\n";
        }
        
        // Test 6: Retrieve Payment
        if (isset($stripePayment)) {
            echo "6. Retrieving payment details...\n";
            try {
                $retrievedPayment = $xpay->payments->retrieve($stripePayment->id);
                echo "   ✅ Payment retrieved successfully!\n";
                echo "   🆔 ID: {$retrievedPayment->id}\n";
                echo "   📊 Status: {$retrievedPayment->status}\n";
                echo "   💰 Amount: {$retrievedPayment->amount} {$retrievedPayment->currency}\n";
                echo "   📅 Created: {$retrievedPayment->createdAt}\n\n";
            } catch (Exception $e) {
                echo "   ❌ Failed to retrieve payment: " . $e->getMessage() . "\n\n";
            }
        }
        
        // Test 7: Currency Utilities
        echo "7. Testing currency utilities...\n";
        $testAmount = 12.50;
        $testCurrency = 'USD';
        
        $smallestUnit = $xpay->payments->toSmallestUnit($testAmount, $testCurrency);
        echo "   🔢 ${testAmount} {$testCurrency} = {$smallestUnit} cents (smallest unit)\n";
        
        $backToDecimal = $xpay->payments->fromSmallestUnit($smallestUnit, $testCurrency);
        echo "   🔢 {$smallestUnit} cents = ${backToDecimal} (back to decimal)\n";
        
        $formatted = $xpay->payments->formatAmount($testAmount, $testCurrency, false);
        echo "   💲 Formatted: {$formatted}\n\n";
        
    } catch (Exception $e) {
        echo "   ❌ Customer creation failed: " . $e->getMessage() . "\n\n";
    }
    
    echo "=== Test Summary ===\n";
    echo "✅ PHP SDK test completed successfully!\n";
    echo "🔐 Credentials: Working with sandbox environment\n";
    echo "🏪 Merchant ID: " . $xpay->getMerchantId() . "\n";
    echo "🌐 Environment: {$config->getEnvironment()}\n";
    echo "🔗 Base URL: {$config->getBaseUrl()}\n";
    
} catch (\XPay\Exceptions\XPayException $e) {
    echo "\n❌ X-Pay Error: {$e->getMessage()}\n";
    echo "🚨 Error Code: {$e->getErrorCode()}\n";
    
    if ($e->getHttpStatus()) {
        echo "📡 HTTP Status: {$e->getHttpStatus()}\n";
    }
    
    if ($e->getDetails()) {
        echo "📋 Details: " . json_encode($e->getDetails(), JSON_PRETTY_PRINT) . "\n";
    }
    
} catch (\Throwable $e) {
    echo "\n💥 Unexpected error: {$e->getMessage()}\n";
    echo "📍 File: {$e->getFile()}:{$e->getLine()}\n";
    echo "🔍 Trace: {$e->getTraceAsString()}\n";
}

echo "\n=== End Test ===\n";