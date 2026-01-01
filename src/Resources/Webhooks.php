<?php

declare(strict_types=1);

namespace XPay\Resources;

use XPay\Http\Client;
use XPay\Types\CreateWebhookRequest;
use XPay\Types\WebhookEndpoint;
use XPay\Utils\WebhookUtils;

final class Webhooks
{
  public function __construct(
    private readonly Client $client,
    private readonly string $merchantId
  ) {}

  /**
   * Create a new webhook endpoint
   */
  public function create(CreateWebhookRequest $webhookData): WebhookEndpoint
  {
    $response = $this->client->post(
      "/v1/api/merchants/{$this->merchantId}/webhooks",
      $webhookData->toArray()
    );

    return WebhookEndpoint::fromArray($response->data);
  }

  /**
   * List all webhook endpoints
   * 
   * @return XPay\Types\WebhookEndpoint[]
   */
  public function list(): array
  {
    $response = $this->client->get("/v1/api/merchants/{$this->merchantId}/webhooks");

    return array_map(
      fn(array $webhook) => WebhookEndpoint::fromArray($webhook),
      $response->data['webhooks'] ?? []
    );
  }

  /**
   * Retrieve a webhook endpoint by ID
   */
  public function retrieve(string $webhookId): WebhookEndpoint
  {
    $response = $this->client->get("/v1/api/merchants/{$this->merchantId}/webhooks/{$webhookId}");

    return WebhookEndpoint::fromArray($response->data);
  }

  /**
   * Update a webhook endpoint
   * 
   * @param string $webhookId
   * @param array<string, mixed> $updateData
   * @return WebhookEndpoint
   */
  public function update(string $webhookId, array $updateData): WebhookEndpoint
  {
    $response = $this->client->put(
      "/v1/api/merchants/{$this->merchantId}/webhooks/{$webhookId}",
      $updateData
    );

    return WebhookEndpoint::fromArray($response->data);
  }

  /**
   * Delete a webhook endpoint
   */
  public function delete(string $webhookId): bool
  {
    $response = $this->client->delete("/v1/api/merchants/{$this->merchantId}/webhooks/{$webhookId}");

    return $response->data['deleted'] ?? false;
  }

  /**
   * Test a webhook endpoint
   */
  public function test(string $webhookId): array
  {
    $response = $this->client->post("/v1/api/merchants/{$this->merchantId}/webhooks/{$webhookId}/test");

    return $response->data;
  }

  /**
   * Verify webhook signature
   */
  public static function verifySignature(string $payload, string $signature, string $secret): bool
  {
    return WebhookUtils::verifySignature($payload, $signature, $secret);
  }

  /**
   * Parse webhook payload
   */
  public static function parsePayload(string $payload): array
  {
    return WebhookUtils::parseWebhookPayload($payload);
  }

  /**
   * Validate webhook event
   * 
   * @param array<string, mixed> $event
   * @return bool
   */
  public static function validateEvent(array $event): bool
  {
    return WebhookUtils::validateWebhookEvent($event);
  }

  /**
   * Get supported webhook events
   * 
   * @return string[]
   */
  public static function getSupportedEvents(): array
  {
    return WebhookUtils::getSupportedEvents();
  }
}

