<?php

declare(strict_types=1);

namespace XPay\Types;

final readonly class XPayConfig
{
    public function __construct(
        public string $apiKey,
        public ?string $merchantId = null,
        public string $environment = 'sandbox',
        public ?string $baseUrl = null,
        public int $timeout = 30
    ) {
        if (empty($this->apiKey)) {
            throw new \InvalidArgumentException('API key is required');
        }
    }

    public function getEnvironment(): string
    {
        // Auto-detect environment from API key prefix if not explicitly set
        if ($this->environment !== 'sandbox' && $this->environment !== 'live') {
            return $this->detectEnvironmentFromApiKey();
        }

        return $this->environment;
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