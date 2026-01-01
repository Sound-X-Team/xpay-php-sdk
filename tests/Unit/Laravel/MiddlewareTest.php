<?php

declare(strict_types=1);

namespace XPay\Tests\Unit\Laravel;

use PHPUnit\Framework\TestCase;
use XPay\Laravel\Middleware\VerifyWebhookSignature;

final class MiddlewareTest extends TestCase
{
    private VerifyWebhookSignature $middleware;

    protected function setUp(): void
    {
        $this->middleware = new VerifyWebhookSignature();
    }

    public function testMiddlewareExists(): void
    {
        $this->assertInstanceOf(VerifyWebhookSignature::class, $this->middleware);
    }

    public function testMiddlewareHasHandleMethod(): void
    {
        $this->assertTrue(method_exists($this->middleware, 'handle'));
    }

    public function testHandleMethodSignature(): void
    {
        $reflection = new \ReflectionMethod($this->middleware, 'handle');
        $parameters = $reflection->getParameters();

        $this->assertCount(3, $parameters);
        $this->assertEquals('request', $parameters[0]->getName());
        $this->assertEquals('next', $parameters[1]->getName());
        $this->assertEquals('secret', $parameters[2]->getName());
        $this->assertTrue($parameters[2]->hasType());
        $this->assertTrue($parameters[2]->allowsNull());
    }
}