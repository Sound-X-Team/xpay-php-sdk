<?php

declare(strict_types=1);

namespace XPay\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use XPay\Exceptions\AuthenticationException;
use XPay\Exceptions\NetworkException;
use XPay\Exceptions\ValidationException;
use XPay\Http\Client;
use XPay\Types\XPayConfig;

final class ClientTest extends TestCase
{
    private ClientInterface $mockHttpClient;
    private ResponseInterface $mockResponse;
    private StreamInterface $mockStream;
    private XPayConfig $config;

    protected function setUp(): void
    {
        $this->mockHttpClient = $this->createMock(ClientInterface::class);
        $this->mockResponse = $this->createMock(ResponseInterface::class);
        $this->mockStream = $this->createMock(StreamInterface::class);
        
        $this->config = new XPayConfig('test_api_key');
    }

    public function testSuccessfulGetRequest(): void
    {
        $responseData = ['success' => true, 'data' => ['id' => '123']];
        
        $this->mockStream->method('__toString')
            ->willReturn(json_encode($responseData));
        
        $this->mockResponse->method('getStatusCode')
            ->willReturn(200);
        
        $this->mockResponse->method('getBody')
            ->willReturn($this->mockStream);
        
        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'http://localhost:8000/test',
                $this->callback(function ($options) {
                    return isset($options['headers']['X-API-Key']) &&
                           $options['headers']['X-API-Key'] === 'test_api_key' &&
                           $options['headers']['Content-Type'] === 'application/json' &&
                           $options['headers']['X-Environment'] === 'sandbox';
                })
            )
            ->willReturn($this->mockResponse);

        $client = new Client($this->config, $this->mockHttpClient);
        $result = $client->get('/test');

        $this->assertTrue($result->success);
        $this->assertEquals(['id' => '123'], $result->data);
    }

    public function testSuccessfulPostRequest(): void
    {
        $requestData = ['amount' => '10.00', 'currency' => 'USD'];
        $responseData = ['success' => true, 'data' => ['id' => '123']];
        
        $this->mockStream->method('__toString')
            ->willReturn(json_encode($responseData));
        
        $this->mockResponse->method('getStatusCode')
            ->willReturn(200);
        
        $this->mockResponse->method('getBody')
            ->willReturn($this->mockStream);
        
        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->with(
                'POST',
                'http://localhost:8000/test',
                $this->callback(function ($options) use ($requestData) {
                    return isset($options['json']) &&
                           $options['json'] === $requestData &&
                           isset($options['headers']['X-API-Key']);
                })
            )
            ->willReturn($this->mockResponse);

        $client = new Client($this->config, $this->mockHttpClient);
        $result = $client->post('/test', $requestData);

        $this->assertTrue($result->success);
    }

    public function testHandles401AuthenticationError(): void
    {
        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Authentication failed');

        $errorResponse = $this->createMock(ResponseInterface::class);
        $errorStream = $this->createMock(StreamInterface::class);
        
        $errorStream->method('__toString')
            ->willReturn('{"message": "Invalid API key", "error_code": "INVALID_API_KEY"}');
        
        $errorResponse->method('getStatusCode')
            ->willReturn(401);
        
        $errorResponse->method('getBody')
            ->willReturn($errorStream);

        $exception = new \GuzzleHttp\Exception\ClientException(
            'Unauthorized',
            $this->createMock(\Psr\Http\Message\RequestInterface::class),
            $errorResponse
        );

        $this->mockHttpClient->method('request')
            ->willThrowException($exception);

        $client = new Client($this->config, $this->mockHttpClient);
        $client->get('/test');
    }

    public function testHandles400ValidationError(): void
    {
        $this->expectException(ValidationException::class);

        $errorResponse = $this->createMock(ResponseInterface::class);
        $errorStream = $this->createMock(StreamInterface::class);
        
        $errorStream->method('__toString')
            ->willReturn('{"message": "Invalid request data", "error_code": "VALIDATION_ERROR"}');
        
        $errorResponse->method('getStatusCode')
            ->willReturn(400);
        
        $errorResponse->method('getBody')
            ->willReturn($errorStream);

        $exception = new \GuzzleHttp\Exception\ClientException(
            'Bad Request',
            $this->createMock(\Psr\Http\Message\RequestInterface::class),
            $errorResponse
        );

        $this->mockHttpClient->method('request')
            ->willThrowException($exception);

        $client = new Client($this->config, $this->mockHttpClient);
        $client->get('/test');
    }

    public function testHandlesNetworkError(): void
    {
        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('Failed to connect to X-Pay API');

        $exception = new \GuzzleHttp\Exception\ConnectException(
            'Connection failed',
            $this->createMock(\Psr\Http\Message\RequestInterface::class)
        );

        $this->mockHttpClient->method('request')
            ->willThrowException($exception);

        $client = new Client($this->config, $this->mockHttpClient);
        $client->get('/test');
    }

    public function testUsesCustomBaseUrl(): void
    {
        $config = new XPayConfig(
            apiKey: 'test_key',
            baseUrl: 'https://custom.api.com'
        );

        $this->mockStream->method('__toString')
            ->willReturn('{"success": true, "data": {}}');
        
        $this->mockResponse->method('getStatusCode')
            ->willReturn(200);
        
        $this->mockResponse->method('getBody')
            ->willReturn($this->mockStream);

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'https://custom.api.com/test',
                $this->anything()
            )
            ->willReturn($this->mockResponse);

        $client = new Client($config, $this->mockHttpClient);
        $client->get('/test');
    }

    public function testDetectsEnvironmentFromApiKey(): void
    {
        $liveConfig = new XPayConfig('xpay_live_test123');

        $this->mockStream->method('__toString')
            ->willReturn('{"success": true, "data": {}}');
        
        $this->mockResponse->method('getStatusCode')
            ->willReturn(200);
        
        $this->mockResponse->method('getBody')
            ->willReturn($this->mockStream);

        $this->mockHttpClient->expects($this->once())
            ->method('request')
            ->with(
                'GET',
                'http://localhost:8000/test',
                $this->callback(function ($options) {
                    return $options['headers']['X-Environment'] === 'live';
                })
            )
            ->willReturn($this->mockResponse);

        $client = new Client($liveConfig, $this->mockHttpClient);
        $client->get('/test');
    }
}