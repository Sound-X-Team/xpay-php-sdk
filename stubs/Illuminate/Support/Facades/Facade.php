<?php

namespace Illuminate\Support\Facades;

abstract class Facade
{
    protected static function getFacadeAccessor(): string
    {
        return '';
    }
}