<?php

declare(strict_types=1);

namespace XPay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use XPay\XPay;
use XPay\Types\XPayConfig;

/**
 * Unit tests for XPay client initialization.
 * Tests configuration validation and merchant ID requirement.
 */
final class XPayTest extends TestCase
{
    public function testXPayRequiresMerchantId(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Merchant ID is required');
        
        $config = new XPayConfig(
            apiKey: 'sk_test_123',
            baseUrl: 'https://api.example.com',
            merchantId: null // No merchant ID - should throw
        );
        
        new XPay($config);
    }

    public function testXPayWithValidMerchantId(): void
    {
        $config = new XPayConfig(
            apiKey: 'sk_test_123',
            baseUrl: 'https://api.example.com',
            merchantId: 'merchant_123'
        );
        
        $xpay = new XPay($config);
        
        $this->assertEquals('merchant_123', $xpay->getMerchantId());
    }

    public function testXPayResourcesAreInitialized(): void
    {
        $config = new XPayConfig(
            apiKey: 'sk_test_123',
            baseUrl: 'https://api.example.com',
            merchantId: 'merchant_123'
        );
        
        $xpay = new XPay($config);
        
        $this->assertNotNull($xpay->payments);
        $this->assertNotNull($xpay->webhooks);
        $this->assertNotNull($xpay->customers);
    }

    public function testGetMerchantId(): void
    {
        $config = new XPayConfig(
            apiKey: 'sk_test_123',
            baseUrl: 'https://api.example.com',
            merchantId: 'my_merchant'
        );
        
        $xpay = new XPay($config);
        
        $this->assertEquals('my_merchant', $xpay->getMerchantId());
    }

    public function testGetHttpClient(): void
    {
        $config = new XPayConfig(
            apiKey: 'sk_test_123',
            baseUrl: 'https://api.example.com',
            merchantId: 'merchant_123'
        );
        
        $xpay = new XPay($config);
        
        $this->assertNotNull($xpay->getHttpClient());
    }
}
