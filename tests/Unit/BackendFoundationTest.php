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

    public function test_b2_contract_reports_only_the_neutral_baseline_as_ready(): void
    {
        $contracts = config('lbb.contracts');

        $this->assertSame('implemented', $contracts['system']['status']);
        $this->assertSame('ready', $contracts['domain_cleanup']['status']);
        $this->assertSame('neutral-baseline-ready', $contracts['catalog']['status']);
        $this->assertSame('not-started', $contracts['apparel_domain']['status']);
        $this->assertSame('F14-BE-C', $contracts['apparel_domain']['target_phase']);
        $this->assertSame('imported-pending-lbb-verification', $contracts['authentication']['status']);
        $this->assertSame('imported-pending-lbb-verification', $contracts['orders']['status']);
        $this->assertSame('disabled-pending-lbb-verification', $contracts['payments']['status']);
        $this->assertSame('not-ready', $contracts['backend_freeze']['status']);
        $this->assertFalse((bool) config('lbb.launch.backend_complete'));
    }
}
