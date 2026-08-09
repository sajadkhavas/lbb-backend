<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackendFinalAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_backend_is_accepted_on_the_exact_amended_frozen_contract_without_claiming_frontend_or_deployment(): void
    {
        $this->getJson('/api/system/contracts')
            ->assertOk()
            ->assertJsonPath('data.contractVersion', '2026-08-09-f14-be-f1')
            ->assertJsonPath('data.contracts.domain_cleanup.status', 'ready')
            ->assertJsonPath('data.contracts.apparel_domain.status', 'ready')
            ->assertJsonPath('data.contracts.catalog.status', 'public-v1-ready')
            ->assertJsonPath('data.contracts.authentication.status', 'public-v1-ready')
            ->assertJsonPath('data.contracts.authentication.source', 'f14-be-f1')
            ->assertJsonPath('data.contracts.orders.status', 'commerce-operations-ready')
            ->assertJsonPath('data.contracts.payments.status', 'provider-ready-fail-closed')
            ->assertJsonPath('data.contracts.store_operations.status', 'commerce-operations-ready')
            ->assertJsonPath('data.contracts.backend_freeze.status', 'ready')
            ->assertJsonPath('data.contracts.backend_freeze.source', 'f14-be-f1')
            ->assertJsonPath('data.contracts.backend_freeze.contract_version', '2026-08-09-f14-be-f1')
            ->assertJsonPath('data.launch.backend_complete', true)
            ->assertJsonPath('data.launch.frontend_integrated', false)
            ->assertJsonPath('data.launch.production_deployed', false);

        $this->assertSame(0, Artisan::call('backend:readiness', ['--json' => true]));
    }

    public function test_final_acceptance_preserves_amended_openapi_freeze_and_commerce_truth_boundaries(): void
    {
        $document = $this->getJson('/api/system/openapi')
            ->assertOk()
            ->assertHeader('ETag')
            ->json();

        $this->assertSame('3.1.0', $document['openapi']);
        $this->assertSame('2026-08-09-f14-be-f1', $document['info']['version']);
        $this->assertSame('F14-BE-F1', $document['x-lbb-freeze']['phase']);
        $this->assertSame('2026-08-09-f14-be-f', $document['x-lbb-freeze']['amends']);
        $this->assertSame('integer Toman', $document['x-lbb-freeze']['money']);
        $this->assertFalse($document['x-lbb-freeze']['frontendAuthoritativePriceOrStock']);
        $this->assertFalse($document['x-lbb-freeze']['legacyCompatibilityRoutesFrozen']);
        $this->assertSame('fail-closed', $document['x-lbb-freeze']['paymentWithoutProvider']);
        $this->assertFalse($document['x-lbb-freeze']['frontendIntegrated']);
        $this->assertFalse($document['x-lbb-freeze']['productionDeployed']);

        foreach ([
            '/api/v1/auth/otp/request',
            '/api/v1/auth/otp/verify',
            '/api/v1/auth/me',
            '/api/v1/auth/logout',
            '/api/v1/products',
            '/api/v1/cart/validate',
            '/api/v1/checkout/quote',
            '/api/v1/checkout/commit',
            '/api/v1/account/orders',
            '/api/v1/orders/{orderId}/payments',
            '/api/v1/payments/verify',
            '/api/v1/orders/{orderId}/returns',
            '/api/v1/orders/{orderId}/exchanges',
            '/api/v1/refunds',
        ] as $path) {
            $this->assertArrayHasKey($path, $document['paths'], "Final acceptance missing frozen path: {$path}");
        }
    }
}
