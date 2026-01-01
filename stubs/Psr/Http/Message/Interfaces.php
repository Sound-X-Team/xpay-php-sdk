<?php

namespace Psr\Http\Message;

interface RequestInterface
{
    public function getMethod(): string;
    public function getUri(): string;
}

interface ResponseInterface
{
    public function getStatusCode(): int;
    public function getBody(): StreamInterface;
}

interface StreamInterface
{
    public function __toString(): string;
}