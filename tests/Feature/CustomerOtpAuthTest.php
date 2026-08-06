<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\OtpChallenge;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerOtpAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'winimi.otp.provider' => 'testing',
            'winimi.otp.expose_test_code' => true,
            'winimi.otp.retry_after_seconds' => 0,
            'winimi.otp.expires_seconds' => 120,
            'winimi.otp.max_attempts' => 5,
            'session.driver' => 'array',
        ]);
    }

    public function test_otp_request_normalizes_mobile_and_never_stores_plain_code_or_mobile_payload(): void
    {
        $response = $this->stateful()->postJson('/api/auth/otp/request', [
            'mobile' => '۰۹۱۲ ۳۴۵ ۶۷۸۹',
        ]);

        $response
            ->assertAccepted()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.expiresIn', 120)
            ->assertJsonStructure(['data' => ['challengeId', 'debugCode']]);

        $challenge = OtpChallenge::query()->firstOrFail();
        $debugCode = (string) $response->json('data.debugCode');
        $rawPayload = DB::table('otp_challenges')->value('mobile_payload');

        $this->assertSame('09123456789', $challenge->mobile_payload);
        $this->assertNotSame('09123456789', $rawPayload);
        $this->assertNotSame($debugCode, $challenge->code_hash);
        $this->assertTrue(Hash::check($debugCode, $challenge->code_hash));
    }

    public function test_customer_can_verify_otp_use_session_update_profile_and_logout(): void
    {
        $challenge = $this->requestChallenge('09123456780');

        $verify = $this->stateful()->postJson('/api/auth/otp/verify', [
            'mobile' => '989123456780',
            'challengeId' => $challenge['challengeId'],
            'code' => $challenge['code'],
        ]);

        $verify
            ->assertOk()
            ->assertJsonPath('data.user.mobile', '09123456780')
            ->assertJsonPath('data.user.mobileVerified', true);

        $customer = Customer::query()->where('mobile', '09123456780')->firstOrFail();
        $this->assertAuthenticatedAs($customer, 'customer');

        $this->stateful()->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.id', $customer->public_id);

        $this->stateful()->patchJson('/api/account/profile', [
            'fullName' => 'سجاد خواص',
            'email' => 'sajad@example.test',
            'marketingConsent' => true,
        ])->assertOk()
            ->assertJsonPath('data.user.fullName', 'سجاد خواص')
            ->assertJsonPath('data.user.email', 'sajad@example.test')
            ->assertJsonPath('data.user.marketingConsent', true);

        $this->stateful()->postJson('/api/auth/logout')
            ->assertOk();

        $this->stateful()->getJson('/api/auth/me')
            ->assertUnauthorized();
    }

    public function test_disabling_customer_invalidates_an_existing_session(): void
    {
        $challenge = $this->requestChallenge('09123456785');

        $this->stateful()->postJson('/api/auth/otp/verify', [
            'mobile' => '09123456785',
            'challengeId' => $challenge['challengeId'],
            'code' => $challenge['code'],
        ])->assertOk();

        $customer = Customer::query()->where('mobile', '09123456785')->firstOrFail();
        $customer->update(['is_active' => false]);

        $this->stateful()->getJson('/api/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('success', false);

        $this->assertGuest('customer');
    }

    public function test_wrong_code_increments_attempts_and_consumed_code_cannot_be_reused(): void
    {
        $challenge = $this->requestChallenge('09123456781');

        $this->stateful()->postJson('/api/auth/otp/verify', [
            'mobile' => '09123456781',
            'challengeId' => $challenge['challengeId'],
            'code' => $this->wrongCode($challenge['code']),
        ])->assertUnprocessable();

        $this->assertSame(1, OtpChallenge::query()->firstOrFail()->attempts);

        $payload = [
            'mobile' => '09123456781',
            'challengeId' => $challenge['challengeId'],
            'code' => $challenge['code'],
        ];

        $this->stateful()->postJson('/api/auth/otp/verify', $payload)->assertOk();
        $this->stateful()->postJson('/api/auth/otp/verify', $payload)->assertUnprocessable();
    }

    public function test_challenge_is_locked_after_maximum_failed_attempts(): void
    {
        config(['winimi.otp.max_attempts' => 2]);
        $challenge = $this->requestChallenge('09123456782');
        $wrongCode = $this->wrongCode($challenge['code']);

        for ($attempt = 0; $attempt < 2; $attempt++) {
            $this->stateful()->postJson('/api/auth/otp/verify', [
                'mobile' => '09123456782',
                'challengeId' => $challenge['challengeId'],
                'code' => $wrongCode,
            ])->assertUnprocessable();
        }

        $this->stateful()->postJson('/api/auth/otp/verify', [
            'mobile' => '09123456782',
            'challengeId' => $challenge['challengeId'],
            'code' => $challenge['code'],
        ])->assertUnprocessable();

        $this->assertDatabaseCount('customers', 0);
        $this->assertSame(2, OtpChallenge::query()->firstOrFail()->attempts);
    }

    public function test_disabled_provider_returns_service_unavailable_and_removes_challenge(): void
    {
        config(['winimi.otp.provider' => 'disabled']);

        $this->stateful()->postJson('/api/auth/otp/request', [
            'mobile' => '09123456783',
        ])->assertServiceUnavailable();

        $this->assertDatabaseCount('otp_challenges', 0);
    }

    public function test_resend_cooldown_returns_retry_after_without_creating_another_challenge(): void
    {
        config(['winimi.otp.retry_after_seconds' => 60]);
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

    public function test_authentication_orders_and_payments_contracts_are_implemented_with_external_activation_disabled(): void
    {
        $this->getJson('/api/system/contracts')
            ->assertOk()
            ->assertJsonPath('data.contracts.authentication.status', 'implemented')
            ->assertJsonPath('data.contracts.orders.status', 'implemented')
            ->assertJsonPath('data.contracts.payments.status', 'implemented')
            ->assertJsonPath(
                'data.contracts.payments.activation',
                'disabled-until-external-credentials',
            );
    }

    private function requestChallenge(string $mobile): array
    {
        $response = $this->stateful()->postJson('/api/auth/otp/request', [
            'mobile' => $mobile,
        ])->assertAccepted();

        return [
            'challengeId' => (string) $response->json('data.challengeId'),
            'code' => (string) $response->json('data.debugCode'),
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
            'User-Agent' => 'Winimi-Test-Client/1.0',
        ]);
    }
}
