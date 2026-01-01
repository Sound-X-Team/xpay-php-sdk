<?php

// Bootstrap file for development without composer install

// Load stubs for missing dependencies
require_once __DIR__ . '/stubs/functions.php';
require_once __DIR__ . '/stubs/Psr/Http/Client/ClientInterface.php';
require_once __DIR__ . '/stubs/Psr/Http/Message/Interfaces.php';
require_once __DIR__ . '/stubs/GuzzleHttp/Client.php';
require_once __DIR__ . '/stubs/GuzzleHttp/Exception/Exceptions.php';
require_once __DIR__ . '/stubs/Money/Money.php';
require_once __DIR__ . '/stubs/Illuminate/Console/Command.php';
require_once __DIR__ . '/stubs/Illuminate/Support/Facades/Facade.php';
require_once __DIR__ . '/stubs/Illuminate/Support/ServiceProvider.php';
require_once __DIR__ . '/stubs/Illuminate/Http/Request.php';
require_once __DIR__ . '/stubs/Illuminate/Contracts/Support/DeferrableProvider.php';

// PSR-4 autoloader for XPay classes
spl_autoload_register(function ($class) {
    $prefix = 'XPay\\';
    $base_dir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
        return;
    }

    // Handle special cases where class name doesn't match file name
    $mapping = [
        'Exceptions\\XPayException' => 'Exceptions/XPayExceptions.php',
        'Exceptions\\AuthenticationException' => 'Exceptions/XPayExceptions.php',
        'Exceptions\\ValidationException' => 'Exceptions/XPayExceptions.php',
        'Exceptions\\NetworkException' => 'Exceptions/XPayExceptions.php',
        'Exceptions\\TimeoutException' => 'Exceptions/XPayExceptions.php',
        'Exceptions\\ResourceNotFoundException' => 'Exceptions/XPayExceptions.php',
        'Exceptions\\PermissionException' => 'Exceptions/XPayExceptions.php',
    ];
    
    if (isset($mapping[$relative_class])) {
        $file = $base_dir . $mapping[$relative_class];
        if (file_exists($file)) {
            require_once $file;
        }
    }
});