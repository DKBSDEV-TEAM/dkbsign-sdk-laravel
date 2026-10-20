<?php

namespace Dkbs\LaravelDKBSign\Tests\Feature;

use Dkbs\LaravelDKBSign\DKBSign;
use Dkbs\LaravelDKBSign\DKBSignManager;
use Dkbs\LaravelDKBSign\EnvelopeBuilder;
use Dkbs\LaravelDKBSign\Facades\DKBSign as DKBSignFacade;
use Dkbs\LaravelDKBSign\Http\DKBSignClient;
use Dkbs\LaravelDKBSign\Tests\TestCase;

class ServiceProviderTest extends TestCase
{
    public function test_container_resolves_http_client(): void
    {
        $this->assertInstanceOf(DKBSignClient::class, $this->app->make(DKBSignClient::class));
    }

    public function test_container_resolves_manager(): void
    {
        $this->assertInstanceOf(DKBSignManager::class, $this->app->make(DKBSignManager::class));
    }

    public function test_sign_binding_returns_fresh_dkbsign_instance(): void
    {
        $first = $this->app->make(DKBSign::class);
        $second = $this->app->make(DKBSign::class);

        $this->assertNotSame($first, $second);
    }

    public function test_facade_resolves_to_manager(): void
    {
        $this->assertInstanceOf(DKBSignManager::class, DKBSignFacade::getFacadeRoot());
    }

    public function test_manager_envelope_returns_fresh_builder(): void
    {
        $manager = $this->app->make(DKBSignManager::class);

        $first = $manager->envelope();
        $second = $manager->envelope();

        $this->assertInstanceOf(EnvelopeBuilder::class, $first);
        $this->assertNotSame($first, $second);
    }

    public function test_facade_envelope_returns_fresh_builder(): void
    {
        $first = DKBSignFacade::envelope();
        $second = DKBSignFacade::envelope();

        $this->assertInstanceOf(EnvelopeBuilder::class, $first);
        $this->assertNotSame($first, $second);
    }
}
