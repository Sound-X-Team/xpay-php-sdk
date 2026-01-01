<?php

declare(strict_types=1);

namespace XPay\Tests\Unit\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ServerException;
use PHPUnit\Framework\TestCase;
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
    private XPayConfig $config;

    protected function setUp(): void
    {
        $this->config = new XPayConfig('test_api_key');
    }

    private function createClientWithMockResponses(array $responses): Client
    {
        $mock = new MockHandler($responses);
        $handlerStack = HandlerStack::create($mock);
        $httpClient = new GuzzleClient(['handler' => $handlerStack]);
        
        return new Client($this->config, $httpClient);
    }

    public function testHandlesConnectException(): void
    {
        $this->expectException(NetworkException::class);
        $this->expectExceptionMessage('Failed to connect to X-Pay API');

        $client = $this->createClientWithMockResponses([
            new ConnectException('Connection failed', new Request('GET', '/test'))
        ]);

        $client->get('/test');
    }

    public function testHandlesClientException400(): void
    {
        $this->expectException(ValidationException::class);

        $client = $this->createClientWithMockResponses([
            new ClientException(
                'Bad Request',
                new Request('GET', '/test'),
                new Response(400, [], json_encode(['message' => 'Bad request']))
            )
        ]);

        $client->get('/test');
    }

    public function testHandlesClientException401(): void
    {
        $this->expectException(AuthenticationException::class);

        $client = $this->createClientWithMockResponses([
            new ClientException(
                'Unauthorized',
                new Request('GET', '/test'),
                new Response(401, [], json_encode(['message' => 'Unauthorized']))
            )
        ]);

        $client->get('/test');
    }

    public function testHandlesClientException403(): void
    {
        $this->expectException(PermissionException::class);

        $client = $this->createClientWithMockResponses([
            new ClientException(
                'Forbidden',
                new Request('GET', '/test'),
                new Response(403, [], json_encode(['message' => 'Forbidden']))
            )
        ]);

        $client->get('/test');
    }

    public function testHandlesClientException404(): void
    {
        $this->expectException(ResourceNotFoundException::class);

        $client = $this->createClientWithMockResponses([
            new ClientException(
                'Not Found',
                new Request('GET', '/test'),
                new Response(404, [], json_encode(['message' => 'Not found']))
            )
        ]);

        $client->get('/test');
    }

    public function testHandlesServerException(): void
    {
        $this->expectException(XPayException::class);
        $this->expectExceptionMessage('Internal server error');

        $client = $this->createClientWithMockResponses([
            new ServerException(
                'Server Error',
                new Request('GET', '/test'),
                new Response(500, [], json_encode(['message' => 'Internal server error', 'error_code' => 'SERVER_ERROR']))
            )
        ]);

        $client->get('/test');
    }

    public function testHandlesTimeoutException(): void
    {
        $this->expectException(TimeoutException::class);
        $this->expectExceptionMessage('Request timeout');

        $client = $this->createClientWithMockResponses([
            new RequestException('timeout error occurred', new Request('GET', '/test'))
        ]);

        $client->get('/test');
    }

    public function testHandlesGenericRequestException(): void
    {
        $this->expectException(NetworkException::class);

        $client = $this->createClientWithMockResponses([
            new RequestException('Generic request error', new Request('GET', '/test'))
        ]);

        $client->get('/test');
    }

    public function testHandlesInvalidJsonResponse(): void
    {
        $this->expectException(XPayException::class);
        $this->expectExceptionMessage('Invalid JSON response');

        $client = $this->createClientWithMockResponses([
            new Response(200, [], 'invalid json {')
        ]);

        $client->get('/test');
    }

    public function testExceptionDetailsArePreserved(): void
    {
        $client = $this->createClientWithMockResponses([
            new ClientException(
                'Bad Request',
                new Request('GET', '/test'),
                new Response(400, [], json_encode([
                    'message' => 'Validation failed',
                    'error_code' => 'VALIDATION_ERROR',
                    'details' => ['field' => 'amount']
                ]))
            )
        ]);

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
}