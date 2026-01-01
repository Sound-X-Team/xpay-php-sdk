# X-Pay PHP SDK Makefile

.PHONY: help install test test-coverage lint lint-fix phpstan cs-check cs-fix clean all

# Default target
help:
	@echo "Available targets:"
	@echo "  install       - Install dependencies"
	@echo "  test          - Run unit tests"
	@echo "  test-coverage - Run tests with coverage report"
	@echo "  lint          - Run all linting (PHPStan + CS check)"
	@echo "  lint-fix      - Run linting and fix code style issues"
	@echo "  phpstan       - Run PHPStan static analysis"
	@echo "  cs-check      - Check code style"
	@echo "  cs-fix        - Fix code style issues"
	@echo "  clean         - Clean cache and generated files"
	@echo "  all           - Run all checks (install, lint, test)"

# Install dependencies
install:
	composer install

# Run unit tests
test:
	vendor/bin/phpunit

# Run tests with coverage
test-coverage:
	vendor/bin/phpunit --coverage-html coverage --coverage-text

# Run PHPStan static analysis
phpstan:
	vendor/bin/phpstan analyse --configuration=phpstan.neon

# Check code style
cs-check:
	vendor/bin/php-cs-fixer fix --dry-run --diff --verbose

# Fix code style
cs-fix:
	vendor/bin/php-cs-fixer fix --verbose

# Run all linting
lint: phpstan cs-check

# Run linting and fix issues
lint-fix: phpstan cs-fix

# Clean generated files
clean:
	rm -rf coverage/
	rm -rf vendor/
	find . -name "*.cache" -delete

# Run all checks
all: install lint test

# Development helpers
dev-setup:
	@echo "Setting up development environment..."
	@make install
	@echo "✓ Dependencies installed"
	@echo "✓ Development environment ready"
	@echo ""
	@echo "Available commands:"
	@make help