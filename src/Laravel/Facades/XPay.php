<?php

declare(strict_types=1);

namespace XPay\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use XPay\XPay as XPayClient;

/**
 * @method static \XPay\Resources\Payments payments()
 * @method static \XPay\Resources\Webhooks webhooks()
 * @method static \XPay\Resources\Customers customers()
 * @method static array ping()
 * @method static array getPaymentMethods()
 * @method static string getMerchantId()
 * @method static \XPay\Http\Client getHttpClient()
 *
 * @see \XPay\XPay
 */
final class XPay extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return XPayClient::class;
    }
}