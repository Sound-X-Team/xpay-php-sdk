<?php

declare(strict_types=1);

namespace XPay\Tests\Unit\Utils;

use PHPUnit\Framework\TestCase;
use XPay\Exceptions\ValidationException;
use XPay\Utils\CurrencyUtils;

final class CurrencyUtilsTest extends TestCase
{
    public function testGetDefaultCurrency(): void
    {
        $this->assertEquals('USD', CurrencyUtils::getDefaultCurrency('stripe'));
        $this->assertEquals('GHS', CurrencyUtils::getDefaultCurrency('momo'));
        $this->assertEquals('USD', CurrencyUtils::getDefaultCurrency('xpay_wallet'));
        $this->assertEquals('USD', CurrencyUtils::getDefaultCurrency('unknown_method'));
    }

    public function testValidateCurrencyWithSupportedMethod(): void
    {
        // Should not throw for supported combinations
        CurrencyUtils::validateCurrency('stripe', 'USD');
        CurrencyUtils::validateCurrency('stripe', 'EUR');
        CurrencyUtils::validateCurrency('momo', 'GHS');
        
        $this->assertTrue(true); // If we get here, no exceptions were thrown
    }

    public function testValidateCurrencyWithUnsupportedMethod(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unsupported payment method: unknown_method');

        CurrencyUtils::validateCurrency('unknown_method', 'USD');
    }

    public function testValidateCurrencyWithUnsupportedCurrency(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Currency JPY is not supported for payment method stripe');

        CurrencyUtils::validateCurrency('stripe', 'JPY');
    }

    public function testGetSupportedCurrencies(): void
    {
        $stripeCurrencies = CurrencyUtils::getSupportedCurrencies('stripe');
        $this->assertEquals(['USD', 'EUR', 'GBP', 'GHS'], $stripeCurrencies);

        $momoCurrencies = CurrencyUtils::getSupportedCurrencies('momo');
        $this->assertEquals(['GHS'], $momoCurrencies);

        $unknownCurrencies = CurrencyUtils::getSupportedCurrencies('unknown');
        $this->assertEquals([], $unknownCurrencies);
    }

    public function testToSmallestUnit(): void
    {
        $this->assertEquals(1000, CurrencyUtils::toSmallestUnit(10.00, 'USD'));
        $this->assertEquals(1050, CurrencyUtils::toSmallestUnit(10.50, 'USD'));
        $this->assertEquals(1, CurrencyUtils::toSmallestUnit(0.01, 'USD'));
        $this->assertEquals(0, CurrencyUtils::toSmallestUnit(0.00, 'USD'));
    }

    public function testToSmallestUnitWithUnsupportedCurrency(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unsupported currency: JPY');

        CurrencyUtils::toSmallestUnit(10.00, 'JPY');
    }

    public function testFromSmallestUnit(): void
    {
        $this->assertEquals(10.00, CurrencyUtils::fromSmallestUnit(1000, 'USD'));
        $this->assertEquals(10.50, CurrencyUtils::fromSmallestUnit(1050, 'USD'));
        $this->assertEquals(0.01, CurrencyUtils::fromSmallestUnit(1, 'USD'));
        $this->assertEquals(0.00, CurrencyUtils::fromSmallestUnit(0, 'USD'));
    }

    public function testFromSmallestUnitWithUnsupportedCurrency(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unsupported currency: JPY');

        CurrencyUtils::fromSmallestUnit(1000, 'JPY');
    }

    public function testFormatAmount(): void
    {
        $this->assertEquals('$10.00', CurrencyUtils::formatAmount(10.00, 'USD', false));
        $this->assertEquals('$10.50', CurrencyUtils::formatAmount(10.50, 'USD', false));
        $this->assertEquals('€25.99', CurrencyUtils::formatAmount(25.99, 'EUR', false));
        $this->assertEquals('£100.00', CurrencyUtils::formatAmount(100.00, 'GBP', false));
        $this->assertEquals('₵50.25', CurrencyUtils::formatAmount(50.25, 'GHS', false));
    }

    public function testFormatAmountFromSmallestUnit(): void
    {
        $this->assertEquals('$10.00', CurrencyUtils::formatAmount(1000, 'USD', true));
        $this->assertEquals('$10.50', CurrencyUtils::formatAmount(1050, 'USD', true));
    }

    public function testFormatAmountWithUnsupportedCurrency(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Unsupported currency: JPY');

        CurrencyUtils::formatAmount(10.00, 'JPY', false);
    }

    public function testIsSupportedCurrency(): void
    {
        $this->assertTrue(CurrencyUtils::isSupportedCurrency('USD'));
        $this->assertTrue(CurrencyUtils::isSupportedCurrency('EUR'));
        $this->assertTrue(CurrencyUtils::isSupportedCurrency('GBP'));
        $this->assertTrue(CurrencyUtils::isSupportedCurrency('GHS'));
        $this->assertFalse(CurrencyUtils::isSupportedCurrency('JPY'));
        $this->assertFalse(CurrencyUtils::isSupportedCurrency('XYZ'));
    }

    public function testGetCurrencyInfo(): void
    {
        $usdInfo = CurrencyUtils::getCurrencyInfo('USD');
        $this->assertIsArray($usdInfo);
        $this->assertEquals('USD', $usdInfo['code']);
        $this->assertEquals('US Dollar', $usdInfo['name']);
        $this->assertEquals('$', $usdInfo['symbol']);
        $this->assertEquals(2, $usdInfo['decimal_places']);
        $this->assertEquals('cents', $usdInfo['smallest_unit_name']);

        $unknownInfo = CurrencyUtils::getCurrencyInfo('UNKNOWN');
        $this->assertNull($unknownInfo);
    }
}