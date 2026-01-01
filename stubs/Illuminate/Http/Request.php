<?php

namespace Illuminate\Http;

class Request
{
    public function header(string $key, mixed $default = null): mixed
    {
        return $default;
    }
    
    public function getContent(): string|false
    {
        return '';
    }
    
    public function merge(array $input): void {}
    
    public function input(string $key, mixed $default = null): mixed
    {
        return $default;
    }
}