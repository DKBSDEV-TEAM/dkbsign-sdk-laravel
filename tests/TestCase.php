<?php

namespace Dkbs\LaravelDKBSign\Tests;

use Dkbs\LaravelDKBSign\DKBSignServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            DKBSignServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('DKBSign', [
            'base_url' => 'https://api.dkbsign.test',
            'token' => 'test-token',
            'timeout' => 30,
        ]);
    }
}
