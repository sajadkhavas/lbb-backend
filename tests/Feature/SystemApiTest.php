<?php

namespace Tests\Feature;

use Tests\TestCase;

class SystemApiTest extends TestCase
{
    public function test_health_endpoint_returns_b2_metadata(): void
    {
        $this->getJson('/api/system/health', ['X-Request-ID' => 'test-request-id'])
            ->assertOk()
            ->assertHeader('X-Request-ID', 'test-request-id')
            ->assertHeader('X-API-Version', '1')
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.service', 'lbb-backend')
            ->assertJsonPath('meta.contractVersion', '2026-08-07-f14-be-b2');
    }

    public function test_meta_reports_neutral_baseline_without_claiming_backend_completion(): void
    {
        $this->getJson('/api/system/meta')
            ->assertOk()
            ->assertJsonPath('data.brand.nameEn', 'LBB')
            ->assertJsonPath('data.contractVersion', '2026-08-07-f14-be-b2')
            ->assertJsonPath('data.backendComplete', false)
            ->assertJsonPath('data.openApiUrl', '/api/system/openapi');
    }

    public function test_contract_endpoint_is_fail_closed_until_apparel_domain_is_built(): void
    {
        $this->getJson('/api/system/contracts')
            ->assertOk()
            ->assertJsonPath('data.contracts.system.status', 'implemented')
            ->assertJsonPath('data.contracts.domain_cleanup.status', 'ready')
            ->assertJsonPath('data.contracts.catalog.status', 'neutral-baseline-ready')
            ->assertJsonPath('data.contracts.apparel_domain.status', 'not-started')
            ->assertJsonPath('data.contracts.apparel_domain.target_phase', 'F14-BE-C')
            ->assertJsonPath('data.contracts.backend_freeze.status', 'not-ready')
            ->assertJsonPath('data.launch.backend_complete', false)
            ->assertJsonPath('data.launch.production_deployed', false);
    }

    public function test_unknown_api_routes_use_standard_json_error(): void
    {
        $this->getJson('/api/does-not-exist')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'resource_not_found')
            ->assertJsonPath('meta.contractVersion', '2026-08-07-f14-be-b2');
    }
}
