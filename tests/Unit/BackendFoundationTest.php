<?php

namespace Tests\Unit;

use Tests\TestCase;

class BackendFoundationTest extends TestCase
{
    public function test_frontend_origins_are_configured_as_an_array(): void
    {
        $origins = config('winimi.frontend_origins');

        $this->assertIsArray($origins);
        $this->assertNotEmpty($origins);
        $this->assertSame($origins, config('cors.allowed_origins'));
        $this->assertTrue((bool) config('cors.supports_credentials'));
    }

    public function test_only_completed_commerce_contracts_are_reported_as_implemented(): void
    {
        $contracts = config('winimi.contracts');

        $this->assertSame('implemented', $contracts['system']['status']);
        $this->assertSame('implemented', $contracts['catalog']['status']);
        $this->assertSame('bakery-catalog', $contracts['catalog']['source']);
        $this->assertSame('implemented', $contracts['authentication']['status']);
        $this->assertSame('customer-session-otp', $contracts['authentication']['source']);
        $this->assertSame('implemented', $contracts['orders']['status']);
        $this->assertSame('transactional-order-reservations', $contracts['orders']['source']);
        $this->assertSame('implemented', $contracts['payments']['status']);
        $this->assertSame('provider-ready-payment-attempts', $contracts['payments']['source']);
        $this->assertSame(
            'disabled-until-external-credentials',
            $contracts['payments']['activation'],
        );
        $this->assertSame('implemented', $contracts['store_operations']['status']);
        $this->assertSame(
            'delivery-content-reviews-inquiries-notification-outbox',
            $contracts['store_operations']['source'],
        );
        $this->assertSame(
            'sms-disabled-until-external-credentials',
            $contracts['store_operations']['activation'],
        );
    }
}
