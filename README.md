# X-Pay PHP SDK

The official PHP SDK for the X-Pay payment processing platform. This SDK provides a simple and intuitive interface for integrating X-Pay's payment services into your PHP applications, with special support for Laravel.

[![Latest Version](https://img.shields.io/packagist/v/xpay/php-sdk.svg)](https://packagist.org/packages/xpay/php-sdk)
[![PHP Version](https://img.shields.io/packagist/php-v/xpay/php-sdk.svg)](https://packagist.org/packages/xpay/php-sdk)
[![License](https://img.shields.io/packagist/l/xpay/php-sdk.svg)](https://packagist.org/packages/xpay/php-sdk)

## Features

- 🚀 **Easy Integration**: Simple, intuitive API design
- 💳 **Multiple Payment Methods**: Stripe, Mobile Money (Ghana, Liberia, Nigeria, Uganda, Rwanda), X-Pay Wallet
- 🏗️ **Laravel Support**: Service provider, facade, middleware, and Artisan commands
- 🔐 **Secure**: Built-in signature verification for webhooks
- 💰 **Currency Utilities**: Proper handling of currency conversion and formatting
- 🛡️ **Type Safety**: Full PHP 8.1+ type hints and readonly classes
- ✅ **Well Tested**: Comprehensive unit and integration tests
- 📖 **Documentation**: Complete API documentation and examples

## Requirements

- PHP 8.1 or higher
- ext-json
- ext-openssl
- Laravel 10+ (for Laravel integration features)

## Installation

Install via Composer:

```bash
composer require xpay/php-sdk
```

### Laravel Integration

If you're using Laravel, the service provider will be auto-discovered. Publish the configuration file:

```bash
php artisan vendor:publish --tag=xpay-config
```

Add your X-Pay credentials to your `.env` file:

```env
XPAY_API_KEY=xpay_sandbox_your_api_key_here
XPAY_ENVIRONMENT=sandbox
XPAY_WEBHOOK_SECRET=your_webhook_secret_here
```

## Quick Start

### Basic Usage

```php
use XPay\Types\XPayConfig;
use XPay\Types\PaymentRequest;
use XPay\Types\PaymentMethodData;
use XPay\XPay;

// Initialize the client
$config = new XPayConfig(
    apiKey: 'xpay_sandbox_your_api_key_here',
    environment: 'sandbox'
);

$xpay = new XPay($config);

// Create a payment
$payment = $xpay->payments->create(new PaymentRequest(
    amount: '10.00',
    paymentMethod: 'stripe',
    currency: 'USD',
    description: 'Test payment',
    paymentMethodData: new PaymentMethodData(
        paymentMethodTypes: ['card']
    )
));

echo "Payment ID: {$payment->id}\n";
echo "Status: {$payment->status}\n";
echo "Client Secret: {$payment->clientSecret}\n";
```

### Laravel Usage

```php
use XPay\Laravel\Facades\XPay;
use XPay\Types\PaymentRequest;

// Using the facade
$payment = XPay::payments()->create(new PaymentRequest(
    amount: '25.00',
    paymentMethod: 'momo',
    currency: 'GHS',
    description: 'Mobile money payment'
));

// Test API connectivity
php artisan xpay:test

// List available payment methods
php artisan xpay:payment-methods
```

## Payment Methods

### Stripe Payments

```php
$payment = $xpay->payments->create(new PaymentRequest(
    amount: '10.00',
    paymentMethod: 'stripe',
    currency: 'USD',
    paymentMethodData: new PaymentMethodData(
        paymentMethodTypes: ['card']
    ),
    successUrl: 'https://yourapp.com/success',
    cancelUrl: 'https://yourapp.com/cancel'
));
```

### Mobile Money

```php
// Ghana Mobile Money
$payment = $xpay->payments->create(new PaymentRequest(
    amount: '50.00',
    paymentMethod: 'momo',
    currency: 'GHS',
    paymentMethodData: new PaymentMethodData(
        phoneNumber: '+233541234567'
    )
));

// Other countries
$payment = $xpay->payments->create(new PaymentRequest(
    amount: '25.00',
    paymentMethod: 'momo_nigeria', // or momo_liberia, momo_uganda, momo_rwanda
    currency: 'USD',
    paymentMethodData: new PaymentMethodData(
        phoneNumber: '+2341234567890'
    )
));
```

### X-Pay Wallet

```php
$payment = $xpay->payments->create(new PaymentRequest(
    amount: '15.00',
    paymentMethod: 'xpay_wallet',
    currency: 'USD',
    paymentMethodData: new PaymentMethodData(
        walletId: 'wallet_123',
        pin: '1234'
    )
));
```

## Customer Management

```php
use XPay\Types\CreateCustomerRequest;

// Create a customer
$customer = $xpay->customers->create(new CreateCustomerRequest(
    email: 'john@example.com',
    name: 'John Doe',
    phone: '+1234567890',
    metadata: ['source' => 'website']
));

// Retrieve a customer
$customer = $xpay->customers->retrieve('cust_123');

// Update a customer
$customer = $xpay->customers->update('cust_123', [
    'phone' => '+1987654321',
    'metadata' => ['updated' => true]
]);

// List customers
$result = $xpay->customers->list([
    'limit' => 20,
    'email' => 'john@example.com'
]);
```

## Webhook Management

```php
use XPay\Types\CreateWebhookRequest;

// Create a webhook
$webhook = $xpay->webhooks->create(new CreateWebhookRequest(
    url: 'https://yourapp.com/webhooks/xpay',
    events: ['payment.succeeded', 'payment.failed']
));

// Verify webhook signature
$isValid = $xpay->webhooks->verifySignature(
    $payload,
    $signature,
    $webhook->secret
);

// Parse webhook payload
$event = $xpay->webhooks->parsePayload($payload);
```

## Laravel Webhook Handling

### Register the Middleware

Add to your `app/Http/Kernel.php`:

```php
protected $routeMiddleware = [
    // ...
    'xpay.webhook' => \XPay\Laravel\Middleware\VerifyWebhookSignature::class,
];
```

### Create a Webhook Controller

```php
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    public function handle(Request $request): Response
    {
        $webhookData = $request->input('webhook_data');
        
        match ($webhookData['type']) {
            'payment.succeeded' => $this->handlePaymentSucceeded($webhookData['data']),
            'payment.failed' => $this->handlePaymentFailed($webhookData['data']),
            default => logger()->info('Unhandled webhook event', ['type' => $webhookData['type']])
        };

        return response('OK', 200);
    }
    
    private function handlePaymentSucceeded(array $data): void
    {
        // Handle successful payment
        $payment = $data['payment'];
        // Update order status, send confirmation email, etc.
    }
}
```

### Register the Route

```php
// routes/api.php
Route::post('/webhooks/xpay', [WebhookController::class, 'handle'])
    ->middleware('xpay.webhook');
```

## Currency Utilities

```php
use XPay\Utils\CurrencyUtils;

// Convert to smallest unit (cents)
$cents = CurrencyUtils::toSmallestUnit(10.50, 'USD'); // 1050

// Convert from smallest unit
$dollars = CurrencyUtils::fromSmallestUnit(1050, 'USD'); // 10.50

// Format for display
$formatted = CurrencyUtils::formatAmount(10.50, 'USD'); // $10.50

// Check supported currencies for payment method
$currencies = CurrencyUtils::getSupportedCurrencies('stripe'); // ['USD', 'EUR', 'GBP', 'GHS']

// Validate currency for payment method
CurrencyUtils::validateCurrency('momo', 'GHS'); // OK
CurrencyUtils::validateCurrency('momo', 'USD'); // Throws ValidationException
```

## Error Handling

The SDK provides structured error handling with specific exception types:

```php
use XPay\Exceptions\XPayException;
use XPay\Exceptions\AuthenticationException;
use XPay\Exceptions\ValidationException;
use XPay\Exceptions\NetworkException;

try {
    $payment = $xpay->payments->create($paymentRequest);
} catch (AuthenticationException $e) {
    // Handle authentication errors (invalid API key, etc.)
    echo "Auth Error: {$e->getMessage()}";
} catch (ValidationException $e) {
    // Handle validation errors (invalid input data)
    echo "Validation Error: {$e->getMessage()}";
    echo "Details: " . json_encode($e->getDetails());
} catch (NetworkException $e) {
    // Handle network connectivity issues
    echo "Network Error: {$e->getMessage()}";
} catch (XPayException $e) {
    // Handle other X-Pay specific errors
    echo "X-Pay Error: {$e->getMessage()}";
    echo "Error Code: {$e->getErrorCode()}";
    echo "HTTP Status: {$e->getHttpStatus()}";
}
```

## Testing

Run the test suite:

```bash
composer test
```

Run tests with coverage:

```bash
composer test-coverage
```

Run static analysis:

```bash
composer phpstan
```

## Laravel Testing

```bash
# Test API connectivity
php artisan xpay:test

# List payment methods
php artisan xpay:payment-methods
```

## Configuration

### Standalone Configuration

```php
$config = new XPayConfig(
    apiKey: 'your_api_key',
    merchantId: 'merchant_123', // Optional
    environment: 'sandbox', // or 'live'
    baseUrl: 'https://custom-api.com', // Optional
    timeout: 30 // seconds
);
```

### Laravel Configuration

The configuration file (`config/xpay.php`) supports:

```php
return [
    'api_key' => env('XPAY_API_KEY'),
    'merchant_id' => env('XPAY_MERCHANT_ID'),
    'environment' => env('XPAY_ENVIRONMENT', 'sandbox'),
    'base_url' => env('XPAY_BASE_URL'),
    'timeout' => env('XPAY_TIMEOUT', 30),
    
    'webhook' => [
        'secret' => env('XPAY_WEBHOOK_SECRET'),
        'tolerance' => env('XPAY_WEBHOOK_TOLERANCE', 300),
        'verify_signature' => env('XPAY_WEBHOOK_VERIFY_SIGNATURE', true),
    ],
];
```

## Environment Detection

The SDK automatically detects the environment from your API key prefix:

- `xpay_sandbox_*` or `pk_sandbox_*` → sandbox
- `xpay_live_*` or `pk_live_*` → live
- Unknown format → defaults to sandbox

## Examples

See the [`examples/`](examples/) directory for complete working examples:

- [Basic Payment Processing](examples/basic-payment.php)
- [Webhook Management](examples/webhooks.php)
- [Customer Management](examples/customers.php)
- [Laravel Payment Controller](examples/laravel-payment-controller.php)
- [Laravel Webhook Controller](examples/laravel-webhook-controller.php)

## API Reference

### Core Classes

- `XPay` - Main client class
- `XPayConfig` - Configuration object
- `Payments` - Payment operations
- `Webhooks` - Webhook management
- `Customers` - Customer management

### Data Types

- `PaymentRequest` - Payment creation data
- `Payment` - Payment object
- `CreateWebhookRequest` - Webhook creation data
- `WebhookEndpoint` - Webhook object
- `CreateCustomerRequest` - Customer creation data
- `Customer` - Customer object

### Utilities

- `CurrencyUtils` - Currency conversion and validation
- `WebhookUtils` - Webhook signature verification
- `VerifyWebhookSignature` - Laravel middleware

## Contributing

We welcome contributions! Please see our [Contributing Guide](CONTRIBUTING.md) for details.

### Development Setup

```bash
git clone https://github.com/xpay/php-sdk.git
cd php-sdk
composer install
composer test
```

### IDE Support

The SDK includes an `ide-helper.php` file to provide better IDE support during development. If your IDE shows errors about missing Laravel classes when not in a Laravel project, you can include this file in your IDE configuration.

For PhpStorm:
1. Go to Settings → PHP → Include Paths
2. Add the `ide-helper.php` file

For VSCode with Intelephense:
1. Add to your workspace settings:
```json
{
    "intelephense.stubs": ["../ide-helper.php"]
}
```

**Note**: The Laravel-related warnings will be resolved when the SDK is used in an actual Laravel project with Laravel dependencies installed.

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for release history.

## Security

If you discover any security vulnerabilities, please email security@xpay.com instead of using the issue tracker.

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- 📧 Email: developers@xpay.com
- 📖 Documentation: https://docs.xpay.com
- 🐛 Issues: https://github.com/xpay/php-sdk/issues
- 💬 Community: https://community.xpay.com

---

Made with ❤️ by the X-Pay team