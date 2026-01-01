<?php

declare(strict_types=1);

namespace XPay\Tests\Unit\Resources;

use PHPUnit\Framework\TestCase;
use XPay\Resources\Payments;
use XPay\Utils\CurrencyUtils;

/**
 * Unit tests for Payments resource.
 * Tests static utility methods and currency-related functionality.
 */
final class PaymentsTest extends TestCase
{
    public function testToSmallestUnitUSD(): void
    {
        // USD: 100.00 -> 10000 cents
        $this->assertEquals(10000, Payments::toSmallestUnit(100.00, 'USD'));
    }

    public function testToSmallestUnitEUR(): void
    {
        // EUR: 50.00 -> 5000 cents
        $this->assertEquals(5000, Payments::toSmallestUnit(50.00, 'EUR'));
    }

    public function testToSmallestUnitGBP(): void
    {
        // GBP: 25.00 -> 2500 pence
        $this->assertEquals(2500, Payments::toSmallestUnit(25.00, 'GBP'));
    }

    public function testFromSmallestUnitUSD(): void
    {
        // USD: 10000 cents -> 100.00
        $this->assertEquals(100.00, Payments::fromSmallestUnit(10000, 'USD'));
    }

    public function testFromSmallestUnitEUR(): void
    {
        // EUR: 5000 cents -> 50.00
        $this->assertEquals(50.00, Payments::fromSmallestUnit(5000, 'EUR'));
    }

    public function testFromSmallestUnitGBP(): void
    {
        // GBP: 2500 pence -> 25.00
        $this->assertEquals(25.00, Payments::fromSmallestUnit(2500, 'GBP'));
    }

    public function testFormatAmountUSD(): void
    {
        $formatted = Payments::formatAmount(10000, 'USD', true);
        $this->assertStringContainsString('100', $formatted);
    }

    public function testFormatAmountEUR(): void
    {
        $formatted = Payments::formatAmount(5000, 'EUR', true);
        $this->assertStringContainsString('50', $formatted);
    }

    public function testCurrencyUtilsGetSupportedCurrenciesStripe(): void
    {
        $currencies = CurrencyUtils::getSupportedCurrencies('stripe');
        
        $this->assertIsArray($currencies);
        $this->assertContains('USD', $currencies);
        $this->assertContains('EUR', $currencies);
    }

    public function testCurrencyUtilsValidateCurrencyValid(): void
    {
        // Should not throw for valid currency
        CurrencyUtils::validateCurrency('stripe', 'USD');
        $this->assertTrue(true);
    }
}
