<?php

declare(strict_types=1);

namespace XPay\Tests\Unit\Http;

use PHPUnit\Framework\TestCase;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Psr7\Request;
use XPay\Exceptions\AuthenticationException;
use XPay\Exceptions\NetworkException;
use XPay\Exceptions\ValidationException;
use XPay\Http\Client;
use XPay\Types\XPayConfig;

final class ClientTest extends TestCase
{
    private XPayConfig $config;

    protected function setUp(): void
    {
        $this->config = new XPayConfig('test_api_key');
    }

    private function createClientWithMockResponses(array $responses, ?XPayConfig $config = null): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new GuzzleClient(['handler' => $handlerStack]);
        
        return new Client($config ?? $this->config, $httpClient);
    }

    public function testSuccessfulGetRequest(): void
    {
        $responseData = ['success' => true, 'data' => ['id' => '123']];
        
        $client = $this->createClientWithMockResponses([
            new Response(200, [], json_encode($responseData))
        ]);

        $result = $client->get('/test');

        $this->assertTrue($result->success);
        $this->assertEquals(['id' => '123'], $result->data);
    }

    public function testSuccessfulPostRequest(): void
    {
        $responseData = ['success' => true, 'data' => ['id' => '123']];
        
        $client = $this->createClientWithMockResponses([
            new Response(200, [], json_encode($responseData))
        ]);

        $result = $client->post('/test', ['amount' => '10.00', 'currency' => 'USD']);

        $this->assertTrue($result->success);
    }

    public function testHandles401AuthenticationError(): void
    {
        $this->expectException(AuthenticationException::class);

        $client = $this->createClientWithMockResponses([
            new ClientException(
                'Unauthorized',
                new Request('GET', '/test'),
                new Response(401, [], json_encode(['message' => 'Invalid API key', 'error_code' => 'INVALID_API_KEY']))
            )
        ]);

        $client->get('/test');
    }

    public function testHandles400ValidationError(): void
    {
        $this->expectException(ValidationException::class);

        $client = $this->createClientWithMockResponses([
            new ClientException(
                'Bad Request',
                new Request('GET', '/test'),
                new Response(400, [], json_encode(['message' => 'Invalid request data', 'error_code' => 'VALIDATION_ERROR']))
            )
        ]);

        $client->get('/test');
    }

    public function testHandlesNetworkError(): void
    {
        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('Failed to connect to X-Pay API');

        $client = $this->createClientWithMockResponses([
            new ConnectException('Connection failed', new Request('GET', '/test'))
        ]);

        $client->get('/test');
    }

    public function testUsesCustomBaseUrl(): void
    {
        $config = new XPayConfig(
            apiKey: 'test_key',
            baseUrl: 'https://custom.api.com'
        );

        $client = $this->createClientWithMockResponses([
            new Response(200, [], json_encode(['success' => true, 'data' => []]))
        ], $config);

        $result = $client->get('/test');
        $this->assertTrue($result->success);
    }

    public function testDetectsEnvironmentFromApiKey(): void
    {
        $liveConfig = new XPayConfig('xpay_live_test123');

        $client = $this->createClientWithMockResponses([
            new Response(200, [], json_encode(['success' => true, 'data' => []]))
        ], $liveConfig);

        // This test verifies the environment is set correctly
        $this->assertEquals('live', $liveConfig->getEnvironment());
        
        $result = $client->get('/test');
        $this->assertTrue($result->success);
    }
}