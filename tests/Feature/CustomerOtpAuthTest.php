<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\OtpChallenge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerOtpAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'lbb.otp.provider' => 'testing',
            'lbb.otp.expose_test_code' => true,
            'lbb.otp.retry_after_seconds' => 0,
            'session.driver' => 'array',
        ]);
    }

    public function test_otp_request_normalizes_mobile_and_never_stores_plain_code_or_mobile_in_cache_key(): void
    {
        $response = $this->stateful()->postJson('/api/auth/otp/request', [
            'mobile' => '+98 912 345 6789',
        ])->assertAccepted();

        $challengeId = (string) $response->json('data.challengeId');
        $debugCode = (string) $response->json('data.debugCode');

        $this->assertSame(6, strlen($debugCode));
        $this->assertDatabaseHas('otp_challenges', [
            'public_id' => $challengeId,
            'mobile' => '09123456789',
        ]);

        $challenge = OtpChallenge::query()->where('public_id', $challengeId)->firstOrFail();
        $this->assertNotSame($debugCode, $challenge->code_hash);
        $this->assertStringNotContainsString('09123456789', (string) $challenge->request_key);
    }

    public function test_customer_can_verify_otp_use_session_update_profile_and_logout(): void
    {
        [$challengeId, $code] = $this->requestChallenge('09123456781');

        $this->stateful()->postJson('/api/auth/otp/verify', [
            'mobile' => '09123456781',
            'challengeId' => $challengeId,
            'code' => $code,
        ])->assertOk()
            ->assertJsonPath('data.user.mobile', '09123456781')
            ->assertJsonPath('data.user.mobileVerified', true);

        $this->stateful()->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.mobile', '09123456781');

        $this->stateful()->patchJson('/api/account/profile', [
            'fullName' => 'مشتری تست',
            'email' => 'customer@example.com',
            'marketingConsent' => true,
        ])->assertOk()
            ->assertJsonPath('data.user.fullName', 'مشتری تست')
            ->assertJsonPath('data.user.email', 'customer@example.com')
            ->assertJsonPath('data.user.marketingConsent', true);

        $this->stateful()->postJson('/api/auth/logout')->assertOk();
        $this->stateful()->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_disabling_customer_invalidates_an_existing_session(): void
    {
        [$challengeId, $code] = $this->requestChallenge('09123456782');

        $this->stateful()->postJson('/api/auth/otp/verify', [
            'mobile' => '09123456782',
            'challengeId' => $challengeId,
            'code' => $code,
        ])->assertOk();

        Customer::query()->where('mobile', '09123456782')->update(['is_active' => false]);

        $this->stateful()->getJson('/api/auth/me')->assertForbidden();
    }

    public function test_wrong_code_increments_attempts_and_consumed_code_cannot_be_reused(): void
    {
        [$challengeId, $code] = $this->requestChallenge('09123456783');

        $this->stateful()->postJson('/api/auth/otp/verify', [
            'mobile' => '09123456783',
            'challengeId' => $challengeId,
            'code' => $this->wrongCode($code),
        ])->assertUnprocessable();

        $this->assertDatabaseHas('otp_challenges', [
            'public_id' => $challengeId,
            'attempts' => 1,
        ]);

        $this->stateful()->postJson('/api/auth/otp/verify', [
            'mobile' => '09123456783',
            'challengeId' => $challengeId,
            'code' => $code,
        ])->assertOk();

        $this->stateful()->postJson('/api/auth/otp/verify', [
            'mobile' => '09123456783',
            'challengeId' => $challengeId,
            'code' => $code,
        ])->assertUnprocessable();
    }

    public function test_challenge_is_locked_after_maximum_failed_attempts(): void
    {
        config(['lbb.otp.max_attempts' => 2]);
        [$challengeId, $code] = $this->requestChallenge('09123456785');
        $wrong = $this->wrongCode($code);

        $this->stateful()->postJson('/api/auth/otp/verify', [
            'mobile' => '09123456785',
            'challengeId' => $challengeId,
            'code' => $wrong,
        ])->assertUnprocessable();

        $this->stateful()->postJson('/api/auth/otp/verify', [
            'mobile' => '09123456785',
            'challengeId' => $challengeId,
            'code' => $wrong,
        ])->assertTooManyRequests();
    }

    public function test_disabled_provider_returns_service_unavailable_and_removes_challenge(): void
    {
        config(['lbb.otp.provider' => 'disabled']);

        $this->stateful()->postJson('/api/auth/otp/request', [
            'mobile' => '09123456786',
        ])->assertServiceUnavailable();

        $this->assertDatabaseCount('otp_challenges', 0);
    }

    public function test_resend_cooldown_returns_retry_after_without_creating_another_challenge(): void
    {
        config(['lbb.otp.retry_after_seconds' => 120]);
        $this->requestChallenge('09123456784');

        $response = $this->stateful()->postJson('/api/auth/otp/request', [
            'mobile' => '09123456784',
        ]);

        $response
            ->assertTooManyRequests()
            ->assertHeader('Retry-After')
            ->assertJsonPath('success', false);

        $this->assertDatabaseCount('otp_challenges', 1);
    }

    public function test_customer_contracts_remain_commerce_ready_after_p3_storefront_amendment(): void
    {
        $this->getJson('/api/system/contracts')
            ->assertOk()
            ->assertJsonPath('data.contractVersion', '2026-09-06-p3-storefront-v1')
            ->assertJsonPath('data.contracts.authentication.status', 'public-v1-ready')
            ->assertJsonPath('data.contracts.authentication.source', 'f14-be-f1')
            ->assertJsonPath('data.contracts.orders.status', 'commerce-operations-ready')
            ->assertJsonPath('data.contracts.payments.status', 'provider-ready-fail-closed')
            ->assertJsonPath('data.contracts.storefront_content.status', 'public-v1-ready')
            ->assertJsonPath('data.contracts.backend_freeze.status', 'ready')
            ->assertJsonPath('data.contracts.backend_freeze.contract_version', '2026-09-06-p3-storefront-v1')
            ->assertJsonPath('data.launch.backend_complete', true)
            ->assertJsonPath('data.launch.frontend_integrated', false)
            ->assertJsonPath('data.launch.production_deployed', false);
    }

    private function requestChallenge(string $mobile): array
    {
        $response = $this->stateful()->postJson('/api/auth/otp/request', [
            'mobile' => $mobile,
        ])->assertAccepted();

        return [
            (string) $response->json('data.challengeId'),
            (string) $response->json('data.debugCode'),
        ];
    }

    private function wrongCode(string $code): string
    {
        return $code === '000000' ? '111111' : '000000';
    }

    private function stateful(): static
    {
        return $this->withHeaders([
            'Origin' => 'http://localhost:5173',
            'Referer' => 'http://localhost:5173/',
            'User-Agent' => 'LBB-Customer-Contract-Test/1.0',
        ]);
    }
}
