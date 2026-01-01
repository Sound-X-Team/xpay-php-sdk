<?php

declare(strict_types=1);

namespace XPay\Laravel;

use Illuminate\Contracts\Support\DeferrableProvider;
use Illuminate\Support\ServiceProvider;
use XPay\Types\XPayConfig;
use XPay\XPay;

final class XPayServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/xpay.php', 'xpay'
        );

        $this->app->singleton(XPay::class, function ($app): XPay {
            $config = $app['config']['xpay'];

            if (!is_array($config)) {
                throw new \RuntimeException('XPay configuration is not properly loaded');
            }

            return new XPay(new XPayConfig(
                apiKey: $config['api_key'] ?? throw new \RuntimeException('XPay API key is required'),
                merchantId: $config['merchant_id'] ?? null,
                environment: $config['environment'] ?? 'sandbox',
                baseUrl: $config['base_url'] ?? null,
                timeout: (int) ($config['timeout'] ?? 30)
            ));
        });

        $this->app->alias(XPay::class, 'xpay');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/config/xpay.php' => \config_path('xpay.php'),
            ], 'xpay-config');

            $this->commands([
                Console\TestApiCommand::class,
                Console\ListPaymentMethodsCommand::class,
            ]);
        }
    }

    /**
     * Get the services provided by the provider.
     * 
     * @return array<string>
     */
    public function provides(): array
    {
        return [XPay::class, 'xpay'];
    }
}