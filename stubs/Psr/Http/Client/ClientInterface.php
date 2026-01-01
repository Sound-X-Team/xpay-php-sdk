<?php

namespace Psr\Http\Client;

interface ClientInterface
{
    public function sendRequest(\Psr\Http\Message\RequestInterface $request): \Psr\Http\Message\ResponseInterface;
}