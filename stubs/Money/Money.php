<?php

namespace Money;

class Money 
{
    public function __construct(string|int $amount, Currency $currency) {}
    
    public function getAmount(): string 
    {
        return '100';
    }
    
    public function getCurrency(): Currency 
    {
        return new Currency('USD');
    }
}

class Currency 
{
    public function __construct(string $code) {}
    
    public function getCode(): string 
    {
        return 'USD';
    }
}