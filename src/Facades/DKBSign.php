<?php

namespace Dkbs\LaravelDKBSign\Facades;

use Dkbs\LaravelDKBSign\DKBSignManager;
use Dkbs\LaravelDKBSign\Http\ApiResponse;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ApiResponse sendOtp()
 * @method static \Dkbs\LaravelDKBSign\DKBSign sign()
 *
 * @see DKBSignManager
 */
class DKBSign extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DKBSignManager::class;
    }
}
