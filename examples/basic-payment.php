<?php

require_once __DIR__ . '/../vendor/autoload.php';

use XPay\Types\PaymentMethodData;
use XPay\Types\PaymentRequest;
use XPay\Types\CreateCustomerRequest;
use XPay\Types\XPayConfig;
use XPay\XPay;

// Initialize X-Pay client
$config = new XPayConfig(
    apiKey: 'xpay_sandbox_test_your_api_key_here',
    environment: 'sandbox'
);

$xpay = new XPay($config);

try {
    echo "Testing X-Pay API connectivity...\n";
    
    // Test API connection
    $ping = $xpay->ping();
    echo "API Connection: " . ($ping['success'] ? "✅ Success" : "❌ Failed") . "\n";
    echo "Timestamp: {$ping['timestamp']}\n\n";
    
    // Get available payment methods
    echo "Available payment methods:\n";
    $paymentMethods = $xpay->getPaymentMethods();
    
    foreach ($paymentMethods['payment_methods'] as $method) {
        $status = $method['enabled'] ? '✅' : '❌';
        echo "  {$status} {$method['name']} ({$method['type']})\n";
        echo "     Currencies: " . implode(', ', $method['currencies']) . "\n";
    }
    echo "\n";
    
    // Create a customer first
    echo "Creating customer...\n";
    $customer = $xpay->customers->create(new CreateCustomerRequest(
        email: 'test-customer-' . bin2hex(random_bytes(4)) . '@example.com',
        name: 'Test Customer',
        phone: '+1234567890',
        description: 'Customer for example payments'
    ));
    
    echo "Customer created: {$customer->id}\n";
    echo "Customer email: {$customer->email}\n\n";
    
    // Create a test payment with Stripe
    echo "Creating Stripe payment...\n";
    $stripePayment = $xpay->payments->create(new PaymentRequest(
        amount: '10.00',
        paymentMethod: 'stripe',
        currency: 'USD',
        description: 'Test payment from PHP SDK',
        customerId: $customer->id,
        paymentMethodData: new PaymentMethodData(
            paymentMethodTypes: ['card']
        ),
        metadata: [
            'order_id' => 'order_123',
            'customer_email' => $customer->email
        ]
    ));
    
    echo "Payment created: {$stripePayment->id}\n";
    echo "Status: {$stripePayment->status}\n";
    echo "Amount: {$stripePayment->amount} {$stripePayment->currency}\n";
    echo "Client Secret: {$stripePayment->clientSecret}\n\n";
    
    // Create a test payment with Mobile Money (Ghana)
    echo "Creating Mobile Money payment...\n";
    $momoPayment = $xpay->payments->create(new PaymentRequest(
        amount: '50.00',
        paymentMethod: 'momo',
        currency: 'GHS',
        description: 'Test MoMo payment from PHP SDK',
        customerId: $customer->id,
        paymentMethodData: new PaymentMethodData(
            phoneNumber: '+233541234567'
        )
    ));
    
    echo "Payment created: {$momoPayment->id}\n";
    echo "Status: {$momoPayment->status}\n";
    echo "Amount: {$momoPayment->amount} {$momoPayment->currency}\n";
    echo "Reference ID: {$momoPayment->referenceId}\n";
    echo "Instructions: {$momoPayment->instructions}\n\n";
    
    // Retrieve the payment
    echo "Retrieving payment...\n";
    $retrievedPayment = $xpay->payments->retrieve($stripePayment->id);
    echo "Retrieved payment: {$retrievedPayment->id}\n";
    echo "Current status: {$retrievedPayment->status}\n\n";
    
    // Demonstrate currency utilities
    echo "Currency utilities:\n";
    $amount = 12.50;
    $currency = 'USD';
    
    $smallestUnit = $xpay->payments->toSmallestUnit($amount, $currency);
    echo "Amount in smallest unit: $smallestUnit cents\n";
    
    $backToDecimal = $xpay->payments->fromSmallestUnit($smallestUnit, $currency);
    echo "Back to decimal: $backToDecimal\n";
    
    $formatted = $xpay->payments->formatAmount($amount, $currency, false);
    echo "Formatted: $formatted\n";
    
} catch (\XPay\Exceptions\XPayException $e) {
    echo "X-Pay Error: {$e->getMessage()}\n";
    echo "Error Code: {$e->getErrorCode()}\n";
    
    if ($e->getHttpStatus()) {
        echo "HTTP Status: {$e->getHttpStatus()}\n";
    }
    
    if ($e->getDetails()) {
        echo "Details: " . json_encode($e->getDetails(), JSON_PRETTY_PRINT) . "\n";
    }
} catch (\Throwable $e) {
    echo "Unexpected error: {$e->getMessage()}\n";
}