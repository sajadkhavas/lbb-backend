<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackendContractFreezeTest extends TestCase
{
    use RefreshDatabase;

    public function test_b2_openapi_is_system_only_and_backend_freeze_remains_closed(): void
    {
        $document = $this->getJson('/api/system/openapi')
            ->assertOk()
            ->json();

        $this->assertSame('3.1.0', $document['openapi']);
        $this->assertSame('2026-08-07-f14-be-b2', $document['info']['version']);
        $this->assertArrayHasKey('/api/system/openapi', $document['paths']);
        $this->assertArrayNotHasKey('/api/catalog/products', $document['paths']);

        $this->getJson('/api/system/contracts')
            ->assertOk()
            ->assertJsonPath('data.contracts.domain_cleanup.status', 'ready')
            ->assertJsonPath('data.contracts.apparel_domain.status', 'not-started')
            ->assertJsonPath('data.contracts.backend_freeze.status', 'not-ready');

        $this->assertSame(1, Artisan::call('backend:readiness', ['--json' => true]));
    }
}
