<?php

namespace Tests\Feature;

use Tests\TestCase;

class SystemApiTest extends TestCase
{
    public function test_health_endpoint_returns_standard_metadata(): void
    {
        $response = $this->getJson('/api/system/health', [
            'X-Request-ID' => 'test-request-id',
        ]);

        $response
            ->assertOk()
            ->assertHeader('X-Request-ID', 'test-request-id')
            ->assertHeader('X-API-Version', '1')
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.service', 'winimi-bakery-backend')
            ->assertJsonPath('meta.requestId', 'test-request-id')
            ->assertJsonPath('meta.contractVersion', '2026-07-20-phase-16');
    }

    public function test_meta_endpoint_exposes_the_frozen_contract_and_phase_18_roadmap_identity(): void
    {
        $this->getJson('/api/system/meta')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.brand.nameEn', 'Winimi Bakery')
            ->assertJsonPath('data.contractVersion', '2026-07-20-phase-16')
            ->assertJsonPath('data.roadmapVersion', '2026-07-20-phase-18')
            ->assertJsonPath('data.openApiUrl', '/api/system/openapi')
            ->assertJsonPath('data.legacyApiEnabled', true);
    }

    public function test_contract_endpoint_reports_frozen_backend_and_verified_acceptance_gates(): void
    {
        $response = $this->getJson('/api/system/contracts');

        $response
            ->assertOk()
            ->assertJsonPath('data.contracts.system.status', 'implemented')
            ->assertJsonPath('data.contracts.catalog.status', 'implemented')
            ->assertJsonPath('data.contracts.catalog.source', 'bakery-catalog')
            ->assertJsonPath('data.contracts.authentication.status', 'implemented')
            ->assertJsonPath('data.contracts.authentication.source', 'customer-session-otp')
            ->assertJsonPath('data.contracts.orders.status', 'implemented')
            ->assertJsonPath('data.contracts.orders.source', 'transactional-order-reservations')
            ->assertJsonPath('data.contracts.payments.status', 'implemented')
            ->assertJsonPath('data.contracts.payments.source', 'provider-ready-payment-attempts')
            ->assertJsonPath(
                'data.contracts.payments.activation',
                'disabled-until-external-credentials',
            )
            ->assertJsonPath('data.contracts.store_operations.status', 'implemented')
            ->assertJsonPath(
                'data.contracts.store_operations.source',
                'delivery-content-reviews-inquiries-notification-outbox',
            )
            ->assertJsonPath('data.contracts.backend_freeze.status', 'ready')
            ->assertJsonPath('data.contracts.backend_freeze.schema', '/api/system/openapi')
            ->assertJsonPath('data.launch.strategy', 'complete-internal-work-before-external-activation')
            ->assertJsonPath('data.launch.roadmap_version', '2026-07-20-phase-18')
            ->assertJsonPath('data.launch.internal_gates.backend_complete.status', 'ready')
            ->assertJsonPath('data.launch.internal_gates.backend_complete.target_phase', 16)
            ->assertJsonPath('data.launch.internal_gates.frontend_integrated.status', 'ready')
            ->assertJsonPath('data.launch.internal_gates.frontend_integrated.target_phase', 17)
            ->assertJsonPath('data.launch.internal_gates.end_to_end_verified.status', 'ready')
            ->assertJsonPath('data.launch.internal_gates.end_to_end_verified.target_phase', 18)
            ->assertJsonPath('data.launch.internal_gates.production_deployed.status', 'not-started')
            ->assertJsonPath('data.launch.internal_gates.production_deployed.target_phase', 19)
            ->assertJsonPath(
                'data.launch.internal_gates.production_deployed.topology',
                'single-server-two-virtual-hosts',
            )
            ->assertJsonPath('data.launch.external_only.payment_gateway_credentials.status', 'pending-external')
            ->assertJsonPath('data.launch.external_only.enamad_badge_code.status', 'pending-external')
            ->assertJsonPath('data.launch.external_only.sms_provider_credentials.status', 'pending-external')
            ->assertJsonCount(3, 'data.launch.external_only');
    }

    public function test_unknown_api_routes_render_frozen_json_error(): void
    {
        $this->getJson('/api/does-not-exist')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'resource_not_found')
            ->assertJsonPath('meta.contractVersion', '2026-07-20-phase-16');
    }
}
