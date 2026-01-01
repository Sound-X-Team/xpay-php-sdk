<?php

declare(strict_types=1);

namespace XPay\Tests\Unit\Http;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use XPay\Exceptions\AuthenticationException;
use XPay\Exceptions\NetworkException;
use XPay\Exceptions\PermissionException;
use XPay\Exceptions\ResourceNotFoundException;
use XPay\Exceptions\TimeoutException;
use XPay\Exceptions\ValidationException;
use XPay\Exceptions\XPayException;
use XPay\Http\Client;
use XPay\Types\XPayConfig;

final class ClientExceptionHandlingTest extends TestCase
{
    private ClientInterface $mockHttpClient;
    private XPayConfig $config;

    protected function setUp(): void
    {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->config = new XPayConfig('test_api_key');
    }

    public function testHandlesConnectException(): void
    {
        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('Failed to connect to X-Pay API');

        $request = $this->createMock(RequestInterface::class);
        $exception = new ConnectException('Connection failed', $request);

        $this->mockHttpClient->method('request')
            ->willThrowException($exception);

        $client = new Client($this->config, $this->mockHttpClient);
        $client->get('/test');
    }

    public function testHandlesClientException400(): void
    {
        $this->expectException(ValidationException::class);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMockResponse(400, '{"message": "Bad request"}');
        $exception = new ClientException('Bad Request', $request, $response);

        $this->mockHttpClient->method('request')
            ->willThrowException($exception);

        $client = new Client($this->config, $this->mockHttpClient);
        $client->get('/test');
    }

    public function testHandlesClientException401(): void
    {
        $this->expectException(AuthenticationException::class);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMockResponse(401, '{"message": "Unauthorized"}');
        $exception = new ClientException('Unauthorized', $request, $response);

        $this->mockHttpClient->method('request')
            ->willThrowException($exception);

        $client = new Client($this->config, $this->mockHttpClient);
        $client->get('/test');
    }

    public function testHandlesClientException403(): void
    {
        $this->expectException(PermissionException::class);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMockResponse(403, '{"message": "Forbidden"}');
        $exception = new ClientException('Forbidden', $request, $response);

        $this->mockHttpClient->method('request')
            ->willThrowException($exception);

        $client = new Client($this->config, $this->mockHttpClient);
        $client->get('/test');
    }

    public function testHandlesClientException404(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMockResponse(404, '{"message": "Not found"}');
        $exception = new ClientException('Not Found', $request, $response);

        $this->mockHttpClient->method('request')
            ->willThrowException($exception);

        $client = new Client($this->config, $this->mockHttpClient);
        $client->get('/test');
    }

    public function testHandlesServerException(): void
    {
        $this->expectException(XPayException::class);
        $this->expectExceptionMessage('Server error occurred');

        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMockResponse(500, '{"message": "Internal server error", "error_code": "SERVER_ERROR"}');
        $exception = new ServerException('Server Error', $request, $response);

        $this->mockHttpClient->method('request')
            ->willThrowException($exception);

        $client = new Client($this->config, $this->mockHttpClient);
        $client->get('/test');
    }

    public function testHandlesTimeoutException(): void
    {
        $this->expectException(TimeoutException::class);
        $this->expectExceptionMessage('Request timeout');

        $request = $this->createMock(RequestInterface::class);
        $exception = new RequestException('timeout error occurred', $request);

        $this->mockHttpClient->method('request')
            ->willThrowException($exception);

        $client = new Client($this->config, $this->mockHttpClient);
        $client->get('/test');
    }

    public function testHandlesGenericRequestException(): void
    {
        $this->expectException(NetworkException::class);

        $request = $this->createMock(RequestInterface::class);
        $exception = new RequestException('Generic request error', $request);

        $this->mockHttpClient->method('request')
            ->willThrowException($exception);

        $client = new Client($this->config, $this->mockHttpClient);
        $client->get('/test');
    }

    public function testHandlesInvalidJsonResponse(): void
    {
        $this->expectException(XPayException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        
        $stream->method('__toString')
            ->willReturn('invalid json {');
        
        $response->method('getBody')
            ->willReturn($stream);

        $this->mockHttpClient->method('request')
            ->willReturn($response);

        $client = new Client($this->config, $this->mockHttpClient);
        $client->get('/test');
    }

    public function testExceptionDetailsArePreserved(): void
    {
        $request = $this->createMock(RequestInterface::class);
        $response = $this->createMockResponse(400, '{"message": "Validation failed", "error_code": "VALIDATION_ERROR", "details": {"field": "amount"}}');
        $exception = new ClientException('Bad Request', $request, $response);

        $this->mockHttpClient->method('request')
            ->willThrowException($exception);

        $client = new Client($this->config, $this->mockHttpClient);

        try {
            $client->get('/test');
            $this->fail('Expected ValidationException to be thrown');
        } catch (ValidationException $e) {
            $this->assertEquals('Validation failed', $e->getMessage());
            $this->assertEquals('VALIDATION_ERROR', $e->getErrorCode());
            $this->assertEquals(['field' => 'amount'], $e->getDetails()['details']);
            $this->assertEquals(400, $e->getHttpStatus());
        }
    }

    private function createMockResponse(int $statusCode, string $body): ResponseInterface
    {
        $response = $this->createMock(ResponseInterface::class);
        $stream = $this->createMock(StreamInterface::class);
        
        $stream->method('__toString')
            ->willReturn($body);
        
        $response->method('getStatusCode')
            ->willReturn($statusCode);
        
        $response->method('getBody')
            ->willReturn($stream);

        return $response;
    }
}