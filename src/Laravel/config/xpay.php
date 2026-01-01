<?php

return [
    /*
    |--------------------------------------------------------------------------
    | X-Pay API Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your X-Pay API credentials and settings.
    |
    */

    'api_key' => env('XPAY_API_KEY'),

    'merchant_id' => env('XPAY_MERCHANT_ID'),

    'environment' => env('XPAY_ENVIRONMENT', 'sandbox'),

    'base_url' => env('XPAY_BASE_URL'),

    'timeout' => env('XPAY_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configure webhook verification and handling settings.
    |
    */

    'webhook' => [
        'secret' => env('XPAY_WEBHOOK_SECRET'),
        'tolerance' => env('XPAY_WEBHOOK_TOLERANCE', 300), // 5 minutes
        'verify_signature' => env('XPAY_WEBHOOK_VERIFY_SIGNATURE', true),
    ],
];