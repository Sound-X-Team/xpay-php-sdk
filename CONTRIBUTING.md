# Contributing to X-Pay PHP SDK

We love your input! We want to make contributing to the X-Pay PHP SDK as easy and transparent as possible, whether it's:

- Reporting a bug
- Discussing the current state of the code
- Submitting a fix
- Proposing new features
- Becoming a maintainer

## Development Process

We use GitHub to host code, to track issues and feature requests, as well as accept pull requests.

1. Fork the repo and create your branch from `main`
2. If you've added code that should be tested, add tests
3. If you've changed APIs, update the documentation
4. Ensure the test suite passes
5. Make sure your code lints
6. Issue that pull request!

## Development Setup

```bash
# Clone your fork
git clone https://github.com/your-username/php-sdk.git
cd php-sdk

# Install dependencies
composer install

# Run tests
composer test

# Run static analysis
composer phpstan

# Fix code style
composer cs-fix
```

## Code Standards

- Follow PSR-12 coding standards
- Use PHP 8.1+ features where appropriate
- Write comprehensive tests for new functionality
- Use type hints for all parameters and return types
- Use readonly classes where appropriate
- Follow existing patterns in the codebase

### Code Style

We use PHP-CS-Fixer to maintain code style. Run before committing:

```bash
composer cs-fix
```

### Static Analysis

We use PHPStan for static analysis:

```bash
composer phpstan
```

## Testing

### Running Tests

```bash
# Run all tests
composer test

# Run with coverage
composer test-coverage

# Run specific test file
./vendor/bin/phpunit tests/Unit/XPayConfigTest.php

# Run specific test method
./vendor/bin/phpunit --filter testConstructorRequiresApiKey
```

### Writing Tests

- Write unit tests for all new functionality
- Use descriptive test method names
- Test both success and failure scenarios
- Mock external dependencies
- Follow AAA pattern (Arrange, Act, Assert)

Example test:

```php
public function testCreatePaymentWithValidRequest(): void
{
    // Arrange
    $paymentRequest = new PaymentRequest(
        amount: '10.00',
        paymentMethod: 'stripe'
    );

    // Act
    $payment = $this->paymentsResource->create($paymentRequest);

    // Assert
    $this->assertInstanceOf(Payment::class, $payment);
    $this->assertEquals('10.00', $payment->amount);
}
```

## Laravel Integration

When contributing Laravel-specific features:

- Test with multiple Laravel versions (10.x, 11.x)
- Follow Laravel conventions and patterns
- Use Laravel's testing helpers
- Update the service provider and configuration as needed

## Documentation

- Update README.md for new features
- Add docblocks to all public methods
- Include code examples in docblocks
- Update CHANGELOG.md with your changes

## API Compatibility

This SDK must maintain compatibility with the X-Pay backend API. When making changes:

- Ensure compatibility with existing API endpoints
- Test against both sandbox and live environments
- Follow the same patterns as the JavaScript SDK where possible
- Maintain consistent error codes and messages

## Security

- Never commit API keys or secrets
- Use constant-time comparison for sensitive operations
- Validate all input parameters
- Follow secure coding practices

## Pull Request Process

1. **Create a feature branch**: `git checkout -b feature/your-feature-name`
2. **Make your changes**: Follow the coding standards and write tests
3. **Update documentation**: Update README, docblocks, and CHANGELOG
4. **Run tests**: Ensure all tests pass and coverage is maintained
5. **Submit PR**: Create a pull request with a clear description

### Pull Request Template

```markdown
## Description
Brief description of what this PR does.

## Type of Change
- [ ] Bug fix (non-breaking change which fixes an issue)
- [ ] New feature (non-breaking change which adds functionality)
- [ ] Breaking change (fix or feature that would cause existing functionality to not work as expected)
- [ ] Documentation update

## Testing
- [ ] Unit tests added/updated
- [ ] Integration tests added/updated
- [ ] Manual testing completed

## Checklist
- [ ] Code follows the project's coding standards
- [ ] Self-review of code completed
- [ ] Code is commented, particularly in hard-to-understand areas
- [ ] Corresponding changes to documentation made
- [ ] Tests pass locally
- [ ] Static analysis passes
```

## Issue Reporting

When filing an issue, make sure to answer these questions:

1. What version of PHP are you using?
2. What version of the SDK are you using?
3. What did you do?
4. What did you expect to see?
5. What did you see instead?

### Bug Report Template

```markdown
**Describe the bug**
A clear and concise description of what the bug is.

**To Reproduce**
Steps to reproduce the behavior:
1. Initialize client with '...'
2. Call method '....'
3. See error

**Expected behavior**
A clear and concise description of what you expected to happen.

**Environment:**
- PHP Version: [e.g. 8.1.0]
- SDK Version: [e.g. 1.0.0]
- Laravel Version: [e.g. 10.0] (if applicable)
- OS: [e.g. Ubuntu 20.04]

**Additional context**
Add any other context about the problem here.
```

## Feature Requests

We love feature requests! Before submitting:

1. Check if the feature already exists
2. Check if there's already an issue for it
3. Consider if it fits the scope of this SDK

### Feature Request Template

```markdown
**Is your feature request related to a problem?**
A clear and concise description of what the problem is.

**Describe the solution you'd like**
A clear and concise description of what you want to happen.

**Describe alternatives you've considered**
A clear and concise description of any alternative solutions or features you've considered.

**Additional context**
Add any other context or screenshots about the feature request here.
```

## Code of Conduct

This project follows the [Contributor Covenant Code of Conduct](https://www.contributor-covenant.org/version/2/1/code_of_conduct/). Please be respectful and inclusive.

## License

By contributing, you agree that your contributions will be licensed under the MIT License.

## Questions?

Don't hesitate to reach out:
- Create an issue for questions about the codebase
- Email developers@xpay.com for general questions
- Join our community discussions

Thank you for contributing! 🙏