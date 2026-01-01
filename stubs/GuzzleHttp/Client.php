<?php

namespace GuzzleHttp;

class Client implements \Psr\Http\Client\ClientInterface
{
    public function __construct(array $config = []) {}
    
    public function request(string $method, string $uri, array $options = []): \Psr\Http\Message\ResponseInterface
    {
        throw new \RuntimeException('Stub implementation');
    }
    
    public function sendRequest(\Psr\Http\Message\RequestInterface $request): \Psr\Http\Message\ResponseInterface
    {
        throw new \RuntimeException('Stub implementation');
    }
}