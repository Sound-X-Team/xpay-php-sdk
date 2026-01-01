<?php

declare(strict_types=1);

namespace XPay\Utils;

use Money\Currency;
use Money\Money;
use XPay\Exceptions\ValidationException;

final class CurrencyUtils
{
    public const SUPPORTED_CURRENCIES = [
        'USD' => [
            'code' => 'USD',
            'name' => 'US Dollar',
            'symbol' => '$',
            'decimal_places' => 2,
            'smallest_unit_name' => 'cents'
        ],
        'GHS' => [
            'code' => 'GHS',
            'name' => 'Ghanaian Cedi',
            'symbol' => '₵',
            'decimal_places' => 2,
            'smallest_unit_name' => 'pesewas'
        ],
        'EUR' => [
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'decimal_places' => 2,
            'smallest_unit_name' => 'cents'
        ],
        'GBP' => [
            'code' => 'GBP',
            'name' => 'British Pound',
            'symbol' => '£',
            'decimal_places' => 2,
            'smallest_unit_name' => 'pence'
        ]
    ];

    public const PAYMENT_METHOD_CURRENCIES = [
        'stripe' => [
            'payment_method' => 'stripe',
            'supported_currencies' => ['USD', 'EUR', 'GBP', 'GHS'],
            'default_currency' => 'USD',
            'regions' => ['US', 'EU', 'GB', 'GH']
        ],
        'momo' => [
            'payment_method' => 'momo',
            'supported_currencies' => ['GHS'],
            'default_currency' => 'GHS',
            'regions' => ['GH']
        ],
        'momo_liberia' => [
            'payment_method' => 'momo_liberia',
            'supported_currencies' => ['USD'],
            'default_currency' => 'USD',
            'regions' => ['LR']
        ],
        'momo_nigeria' => [
            'payment_method' => 'momo_nigeria',
            'supported_currencies' => ['USD'],
            'default_currency' => 'USD',
            'regions' => ['NG']
        ],
        'momo_uganda' => [
            'payment_method' => 'momo_uganda',
            'supported_currencies' => ['USD'],
            'default_currency' => 'USD',
            'regions' => ['UG']
        ],
        'momo_rwanda' => [
            'payment_method' => 'momo_rwanda',
            'supported_currencies' => ['USD'],
            'default_currency' => 'USD',
            'regions' => ['RW']
        ],
        'xpay_wallet' => [
            'payment_method' => 'xpay_wallet',
            'supported_currencies' => ['USD', 'GHS', 'EUR'],
            'default_currency' => 'USD',
            'regions' => ['US', 'GH', 'EU']
        ]
    ];

    public static function getDefaultCurrency(string $paymentMethod): string
    {
        $methodConfig = self::PAYMENT_METHOD_CURRENCIES[$paymentMethod] ?? null;
        
        return $methodConfig['default_currency'] ?? 'USD';
    }

    public static function validateCurrency(string $paymentMethod, string $currency): void
    {
        $methodConfig = self::PAYMENT_METHOD_CURRENCIES[$paymentMethod] ?? null;
        
        if ($methodConfig === null) {
            throw new ValidationException("Unsupported payment method: {$paymentMethod}");
        }

        if (!in_array($currency, $methodConfig['supported_currencies'], true)) {
            $supported = implode(', ', $methodConfig['supported_currencies']);
            throw new ValidationException(
                "Currency {$currency} is not supported for payment method {$paymentMethod}. " .
                "Supported currencies: {$supported}"
            );
        }
    }

    public static function getSupportedCurrencies(string $paymentMethod): array
    {
        $methodConfig = self::PAYMENT_METHOD_CURRENCIES[$paymentMethod] ?? null;
        
        return $methodConfig['supported_currencies'] ?? [];
    }

    /**
     * Convert amount to smallest currency unit (e.g., dollars to cents)
     */
    public static function toSmallestUnit(float $amount, string $currency): int
    {
        $currencyInfo = self::SUPPORTED_CURRENCIES[$currency] ?? null;
        
        if ($currencyInfo === null) {
            throw new ValidationException("Unsupported currency: {$currency}");
        }
        
        return (int) round($amount * (10 ** $currencyInfo['decimal_places']));
    }

    /**
     * Convert amount from smallest currency unit (e.g., cents to dollars)
     */
    public static function fromSmallestUnit(int $amount, string $currency): float
    {
        $currencyInfo = self::SUPPORTED_CURRENCIES[$currency] ?? null;
        
        if ($currencyInfo === null) {
            throw new ValidationException("Unsupported currency: {$currency}");
        }
        
        return $amount / (10 ** $currencyInfo['decimal_places']);
    }

    /**
     * Format amount for display with currency symbol
     */
    public static function formatAmount(float $amount, string $currency, bool $fromSmallestUnit = true): string
    {
        $currencyInfo = self::SUPPORTED_CURRENCIES[$currency] ?? null;
        
        if ($currencyInfo === null) {
            throw new ValidationException("Unsupported currency: {$currency}");
        }
        
        $displayAmount = $fromSmallestUnit 
            ? self::fromSmallestUnit((int) $amount, $currency)
            : $amount;
        
        return $currencyInfo['symbol'] . number_format($displayAmount, $currencyInfo['decimal_places']);
    }

    /**
     * Create Money object from amount string (as received from API)
     */
    public static function createMoney(string $amount, string $currency): Money
    {
        // Convert decimal amount to smallest unit
        $smallestUnit = self::toSmallestUnit((float) $amount, $currency);
        
        return new Money($smallestUnit, new Currency($currency));
    }

    /**
     * Convert Money object to API amount string
     */
    public static function moneyToApiAmount(Money $money): string
    {
        $currency = $money->getCurrency()->getCode();
        $amount = self::fromSmallestUnit((int) $money->getAmount(), $currency);
        
        $currencyInfo = self::SUPPORTED_CURRENCIES[$currency];
        
        return number_format($amount, $currencyInfo['decimal_places'], '.', '');
    }

    /**
     * Check if currency is supported
     */
    public static function isSupportedCurrency(string $currency): bool
    {
        return isset(self::SUPPORTED_CURRENCIES[$currency]);
    }

    /**
     * Get currency information
     */
    public static function getCurrencyInfo(string $currency): ?array
    {
        return self::SUPPORTED_CURRENCIES[$currency] ?? null;
    }
}