<?php

namespace Illuminate\Console;

abstract class Command
{
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