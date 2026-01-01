<?php

declare(strict_types=1);

namespace XPay\Tests\Unit\Laravel;

use PHPUnit\Framework\TestCase;
use XPay\Laravel\Facades\XPay as XPayFacade;
use XPay\XPay;

final class FacadeTest extends TestCase
{
    public function testFacadeAccessor(): void
    {
        $accessor = XPayFacade::getFacadeAccessor();
        $this->assertEquals(XPay::class, $accessor);
    }

    public function testFacadeDocBlocks(): void
    {
        $reflection = new \ReflectionClass(XPayFacade::class);
        $docComment = $reflection->getDocComment();
        
        $this->assertStringContainsString('@method static \XPay\Resources\Payments payments()', $docComment);
        $this->assertStringContainsString('@method static \XPay\Resources\Webhooks webhooks()', $docComment);
        $this->assertStringContainsString('@method static \XPay\Resources\Customers customers()', $docComment);
        $this->assertStringContainsString('@see \XPay\XPay', $docComment);
    }
}