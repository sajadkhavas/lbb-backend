<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackendContractFreezeTest extends TestCase
{
    use RefreshDatabase;

    public function test_pre_freeze_openapi_remains_system_only_while_apparel_and_commerce_are_ready(): void
    {
        $document = $this->getJson('/api/system/openapi')
            ->assertOk()
            ->json();

        $this->assertSame('3.1.0', $document['openapi']);
        $this->assertSame('2026-08-07-f14-be-b2', $document['info']['version']);
        $this->assertArrayHasKey('/api/system/openapi', $document['paths']);
        $this->assertArrayNotHasKey('/api/v1/products', $document['paths']);
        $this->assertArrayNotHasKey('/api/v1/checkout/commit', $document['paths']);

        $this->getJson('/api/system/contracts')
            ->assertOk()
            ->assertJsonPath('data.contracts.domain_cleanup.status', 'ready')
            ->assertJsonPath('data.contracts.apparel_domain.status', 'ready')
            ->assertJsonPath('data.contracts.catalog.status', 'public-v1-ready')
            ->assertJsonPath('data.contracts.orders.status', 'commerce-operations-ready')
            ->assertJsonPath('data.contracts.backend_freeze.status', 'not-ready');

        $this->assertSame(1, Artisan::call('backend:readiness', ['--json' => true]));
    }
}
