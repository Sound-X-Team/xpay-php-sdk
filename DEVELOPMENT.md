# Development and Linting Guide

This document explains how to set up the development environment, run linting tools, and resolve common issues with the XPay PHP SDK.

## Quick Setup

```bash
# Clone and setup
git clone <repository-url>
cd php-sdk

# Install dependencies (requires Composer)
composer install

# Run all checks
make all

# Or use composer scripts
composer run all
```

## Available Commands

### Make Commands
```bash
make help          # Show all available targets
make install       # Install dependencies
make test          # Run unit tests
make test-coverage # Run tests with coverage
make lint          # Run all linting (PHPStan + CS)
make lint-fix      # Run linting and fix issues
make phpstan       # Run static analysis only
make cs-check      # Check code style only
make cs-fix        # Fix code style only
make clean         # Clean generated files
make all           # Run install, lint, and test
```

### Composer Scripts
```bash
composer run test          # Run PHPUnit tests
composer run test-coverage # Generate coverage report
composer run phpstan       # Run PHPStan analysis
composer run cs-check      # Check code style
composer run cs-fix        # Fix code style
composer run lint          # Run PHPStan + style check
composer run lint-fix      # Run PHPStan + style fix
composer run all           # Run lint + test
```

## IDE Setup for Development

The SDK includes comprehensive IDE support through the `ide-helper.php` file.

### PHPStorm Setup
1. Go to **Settings** → **PHP** → **Include Paths**
2. Add the `ide-helper.php` file path
3. The IDE will now recognize Laravel, Guzzle, PSR, and Money classes

### VSCode with Intelephense
Add to your workspace `.vscode/settings.json`:
```json
{
    "intelephense.files.associations": [
        "*.php",
        "**/ide-helper.php"
    ],
    "intelephense.stubs": [
        "apache",
        "bcmath",
        "bz2",
        "calendar",
        "com_dotnet",
        "Core",
        "ctype",
        "curl",
        "date",
        "dba",
        "dom",
        "enchant",
        "exif",
        "FFI",
        "fileinfo",
        "filter",
        "fpm",
        "ftp",
        "gd",
        "gettext",
        "gmp",
        "hash",
        "iconv",
        "imap",
        "intl",
        "json",
        "ldap",
        "libxml",
        "mbstring",
        "meta",
        "mysqli",
        "oci8",
        "odbc",
        "openssl",
        "pcntl",
        "pcre",
        "PDO",
        "pdo_ibm",
        "pdo_mysql",
        "pdo_pgsql",
        "pdo_sqlite",
        "pgsql",
        "Phar",
        "posix",
        "pspell",
        "readline",
        "Reflection",
        "session",
        "shmop",
        "SimpleXML",
        "snmp",
        "soap",
        "sockets",
        "sodium",
        "SPL",
        "sqlite3",
        "standard",
        "superglobals",
        "sysvmsg",
        "sysvsem",
        "sysvshm",
        "tidy",
        "tokenizer",
        "xml",
        "xmlreader",
        "xmlrpc",
        "xmlwriter",
        "xsl",
        "Zend OPcache",
        "zip",
        "zlib"
    ]
}
```

## Understanding the Lint Errors

### Laravel-Related Errors
These errors occur because Laravel is a **dev-dependency** and not required for core SDK functionality:

```
Undefined type 'Illuminate\Console\Command'
Undefined function 'config'
Undefined function 'env'
```

**Resolution**: These are expected when developing outside a Laravel project. The `ide-helper.php` provides stubs for development. In production Laravel projects, these classes will be available.

### Guzzle/PSR Errors
These occur when the IDE can't find the HTTP client interfaces:

```
Undefined type 'Psr\Http\Client\ClientInterface'
Undefined type 'GuzzleHttp\Client'
```

**Resolution**: These dependencies are properly defined in `composer.json`. The `ide-helper.php` provides stubs for development environments.

### Money Library Errors
```
Undefined type 'Money\Money'
Undefined type 'Money\Currency'
```

**Resolution**: The Money library is a required dependency. Ensure `composer install` has been run and the `ide-helper.php` is loaded in your IDE.

## Static Analysis Configuration

### PHPStan (phpstan.neon)
The PHPStan configuration ignores expected development-time issues:
- Laravel classes when not in a Laravel project
- Guzzle/PSR interfaces in development
- Money library in development environments

### PHP CS Fixer (.php-cs-fixer.php)
Enforces PSR-12 coding standards with additional rules:
- Short array syntax
- Ordered imports
- Proper spacing
- Trailing commas in multiline arrays

## Running Tests

### Unit Tests
```bash
# Run all tests
composer run test

# Run specific test suites
./vendor/bin/phpunit --testsuite=Unit
./vendor/bin/phpunit --testsuite=Integration
./vendor/bin/phpunit --testsuite=Laravel

# Run specific test files
./vendor/bin/phpunit tests/Unit/Http/ClientTest.php
```

### Test Coverage
```bash
# Generate HTML coverage report
composer run test-coverage

# View coverage
open coverage/index.html
```

## Continuous Integration

The project includes GitHub Actions CI that:
1. Tests against PHP 8.1, 8.2, and 8.3
2. Runs PHPStan static analysis
3. Checks code style compliance
4. Runs all unit tests
5. Generates coverage reports
6. Runs integration tests on main branch

## Dependency Management

### Required Dependencies
- **PHP 8.1+**: Modern PHP features and type system
- **ext-json**: JSON handling
- **ext-openssl**: Cryptographic operations for webhooks
- **guzzlehttp/guzzle**: HTTP client
- **psr/http-client**: HTTP client interface
- **moneyphp/money**: Currency handling
- **respect/validation**: Input validation

### Development Dependencies
- **phpunit/phpunit**: Testing framework
- **phpstan/phpstan**: Static analysis
- **friendsofphp/php-cs-fixer**: Code style fixing
- **orchestra/testbench**: Laravel testing tools
- **laravel/framework**: Laravel integration (dev-only)

## Troubleshooting

### "Class not found" errors
1. Ensure `composer install` has been run
2. Check that `ide-helper.php` is loaded in your IDE
3. Verify your IDE is using the correct PHP interpreter

### Laravel integration issues
1. Laravel components require Laravel framework to be installed
2. Use the SDK in a Laravel project for full Laravel features
3. Test Laravel integration using Orchestra Testbench

### Performance issues with static analysis
1. PHPStan may be slow on first run - subsequent runs are cached
2. Exclude vendor directories if analyzing large codebases
3. Use `--memory-limit=1G` for large projects

### Code style conflicts
1. Run `composer run cs-fix` to auto-fix most issues
2. Check `.php-cs-fixer.php` configuration for custom rules
3. Some rules may conflict with your team's preferences - adjust config as needed

## Contributing

When contributing to the SDK:
1. Run `make all` before submitting PRs
2. Ensure all tests pass
3. Follow PSR-12 coding standards
4. Add tests for new functionality
5. Update documentation as needed

## Production Deployment

In production environments:
1. Use `composer install --no-dev` to exclude development dependencies
2. Do not include `ide-helper.php` in production builds
3. Set appropriate environment variables for X-Pay configuration
4. Enable proper error logging and monitoring