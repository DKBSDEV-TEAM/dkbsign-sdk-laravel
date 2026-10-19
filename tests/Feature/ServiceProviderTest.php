<?php

namespace Dkbs\LaravelDkbsign\Tests\Feature;

use Dkbs\LaravelDkbsign\Dkbsign;
use Dkbs\LaravelDkbsign\DkbsignManager;
use Dkbs\LaravelDkbsign\Facades\Dkbsign as DkbsignFacade;
use Dkbs\LaravelDkbsign\Http\DkbsignClient;
use Dkbs\LaravelDkbsign\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_container_resolves_http_client(): void
    {
        $this->assertInstanceOf(DkbsignClient::class, $this->app->make(DkbsignClient::class));
    }

    public function test_container_resolves_manager(): void
    {
        $this->assertInstanceOf(DkbsignManager::class, $this->app->make(DkbsignManager::class));
    }

    public function test_sign_binding_returns_fresh_dkbsign_instance(): void
    {
        $first = $this->app->make(Dkbsign::class);
        $second = $this->app->make(Dkbsign::class);

        $this->assertNotSame($first, $second);
    }

    public function test_facade_resolves_to_manager(): void
    {
        $this->assertInstanceOf(DkbsignManager::class, DkbsignFacade::getFacadeRoot());
    }
}
