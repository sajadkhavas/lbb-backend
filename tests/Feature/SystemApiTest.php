<?php

namespace Tests\Feature;

use Tests\TestCase;

class SystemApiTest extends TestCase
{
    public function test_health_endpoint_returns_current_contract_metadata(): void
    {
        $this->getJson('/api/system/health', ['X-Request-ID' => 'test-request-id'])
            ->assertOk()
            ->assertHeader('X-Request-ID', 'test-request-id')
            ->assertHeader('X-API-Version', '1')
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.service', 'lbb-backend')
            ->assertJsonPath('meta.contractVersion', '2026-08-09-f14-be-f');
    }

    public function test_meta_reports_frozen_contract_without_claiming_final_acceptance(): void
    {
        $this->getJson('/api/system/meta')
            ->assertOk()
            ->assertJsonPath('data.brand.nameEn', 'LBB')
            ->assertJsonPath('data.contractVersion', '2026-08-09-f14-be-f')
            ->assertJsonPath('data.backendComplete', false)
            ->assertJsonPath('data.openApiUrl', '/api/system/openapi');
    }

    public function test_contract_endpoint_reports_backend_freeze_without_claiming_frontend_or_deployment(): void
    {
        $this->getJson('/api/system/contracts')
            ->assertOk()
            ->assertJsonPath('data.contracts.system.status', 'implemented')
            ->assertJsonPath('data.contracts.domain_cleanup.status', 'ready')
            ->assertJsonPath('data.contracts.catalog.status', 'public-v1-ready')
            ->assertJsonPath('data.contracts.apparel_domain.status', 'ready')
            ->assertJsonPath('data.contracts.authentication.status', 'ready-for-commerce')
            ->assertJsonPath('data.contracts.orders.status', 'commerce-operations-ready')
            ->assertJsonPath('data.contracts.payments.status', 'provider-ready-fail-closed')
            ->assertJsonPath('data.contracts.store_operations.status', 'commerce-operations-ready')
            ->assertJsonPath('data.contracts.backend_freeze.status', 'ready')
            ->assertJsonPath('data.contracts.backend_freeze.source', 'f14-be-f')
            ->assertJsonPath('data.launch.backend_complete', false)
            ->assertJsonPath('data.launch.frontend_integrated', false)
            ->assertJsonPath('data.launch.production_deployed', false);
    }

    public function test_unknown_api_routes_use_standard_json_error(): void
    {
        $this->getJson('/api/does-not-exist')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'resource_not_found')
            ->assertJsonPath('meta.contractVersion', '2026-08-09-f14-be-f');
    }
}
