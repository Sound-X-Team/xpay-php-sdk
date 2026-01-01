<?php

// Laravel helper functions

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return $default;
    }
}

if (!function_exists('abort')) {
    function abort(int $code, string $message = ''): never
    {
        throw new RuntimeException($message, $code);
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string
    {
        return $path;
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return $_ENV[$key] ?? $default;
    }
}