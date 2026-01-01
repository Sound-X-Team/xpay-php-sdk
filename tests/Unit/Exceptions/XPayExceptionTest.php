<?php

declare(strict_types=1);

namespace XPay\Tests\Unit\Exceptions;

use PHPUnit\Framework\TestCase;
use XPay\Exceptions\AuthenticationException;
use XPay\Exceptions\NetworkException;
use XPay\Exceptions\PermissionException;
use XPay\Exceptions\ResourceNotFoundException;
use XPay\Exceptions\TimeoutException;
use XPay\Exceptions\ValidationException;
use XPay\Exceptions\XPayException;

final class XPayExceptionTest extends TestCase
{
    public function testXPayExceptionBasics(): void
    {
        $exception = new XPayException(
            'Test message',
            'TEST_ERROR',
            400,
            ['extra' => 'data'],
            new \Exception('Previous exception')
        );

        $this->assertEquals('Test message', $exception->getMessage());
        $this->assertEquals('TEST_ERROR', $exception->getErrorCode());
        $this->assertEquals(400, $exception->getHttpStatus());
        $this->assertEquals(['extra' => 'data'], $exception->getDetails());
        $this->assertInstanceOf(\Exception::class, $exception->getPrevious());
    }

    public function testAuthenticationExceptionDefaults(): void
    {
        $exception = new AuthenticationException();

        $this->assertEquals('Authentication failed', $exception->getMessage());
        $this->assertEquals('AUTHENTICATION_ERROR', $exception->getErrorCode());
        $this->assertEquals(401, $exception->getHttpStatus());
        $this->assertNull($exception->getDetails());
    }

    public function testAuthenticationExceptionCustom(): void
    {
        $exception = new AuthenticationException(
            'Invalid API key',
            ['api_key' => 'hidden'],
            new \Exception('Inner exception')
        );

        $this->assertEquals('Invalid API key', $exception->getMessage());
        $this->assertEquals('AUTHENTICATION_ERROR', $exception->getErrorCode());
        $this->assertEquals(401, $exception->getHttpStatus());
        $this->assertEquals(['api_key' => 'hidden'], $exception->getDetails());
        $this->assertInstanceOf(\Exception::class, $exception->getPrevious());
    }

    public function testValidationException(): void
    {
        $exception = new ValidationException(
            'Invalid amount',
            ['field' => 'amount', 'value' => '-10']
        );

        $this->assertEquals('Invalid amount', $exception->getMessage());
        $this->assertEquals('VALIDATION_ERROR', $exception->getErrorCode());
        $this->assertEquals(400, $exception->getHttpStatus());
        $this->assertEquals(['field' => 'amount', 'value' => '-10'], $exception->getDetails());
    }

    public function testNetworkException(): void
    {
        $exception = new NetworkException('Connection timeout');

        $this->assertEquals('Connection timeout', $exception->getMessage());
        $this->assertEquals('NETWORK_ERROR', $exception->getErrorCode());
        $this->assertNull($exception->getHttpStatus());
    }

    public function testTimeoutException(): void
    {
        $exception = new TimeoutException();

        $this->assertEquals('Request timeout', $exception->getMessage());
        $this->assertEquals('TIMEOUT', $exception->getErrorCode());
        $this->assertEquals(408, $exception->getHttpStatus());
    }

    public function testResourceNotFoundException(): void
    {
        $exception = new ResourceNotFoundException('Payment not found');

        $this->assertEquals('Payment not found', $exception->getMessage());
        $this->assertEquals('NOT_FOUND', $exception->getErrorCode());
        $this->assertEquals(404, $exception->getHttpStatus());
    }

    public function testPermissionException(): void
    {
        $exception = new PermissionException('Access denied');

        $this->assertEquals('Access denied', $exception->getMessage());
        $this->assertEquals('PERMISSION_DENIED', $exception->getErrorCode());
        $this->assertEquals(403, $exception->getHttpStatus());
    }

    public function testExceptionInheritance(): void
    {
        $exception = new ValidationException();

        $this->assertInstanceOf(XPayException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertInstanceOf(\Throwable::class, $exception);
    }

    public function testExceptionCodeProperty(): void
    {
        // Test that the original Exception::$code property is not affected
        $exception = new XPayException('Test', 'CUSTOM_CODE', 500);
        
        // The built-in code property should remain 0 (as set in constructor)
        $this->assertEquals(0, $exception->getCode());
        
        // Our custom error code should be accessible via getErrorCode()
        $this->assertEquals('CUSTOM_CODE', $exception->getErrorCode());
    }
}