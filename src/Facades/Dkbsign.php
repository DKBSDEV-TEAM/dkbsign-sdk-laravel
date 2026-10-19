<?php

namespace Dkbs\LaravelDkbsign\Facades;

use Dkbs\LaravelDkbsign\DkbsignManager;
use Dkbs\LaravelDkbsign\Http\ApiResponse;
use Illuminate\Support\Facades\Facade;

/**
 * @method static ApiResponse sendOtp()
 * @method static \Dkbs\LaravelDkbsign\Dkbsign sign()
 *
 * @see DkbsignManager
 */
class Dkbsign extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return DkbsignManager::class;
    }
}
