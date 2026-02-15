<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @mixin \App\Services\Navigation
 */
class Navigation extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \App\Services\Navigation::class;
    }
}
