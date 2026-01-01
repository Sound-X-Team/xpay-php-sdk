<?php

declare(strict_types=1);

namespace XPay\Tests\Unit\Utils;

use PHPUnit\Framework\TestCase;
use XPay\Utils\WebhookUtils;

final class WebhookUtilsTest extends TestCase
{
    public function testVerifySignatureWithValidSignature(): void
    {
        $payload = '{"test": "data"}';
        $secret = 'test_secret_key';
        $expectedSignature = hash_hmac('sha256', $payload, $secret);
        
        $this->assertTrue(WebhookUtils::verifySignature($payload, $expectedSignature, $secret));
        $this->assertTrue(WebhookUtils::verifySignature($payload, 'sha256=' . $expectedSignature, $secret));
    }

    public function testVerifySignatureWithInvalidSignature(): void
    {
        $payload = '{"test": "data"}';
        $secret = 'test_secret_key';
        $wrongSignature = 'invalid_signature';
        
        $this->assertFalse(WebhookUtils::verifySignature($payload, $wrongSignature, $secret));
    }

    public function testVerifySignatureWithEmptyValues(): void
    {
        $this->assertFalse(WebhookUtils::verifySignature('', 'signature', 'secret'));
        $this->assertFalse(WebhookUtils::verifySignature('payload', '', 'secret'));
        $this->assertFalse(WebhookUtils::verifySignature('payload', 'signature', ''));
    }

    public function testGenerateSignature(): void
    {
        $payload = '{"test": "data"}';
        $secret = 'test_secret_key';
        
        $signature = WebhookUtils::generateSignature($payload, $secret);
        
        $this->assertStringStartsWith('sha256=', $signature);
        $this->assertTrue(WebhookUtils::verifySignature($payload, $signature, $secret));
    }

    public function testParseWebhookPayload(): void
    {
        $payload = '{"id": "evt_123", "type": "payment.succeeded", "data": {"test": "value"}}';
        
        $parsed = WebhookUtils::parseWebhookPayload($payload);
        
        $this->assertIsArray($parsed);
        $this->assertEquals('evt_123', $parsed['id']);
        $this->assertEquals('payment.succeeded', $parsed['type']);
        $this->assertEquals(['test' => 'value'], $parsed['data']);
    }

    public function testParseWebhookPayloadWithInvalidJson(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid JSON payload');

        WebhookUtils::parseWebhookPayload('invalid json');
    }

    public function testValidateWebhookEvent(): void
    {
        $validEvent = [
            'id' => 'evt_123',
            'type' => 'payment.succeeded',
            'created_at' => '2023-12-07T10:30:00Z',
            'data' => ['test' => 'value']
        ];
        
        $this->assertTrue(WebhookUtils::validateWebhookEvent($validEvent));
    }

    public function testValidateWebhookEventWithMissingFields(): void
    {
        $invalidEvent = [
            'id' => 'evt_123',
            'type' => 'payment.succeeded',
            // missing 'created_at' and 'data'
        ];
        
        $this->assertFalse(WebhookUtils::validateWebhookEvent($invalidEvent));
    }

    public function testGetSupportedEvents(): void
    {
        $events = WebhookUtils::getSupportedEvents();
        
        $this->assertIsArray($events);
        $this->assertContains('payment.created', $events);
        $this->assertContains('payment.succeeded', $events);
        $this->assertContains('payment.failed', $events);
        $this->assertContains('customer.created', $events);
    }

    public function testIsSupportedEvent(): void
    {
        $this->assertTrue(WebhookUtils::isSupportedEvent('payment.succeeded'));
        $this->assertTrue(WebhookUtils::isSupportedEvent('customer.created'));
        $this->assertFalse(WebhookUtils::isSupportedEvent('unknown.event'));
    }
}