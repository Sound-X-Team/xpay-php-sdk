# Changelog

All notable changes to the X-Pay PHP SDK will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2023-12-07

### Added
- Initial release of X-Pay PHP SDK
- Core payment processing functionality
- Support for multiple payment methods:
  - Stripe payments
  - Mobile Money (Ghana, Liberia, Nigeria, Uganda, Rwanda)
  - X-Pay Wallet
- Customer management (CRUD operations)
- Webhook management and signature verification
- Currency utilities with proper precision handling
- Laravel integration with:
  - Service provider and facade
  - Configuration file
  - Middleware for webhook verification
  - Artisan commands for API testing
- Comprehensive error handling with typed exceptions
- PHP 8.1+ support with full type safety
- PSR-4 autoloading and PSR-18 HTTP client interface
- Complete test suite with PHPUnit
- Money pattern for currency handling
- Environment detection from API key prefix
- Request timeout and retry mechanisms
- Structured API response handling

### Dependencies
- PHP ^8.1
- guzzlehttp/guzzle ^7.0
- moneyphp/money ^4.0
- respect/validation ^2.2

### Laravel Support
- Laravel ^10.0 compatibility
- Auto-discovery of service provider
- Configuration publishing
- Middleware for webhook verification
- Artisan commands for testing

### Security
- HMAC-SHA256 webhook signature verification
- Constant-time signature comparison
- SSL/TLS verification for API requests
- Input validation and sanitization

### Documentation
- Complete README with examples
- API reference documentation  
- Laravel integration guide
- Currency utilities documentation
- Error handling guide
- Contributing guidelines

### Examples
- Basic payment processing
- Webhook handling
- Customer management
- Laravel controller examples
- Currency conversion examples