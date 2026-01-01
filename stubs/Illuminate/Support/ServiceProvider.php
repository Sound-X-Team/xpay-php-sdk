<?php

namespace Illuminate\Support;

abstract class ServiceProvider
{
    protected $app;
    
    public function register(): void {}
    public function boot(): void {}
    public function mergeConfigFrom(string $path, string $key): void {}
    public function publishes(array $paths, ?string $group = null): void {}
    public function commands(array $commands): void {}
}