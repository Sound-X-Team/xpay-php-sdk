<?php

declare(strict_types=1);

namespace XPay;

use XPay\Http\Client;
use XPay\Resources\Customers;
use XPay\Resources\Payments;
use XPay\Resources\Webhooks;
use XPay\Types\XPayConfig;

final class XPay
{
  private Client $client;
  private string $merchantId;

  public readonly Payments $payments;
  public readonly Webhooks $webhooks;
  public readonly Customers $customers;

  // Static access to utility classes
  public static string $PaymentsClass = Payments::class;
  public static string $WebhooksClass = Webhooks::class;
  public static string $CustomersClass = Customers::class;

  public function __construct(XPayConfig $config)
  {
    // Extract merchant ID from config or derive from API key
    $this->merchantId = $config->merchantId ?? $this->extractMerchantIdFromApiKey($config->apiKey);

    $this->client = new Client($config);
    $this->payments = new Payments($this->client, $this->merchantId);
    $this->webhooks = new Webhooks($this->client, $this->merchantId);
    $this->customers = new Customers($this->client, $this->merchantId);
  }

  private function extractMerchantIdFromApiKey(string $apiKey): string
  {
    // MerchantID must be provided explicitly in the config
    throw new \InvalidArgumentException(
      'Merchant ID is required. Get your merchant ID from the X-Pay dashboard.'
    );
  }

  /**
   * Test API connectivity and authentication
   */
  public function ping(): array
  {
    $response = $this->client->get('/v1/healthz');

    return [
      'success' => $response->success,
      'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
    ];
  }

  /**
   * Get payment methods available for this merchant
   */
  public function getPaymentMethods(): array
  {
    $response = $this->client->get("/v1/api/merchants/{$this->merchantId}/payment-methods");

    return $response->data ?? [];
  }

  /**
   * Get the merchant ID
   */
  public function getMerchantId(): string
  {
    return $this->merchantId;
  }

  /**
   * Get the HTTP client instance (for advanced usage)
   */
  public function getHttpClient(): Client
  {
    return $this->client;
  }
}

