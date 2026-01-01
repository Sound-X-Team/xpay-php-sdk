<?php

require_once __DIR__ . '/../vendor/autoload.php';

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
    echo "Managing customers...\n\n";
    
    // Create a customer
    echo "Creating customer...\n";
    $customer = $xpay->customers->create(new CreateCustomerRequest(
        email: 'john.doe@example.com',
        name: 'John Doe',
        phone: '+1234567890',
        description: 'Test customer from PHP SDK',
        metadata: [
            'source' => 'php_sdk_example',
            'signup_date' => date('Y-m-d')
        ]
    ));
    
    echo "Customer created: {$customer->id}\n";
    echo "Name: {$customer->name}\n";
    echo "Email: {$customer->email}\n";
    echo "Phone: {$customer->phone}\n\n";
    
    // Retrieve the customer
    echo "Retrieving customer...\n";
    $retrievedCustomer = $xpay->customers->retrieve($customer->id);
    echo "Retrieved customer: {$retrievedCustomer->name} ({$retrievedCustomer->email})\n\n";
    
    // Update the customer
    echo "Updating customer...\n";
    $updatedCustomer = $xpay->customers->update($customer->id, [
        'phone' => '+1987654321',
        'description' => 'Updated test customer',
        'metadata' => [
            'source' => 'php_sdk_example',
            'signup_date' => date('Y-m-d'),
            'last_updated' => date('Y-m-d H:i:s')
        ]
    ]);
    
    echo "Updated customer phone: {$updatedCustomer->phone}\n\n";
    
    // Create another customer for listing demo
    $customer2 = $xpay->customers->create(new CreateCustomerRequest(
        email: 'jane.smith@example.com',
        name: 'Jane Smith'
    ));
    
    // List customers
    echo "Listing customers...\n";
    $customers = $xpay->customers->list([
        'limit' => 10
    ]);
    
    echo "Total customers: {$customers['total']}\n";
    echo "Has more: " . ($customers['has_more'] ? 'Yes' : 'No') . "\n\n";
    
    foreach ($customers['customers'] as $cust) {
        echo "  ID: {$cust->id}\n";
        echo "  Name: {$cust->name}\n";
        echo "  Email: {$cust->email}\n";
        echo "  Created: " . $cust->createdAt?->format('Y-m-d H:i:s') . "\n";
        echo "  ---\n";
    }
    echo "\n";
    
    // Search customers by email
    echo "Searching customers by email...\n";
    $searchResults = $xpay->customers->list([
        'email' => 'john.doe@example.com'
    ]);
    
    echo "Search results: " . count($searchResults['customers']) . " customers\n";
    foreach ($searchResults['customers'] as $cust) {
        echo "  Found: {$cust->name} ({$cust->email})\n";
    }
    echo "\n";
    
    // Clean up - delete test customers
    echo "Cleaning up...\n";
    $deleted1 = $xpay->customers->delete($customer->id);
    $deleted2 = $xpay->customers->delete($customer2->id);
    
    echo "Customer 1 deleted: " . ($deleted1 ? '✅ Yes' : '❌ No') . "\n";
    echo "Customer 2 deleted: " . ($deleted2 ? '✅ Yes' : '❌ No') . "\n";
    
} catch (\XPay\Exceptions\XPayException $e) {
    echo "X-Pay Error: {$e->getMessage()}\n";
    echo "Error Code: {$e->getErrorCode()}\n";
    
    if ($e->getHttpStatus()) {
        echo "HTTP Status: {$e->getHttpStatus()}\n";
    }
} catch (\Throwable $e) {
    echo "Unexpected error: {$e->getMessage()}\n";
}