<?php

namespace Dkbs\LaravelDkbsign;

use Dkbs\LaravelDkbsign\Http\DkbsignClient;
use Illuminate\Support\ServiceProvider;

class DkbsignServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/dkbsign.php', 'dkbsign');

        $this->app->singleton(DkbsignClient::class, function ($app) {
            $config = $app['config']['dkbsign'];

            return new DkbsignClient(
                rtrim((string) $config['base_url'], '/'),
                (string) ($config['token'] ?? ''),
                (int) $config['timeout'],
            );
        });

        $this->app->singleton(DkbsignManager::class, function ($app) {
            $config = $app['config']['dkbsign'];

            return new DkbsignManager(
                $app->make(DkbsignClient::class),
                rtrim((string) $config['base_url'], '/'),
                (string) ($config['token'] ?? ''),
                (int) $config['timeout'],
            );
        });

        $this->app->bind(Dkbsign::class, function ($app) {
            return $app->make(DkbsignManager::class)->sign();
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
