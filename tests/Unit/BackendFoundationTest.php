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

    public function test_be_e_contract_reports_commerce_ready_but_not_frozen(): void
    {
        $contracts = config('lbb.contracts');

        $this->assertSame('implemented', $contracts['system']['status']);
        $this->assertSame('ready', $contracts['domain_cleanup']['status']);
        $this->assertSame('public-v1-ready', $contracts['catalog']['status']);
        $this->assertSame('ready', $contracts['apparel_domain']['status']);
        $this->assertSame('ready-for-commerce', $contracts['authentication']['status']);
        $this->assertSame('commerce-operations-ready', $contracts['orders']['status']);
        $this->assertSame('provider-ready-fail-closed', $contracts['payments']['status']);
        $this->assertSame('commerce-operations-ready', $contracts['store_operations']['status']);
        $this->assertSame('not-ready', $contracts['backend_freeze']['status']);
        $this->assertFalse((bool) config('lbb.launch.backend_complete'));
    }
}
