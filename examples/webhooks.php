<?php

require_once __DIR__ . '/../vendor/autoload.php';

use XPay\Types\CreateWebhookRequest;
use XPay\Types\XPayConfig;
use XPay\XPay;

// Initialize X-Pay client
$config = new XPayConfig(
    apiKey: 'xpay_sandbox_test_your_api_key_here',
    environment: 'sandbox'
);

$xpay = new XPay($config);

try {
    echo "Managing webhooks...\n\n";
    
    // Create a webhook endpoint
    echo "Creating webhook endpoint...\n";
    $webhook = $xpay->webhooks->create(new CreateWebhookRequest(
        url: 'https://your-app.com/webhooks/xpay',
        events: [
            'payment.succeeded',
            'payment.failed',
            'payment.cancelled'
        ],
        description: 'Main webhook endpoint for payment events'
    ));
    
    echo "Webhook created: {$webhook->id}\n";
    echo "URL: {$webhook->url}\n";
    echo "Events: " . implode(', ', $webhook->events) . "\n";
    echo "Secret: {$webhook->secret}\n";
    echo "Active: " . ($webhook->isActive ? 'Yes' : 'No') . "\n\n";
    
    // List all webhooks
    echo "Listing all webhooks...\n";
    $webhooks = $xpay->webhooks->list();
    
    foreach ($webhooks as $wh) {
        echo "  ID: {$wh->id}\n";
        echo "  URL: {$wh->url}\n";
        echo "  Events: " . implode(', ', $wh->events) . "\n";
        echo "  Active: " . ($wh->isActive ? 'Yes' : 'No') . "\n";
        echo "  ---\n";
    }
    echo "\n";
    
    // Test webhook signature verification
    echo "Testing webhook signature verification...\n";
    
    $webhookPayload = json_encode([
        'id' => 'evt_test123',
        'type' => 'payment.succeeded',
        'created_at' => '2023-12-07T10:30:00Z',
        'data' => [
            'payment' => [
                'id' => 'pay_test123',
                'status' => 'succeeded',
                'amount' => '10.00',
                'currency' => 'USD'
            ]
        ]
    ]);
    
    $signature = hash_hmac('sha256', $webhookPayload, $webhook->secret);
    $isValid = $xpay->webhooks->verifySignature($webhookPayload, 'sha256=' . $signature, $webhook->secret);
    
    echo "Signature verification: " . ($isValid ? '✅ Valid' : '❌ Invalid') . "\n";
    
    // Parse webhook payload
    $parsedPayload = $xpay->webhooks->parsePayload($webhookPayload);
    echo "Parsed event type: {$parsedPayload['type']}\n";
    echo "Payment ID: {$parsedPayload['data']['payment']['id']}\n\n";
    
    // Test the webhook endpoint
    echo "Testing webhook endpoint...\n";
    $testResult = $xpay->webhooks->test($webhook->id);
    echo "Test result: " . ($testResult['success'] ? '✅ Success' : '❌ Failed') . "\n\n";
    
    // Update webhook
    echo "Updating webhook...\n";
    $updatedWebhook = $xpay->webhooks->update($webhook->id, [
        'events' => [
            'payment.succeeded',
            'payment.failed',
            'payment.cancelled',
            'payment.refunded'
        ]
    ]);
    
    echo "Updated events: " . implode(', ', $updatedWebhook->events) . "\n\n";
    
    // Get supported events
    echo "Supported webhook events:\n";
    $supportedEvents = $xpay->webhooks->getSupportedEvents();
    foreach ($supportedEvents as $event) {
        echo "  - {$event}\n";
    }
    
    // Clean up - delete the test webhook
    echo "\nCleaning up...\n";
    $deleted = $xpay->webhooks->delete($webhook->id);
    echo "Webhook deleted: " . ($deleted ? '✅ Yes' : '❌ No') . "\n";
    
} catch (\XPay\Exceptions\XPayException $e) {
    echo "X-Pay Error: {$e->getMessage()}\n";
    echo "Error Code: {$e->getErrorCode()}\n";
    
    if ($e->getHttpStatus()) {
        echo "HTTP Status: {$e->getHttpStatus()}\n";
    }
} catch (\Throwable $e) {
    echo "Unexpected error: {$e->getMessage()}\n";
}