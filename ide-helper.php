<?php

/**
 * IDE Helper for XPay PHP SDK
 * 
 * This file provides type hints and stubs for better IDE support when
 * working with the XPay PHP SDK. Include this file in your IDE or
 * development environment for better autocompletion and type checking.
 * 
 * Note: This file should NOT be included in production code.
 */

// PSR HTTP Client stubs
namespace Psr\Http\Client {
    if (!interface_exists('ClientInterface')) {
        interface ClientInterface {
            public function sendRequest(\Psr\Http\Message\RequestInterface $request): \Psr\Http\Message\ResponseInterface;
        }
    }
}

namespace Psr\Http\Message {
    if (!interface_exists('RequestInterface')) {
        interface RequestInterface {
            public function getMethod(): string;
            public function getUri(): string;
        }
    }
    
    if (!interface_exists('ResponseInterface')) {
        interface ResponseInterface {
            public function getStatusCode(): int;
            public function getBody(): StreamInterface;
        }
    }
    
    if (!interface_exists('StreamInterface')) {
        interface StreamInterface {
            public function __toString(): string;
        }
    }
}

// Guzzle HTTP stubs
namespace GuzzleHttp {
    if (!class_exists('Client')) {
        class Client implements \Psr\Http\Client\ClientInterface {
            public function __construct(array $config = []) {}
            
            public function request(string $method, string $uri, array $options = []): \Psr\Http\Message\ResponseInterface {
                return new class implements \Psr\Http\Message\ResponseInterface {
                    public function getStatusCode(): int { return 200; }
                    public function getBody(): \Psr\Http\Message\StreamInterface {
                        return new class implements \Psr\Http\Message\StreamInterface {
                            public function __toString(): string { return '{"success": true}'; }
                        };
                    }
                };
            }
            
            public function sendRequest(\Psr\Http\Message\RequestInterface $request): \Psr\Http\Message\ResponseInterface {
                return $this->request('GET', '');
            }
        }
    }
}

namespace GuzzleHttp\Exception {
    if (!class_exists('RequestException')) {
        class RequestException extends \Exception {
            public function getResponse(): ?\Psr\Http\Message\ResponseInterface { return null; }
        }
    }
    
    if (!class_exists('ConnectException')) {
        class ConnectException extends RequestException {}
    }
    
    if (!class_exists('ClientException')) {
        class ClientException extends RequestException {}
    }
    
    if (!class_exists('ServerException')) {
        class ServerException extends RequestException {}
    }
}

// Money PHP stubs
namespace Money {
    if (!class_exists('Money')) {
        class Money {
            public function __construct(string|int $amount, Currency $currency) {}
            public function getAmount(): string { return '100'; }
            public function getCurrency(): Currency { return new Currency('USD'); }
        }
    }
    
    if (!class_exists('Currency')) {
        class Currency {
            public function __construct(string $code) {}
            public function getCode(): string { return 'USD'; }
        }
    }
}

// Laravel stubs (only if not already defined)
if (!class_exists('Illuminate\Console\Command')) {
    namespace Illuminate\Console {
        abstract class Command {
            protected $signature;
            protected $description;
            
            public function info(string $string): void {}
            public function line(string $string): void {}
            public function newLine(): void {}
            public function warn(string $string): void {}
            public function error(string $string): void {}
            public function table(array $headers, array $rows): void {}
            
            const SUCCESS = 0;
            const FAILURE = 1;
        }
    }
}

if (!class_exists('Illuminate\Support\Facades\Facade')) {
    namespace Illuminate\Support\Facades {
        abstract class Facade {
            protected static function getFacadeAccessor(): string {
                return '';
            }
        }
    }
}

if (!class_exists('Illuminate\Support\ServiceProvider')) {
    namespace Illuminate\Support {
        abstract class ServiceProvider {
            protected $app;
            
            public function register(): void {}
            public function boot(): void {}
            public function mergeConfigFrom(string $path, string $key): void {}
            public function publishes(array $paths, ?string $group = null): void {}
            public function commands(array $commands): void {}
        }
    }
}

if (!class_exists('Illuminate\Contracts\Support\DeferrableProvider')) {
    namespace Illuminate\Contracts\Support {
        interface DeferrableProvider {
            public function provides(): array;
        }
    }
}

if (!class_exists('Illuminate\Http\Request')) {
    namespace Illuminate\Http {
        class Request {
            public function header(string $key, mixed $default = null): mixed {
                return $default;
            }
            
            public function getContent(): string|false {
                return '';
            }
            
            public function merge(array $input): void {}
            public function input(string $key, mixed $default = null): mixed {
                return $default;
            }
        }
    }
}

// Laravel helper functions (only if not already defined)
if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed {
        return $default;
    }
}

if (!function_exists('abort')) {
    function abort(int $code, string $message = ''): never {
        throw new RuntimeException($message, $code);
    }
}

if (!function_exists('config_path')) {
    function config_path(string $path = ''): string {
        return $path;
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed {
        return $_ENV[$key] ?? $default;
    }
}