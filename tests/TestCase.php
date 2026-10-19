<?php

namespace Dkbs\LaravelDkbsign\Tests;

use Dkbs\LaravelDkbsign\DkbsignServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            DkbsignServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('dkbsign', [
            'base_url' => 'https://api.dkbsign.test',
            'token' => 'test-token',
            'timeout' => 30,
        ]);
    }
}
