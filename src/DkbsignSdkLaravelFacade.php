<?php

namespace Dkbs\DkbsignSdkLaravel;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Dkbs\DkbsignSdkLaravel\Skeleton\SkeletonClass
 */
class DkbsignSdkLaravelFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dkbsign-sdk-laravel';
    }
}
