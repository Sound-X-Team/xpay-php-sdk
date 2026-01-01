<?php

declare(strict_types=1);

namespace XPay\Resources;

use XPay\Http\Client;
use XPay\Types\CreateCustomerRequest;
use XPay\Types\Customer;

final class Customers
{
  public function __construct(
    private readonly Client $client,
    private readonly string $merchantId
  ) {}

  /**
   * Create a new customer
   */
  public function create(CreateCustomerRequest $customerData): Customer
  {
    $response = $this->client->post(
      "/v1/api/merchants/{$this->merchantId}/customers",
      $customerData->toArray()
    );

    return Customer::fromArray($response->data);
  }

  /**
   * Retrieve a customer by ID
   */
  public function retrieve(string $customerId): Customer
  {
    $response = $this->client->get("/v1/api/merchants/{$this->merchantId}/customers/{$customerId}");

    return Customer::fromArray($response->data);
  }

  /**
   * Update a customer
   * 
   * @param string $customerId
   * @param array<string, mixed> $updateData
   * @return Customer
   */
  public function update(string $customerId, array $updateData): Customer
  {
    $response = $this->client->put(
      "/v1/api/merchants/{$this->merchantId}/customers/{$customerId}",
      $updateData
    );

    return Customer::fromArray($response->data);
  }

  /**
   * Delete a customer
   */
  public function delete(string $customerId): bool
  {
    $response = $this->client->delete("/v1/api/merchants/{$this->merchantId}/customers/{$customerId}");

    return $response->data['deleted'] ?? false;
  }

  /**
   * List all customers
   * 
   * @param array<string, mixed>|null $params
   * @return array<string, mixed>
   */
  public function list(?array $params = null): array
  {
    $queryParams = [];

    if ($params !== null) {
      $allowedParams = ['limit', 'offset', 'email', 'name', 'created_after', 'created_before'];

      foreach ($allowedParams as $param) {
        if (isset($params[$param])) {
          $queryParams[$param] = (string) $params[$param];
        }
      }
    }

    $queryString = !empty($queryParams) ? '?' . http_build_query($queryParams) : '';
    $endpoint = "/v1/api/merchants/{$this->merchantId}/customers{$queryString}";

    $response = $this->client->get($endpoint);

    return [
      'customers' => array_map(
        fn(array $customer) => Customer::fromArray($customer),
        $response->data['customers'] ?? []
      ),
      'total' => $response->data['total'] ?? 0,
      'has_more' => $response->data['has_more'] ?? false,
    ];
  }
}

