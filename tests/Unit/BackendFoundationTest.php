<?php

namespace Tests\Unit;

use Tests\TestCase;

class BackendFoundationTest extends TestCase
{
    public function test_frontend_origins_are_configured_as_an_array(): void
    {
        $origins = config('lbb.frontend_origins');

        $this->assertIsArray($origins);
        $this->assertNotEmpty($origins);
        $this->assertSame($origins, config('cors.allowed_origins'));
        $this->assertTrue((bool) config('cors.supports_credentials'));
    }

    public function test_be_f1_preserves_backend_acceptance_and_external_boundaries(): void
    {
        $contracts = config('lbb.contracts');

        $this->assertSame('2026-08-09-f14-be-f1', config('lbb.api.contract_version'));
        $this->assertSame('implemented', $contracts['system']['status']);
        $this->assertSame('ready', $contracts['domain_cleanup']['status']);
        $this->assertSame('public-v1-ready', $contracts['catalog']['status']);
        $this->assertSame('ready', $contracts['apparel_domain']['status']);
        $this->assertSame('public-v1-ready', $contracts['authentication']['status']);
        $this->assertSame('f14-be-f1', $contracts['authentication']['source']);
        $this->assertSame('commerce-operations-ready', $contracts['orders']['status']);
        $this->assertSame('provider-ready-fail-closed', $contracts['payments']['status']);
        $this->assertSame('commerce-operations-ready', $contracts['store_operations']['status']);
        $this->assertSame('ready', $contracts['backend_freeze']['status']);
        $this->assertSame('f14-be-f1', $contracts['backend_freeze']['source']);
        $this->assertSame('2026-08-09-f14-be-f1', $contracts['backend_freeze']['contract_version']);
        $this->assertTrue((bool) config('lbb.launch.backend_complete'));
        $this->assertFalse((bool) config('lbb.launch.frontend_integrated'));
        $this->assertFalse((bool) config('lbb.launch.production_deployed'));
    }
}
