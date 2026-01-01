<?php

declare(strict_types=1);

namespace XPay\Types;

final class XPayConfig
{
    public readonly string $apiKey;
    public readonly ?string $merchantId;
    public readonly string $environment;
    public readonly ?string $baseUrl;
    public readonly int $timeout;

    public function __construct(
        string $apiKey,
        ?string $merchantId = null,
        string $environment = 'sandbox',
        ?string $baseUrl = null,
        int $timeout = 30
    ) {
        $this->apiKey = $apiKey;
        $this->merchantId = $merchantId;
        $this->environment = $environment;
        $this->baseUrl = $baseUrl;
        $this->timeout = $timeout;

        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException('API key is required');
        }
    }

    public function getEnvironment(): string
    {
        // If explicitly set to something other than default 'sandbox', use that
        if ($this->environment === 'live') {
            return 'live';
        }

        // Auto-detect environment from API key prefix
        return $this->detectEnvironmentFromApiKey();
    }

    public function getBaseUrl(): string
    {
        if ($this->baseUrl !== null) {
            return rtrim($this->baseUrl, '/');
        }

        // Default to the hosted X-Pay API for examples and SDKs
        return 'https://server.xpay-bits.com';
    }

    private function detectEnvironmentFromApiKey(): string
    {
        if (str_starts_with($this->apiKey, 'xpay_sandbox_') ||
            str_starts_with($this->apiKey, 'pk_sandbox_') ||
            str_starts_with($this->apiKey, 'sk_sandbox_')) {
            return 'sandbox';
        }

        if (str_starts_with($this->apiKey, 'xpay_live_') ||
            str_starts_with($this->apiKey, 'pk_live_') ||
            str_starts_with($this->apiKey, 'sk_live_')) {
            return 'live';
        }

        return 'sandbox'; // Default to sandbox for development/testing
    }
}