<?php

namespace Dkbs\LaravelDKBSign;

use Dkbs\LaravelDKBSign\Http\DKBSignClient;
use Illuminate\Support\ServiceProvider;

class DKBSignServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/dkbsign.php', 'dkbsign');

        $this->app->singleton(DKBSignClient::class, function ($app) {
            $config = $app['config']['dkbsign'];

            return new DKBSignClient(
                rtrim((string) $config['base_url'], '/'),
                (string) ($config['token'] ?? ''),
                (int) $config['timeout'],
            );
        });

        $this->app->singleton(DKBSignManager::class, function ($app) {
            $config = $app['config']['dkbsign'];

            return new DKBSignManager(
                $app->make(DKBSignClient::class),
                rtrim((string) $config['base_url'], '/'),
                (string) ($config['token'] ?? ''),
                (int) $config['timeout'],
            );
        });

        $this->app->bind(DKBSign::class, function ($app) {
            return $app->make(DKBSignManager::class)->sign();
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/dkbsign.php' => config_path('dkbsign.php'),
            ], 'dkbsign-config');
        }
    }
}
