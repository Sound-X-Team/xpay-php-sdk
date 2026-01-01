<?php

declare(strict_types=1);

namespace XPay\Tests\Unit;

use PHPUnit\Framework\TestCase;
use XPay\Types\XPayConfig;

final class XPayConfigTest extends TestCase
{
    public function testConstructorRequiresApiKey(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('API key is required');

        new XPayConfig('');
    }

    public function testConstructorWithValidApiKey(): void
    {
        $config = new XPayConfig('test_api_key');

        $this->assertEquals('test_api_key', $config->apiKey);
        $this->assertEquals('sandbox', $config->environment);
        $this->assertEquals(30, $config->timeout);
        $this->assertNull($config->merchantId);
        $this->assertNull($config->baseUrl);
    }

    public function testConstructorWithAllParameters(): void
    {
        $config = new XPayConfig(
            apiKey: 'test_api_key',
            merchantId: 'merchant_123',
            environment: 'live',
            baseUrl: 'https://api.example.com',
            timeout: 60
        );

        $this->assertEquals('test_api_key', $config->apiKey);
        $this->assertEquals('merchant_123', $config->merchantId);
        $this->assertEquals('live', $config->environment);
        $this->assertEquals('https://api.example.com', $config->baseUrl);
        $this->assertEquals(60, $config->timeout);
    }

    public function testGetEnvironmentFromSandboxApiKey(): void
    {
        $config = new XPayConfig('xpay_sandbox_test123');
        $this->assertEquals('sandbox', $config->getEnvironment());
    }

    public function testGetEnvironmentFromLiveApiKey(): void
    {
        $config = new XPayConfig('xpay_live_test123');
        $this->assertEquals('live', $config->getEnvironment());
    }

    public function testGetEnvironmentFromStripeStyleSandboxKey(): void
    {
        $config = new XPayConfig('pk_sandbox_test123');
        $this->assertEquals('sandbox', $config->getEnvironment());
    }

    public function testGetEnvironmentFromStripeStyleLiveKey(): void
    {
        $config = new XPayConfig('pk_live_test123');
        $this->assertEquals('live', $config->getEnvironment());
    }

    public function testGetEnvironmentDefaultsToSandbox(): void
    {
        $config = new XPayConfig('random_api_key_format');
        $this->assertEquals('sandbox', $config->getEnvironment());
    }

    public function testGetEnvironmentExplicitOverride(): void
    {
        $config = new XPayConfig(
            apiKey: 'xpay_sandbox_test123',
            environment: 'live'
        );
        $this->assertEquals('live', $config->getEnvironment());
    }

    public function testGetBaseUrlWithCustomUrl(): void
    {
        $config = new XPayConfig(
            apiKey: 'test_key',
            baseUrl: 'https://custom.example.com/'
        );
        $this->assertEquals('https://custom.example.com', $config->getBaseUrl());
    }

    public function testGetBaseUrlDefault(): void
    {
        $config = new XPayConfig('test_key');
        $this->assertEquals('http://localhost:8000', $config->getBaseUrl());
    }
}