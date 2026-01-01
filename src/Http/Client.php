<?php

declare(strict_types=1);

namespace XPay\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use Psr\Http\Client\ClientInterface;
use XPay\Exceptions\AuthenticationException;
use XPay\Exceptions\NetworkException;
use XPay\Exceptions\PermissionException;
use XPay\Exceptions\ResourceNotFoundException;
use XPay\Exceptions\TimeoutException;
use XPay\Exceptions\ValidationException;
use XPay\Exceptions\XPayException;
use XPay\Types\ApiResponse;
use XPay\Types\XPayConfig;

final class Client
{
  private ClientInterface $httpClient;

  public function __construct(
    private readonly XPayConfig $config,
    ?ClientInterface $httpClient = null
  ) {
    $this->httpClient = $httpClient ?? new GuzzleClient([
      'timeout' => $this->config->timeout,
      'connect_timeout' => 10,
      'verify' => true,
    ]);
  }

  public function get(string $endpoint): ApiResponse
  {
    return $this->request('GET', $endpoint);
  }

  public function post(string $endpoint, ?array $data = null): ApiResponse
  {
    return $this->request('POST', $endpoint, $data);
  }

  public function put(string $endpoint, ?array $data = null): ApiResponse
  {
    return $this->request('PUT', $endpoint, $data);
  }

  public function delete(string $endpoint): ApiResponse
  {
    return $this->request('DELETE', $endpoint);
  }

  private function request(string $method, string $endpoint, ?array $data = null): ApiResponse
  {
    $url = $this->config->getBaseUrl() . $endpoint;

    $options = [
      'headers' => $this->getHeaders(),
    ];

    if ($data !== null && $method !== 'GET') {
      $options['json'] = $data;
    }

    try {
      $response = $this->httpClient->request($method, $url, $options);
      $body = (string) $response->getBody();
      $decodedBody = json_decode($body, true);

      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new XPayException('Invalid JSON response', 'INVALID_RESPONSE');
      }

      return ApiResponse::fromArray($decodedBody);
    } catch (ConnectException $e) {
      throw new NetworkException('Failed to connect to X-Pay API', null, $e);
    } catch (ClientException $e) {
      $this->handleClientException($e);
    } catch (ServerException $e) {
      $errorData = $this->getErrorDataFromResponse($e);
      throw new XPayException(
        $errorData['message'] ?? 'Server error occurred',
        $errorData['error_code'] ?? 'SERVER_ERROR',
        $e->getResponse()?->getStatusCode(),
        $errorData,
        $e
      );
    } catch (RequestException $e) {
      if (str_contains($e->getMessage(), 'timeout')) {
        throw new TimeoutException('Request timeout', null, $e);
      }
      throw new NetworkException($e->getMessage(), null, $e);
    }
  }

  private function getHeaders(): array
  {
    return [
      'X-API-Key' => $this->config->apiKey,
      'Content-Type' => 'application/json',
      'User-Agent' => 'xpay-php-sdk/1.0.0',
      'X-SDK-Version' => '1.0.0',
      'X-Environment' => $this->config->getEnvironment(),
    ];
  }

  private function handleClientException(ClientException $e): never
  {
    $status = $e->getResponse()?->getStatusCode();
    $errorData = $this->getErrorDataFromResponse($e);
    $message = $errorData['message'] ?? $e->getMessage();

    match ($status) {
      400 => throw new ValidationException($message, $errorData, $e),
      401 => throw new AuthenticationException($message, $errorData, $e),
      403 => throw new PermissionException($message, $errorData, $e),
      404 => throw new ResourceNotFoundException($message, $errorData, $e),
      default => throw new XPayException(
        $message,
        $errorData['error_code'] ?? 'CLIENT_ERROR',
        $status,
        $errorData,
        $e
      ),
    };
  }

  private function getErrorDataFromResponse(RequestException $e): array
  {
    $body = (string) ($e->getResponse()?->getBody() ?? '');

    if (empty($body)) {
      return [];
    }

    $decoded = json_decode($body, true);

    return is_array($decoded) ? $decoded : [];
  }
}

