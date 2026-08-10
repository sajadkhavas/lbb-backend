<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\PushSubscription;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WebPushSubscriptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'session.driver' => 'array',
            'lbb.web_push.enabled' => true,
            'lbb.web_push.vapid.subject' => 'https://lbb.example.test',
            'lbb.web_push.vapid.public_key' => 'BPublicKeyForContractOnly',
            'lbb.web_push.vapid.private_key' => 'PrivateKeyMustNeverBeReturned',
        ]);
    }

    public function test_public_configuration_never_exposes_private_vapid_key(): void
    {
        $response = $this->getJson('/api/web-push/config')
            ->assertOk()
            ->assertJsonPath('data.enabled', true)
            ->assertJsonPath('data.publicKey', 'BPublicKeyForContractOnly');

        $this->assertStringNotContainsString('PrivateKeyMustNeverBeReturned', $response->getContent());
    }

    public function test_authenticated_customer_can_register_and_revoke_an_encrypted_subscription(): void
    {
        $customer = $this->customer();
        $this->actingAs($customer, 'customer');
        $endpoint = 'https://push.example.test/subscriptions/device-1';
        $p256dh = str_repeat('p', 88);
        $auth = str_repeat('a', 24);

        $this->postJson('/api/web-push/subscriptions', [
            'endpoint' => $endpoint,
            'keys' => ['p256dh' => $p256dh, 'auth' => $auth],
            'contentEncoding' => 'aes128gcm',
        ])->assertCreated()
            ->assertJsonPath('data.activeCount', 1)
            ->assertJsonMissing(['endpoint' => $endpoint]);

        $subscription = PushSubscription::query()->firstOrFail();
        $this->assertSame($endpoint, $subscription->endpoint);
        $this->assertSame($p256dh, $subscription->p256dh);
        $this->assertSame($auth, $subscription->auth_token);

        $raw = DB::table('push_subscriptions')->first();
        $this->assertNotSame($endpoint, $raw->endpoint);
        $this->assertNotSame($p256dh, $raw->p256dh);
        $this->assertNotSame($auth, $raw->auth_token);

        $this->getJson('/api/web-push/subscriptions')
            ->assertOk()
            ->assertJsonPath('data.activeCount', 1);

        $this->deleteJson('/api/web-push/subscriptions', ['endpoint' => $endpoint])
            ->assertOk()
            ->assertJsonPath('data.activeCount', 0);

        $this->assertNotNull($subscription->fresh()->revoked_at);
    }

    public function test_subscription_mutations_require_customer_authentication(): void
    {
        $payload = [
            'endpoint' => 'https://push.example.test/subscriptions/device-2',
            'keys' => ['p256dh' => str_repeat('p', 88), 'auth' => str_repeat('a', 24)],
        ];

        $this->postJson('/api/web-push/subscriptions', $payload)->assertUnauthorized();
        $this->deleteJson('/api/web-push/subscriptions', ['endpoint' => $payload['endpoint']])->assertUnauthorized();
        $this->postJson('/api/web-push/test')->assertUnauthorized();
    }

    public function test_disabled_push_is_fail_closed(): void
    {
        config(['lbb.web_push.enabled' => false]);
        $customer = $this->customer('09123456781');
        $this->actingAs($customer, 'customer');

        $this->getJson('/api/web-push/config')
            ->assertOk()
            ->assertJsonPath('data.enabled', false)
            ->assertJsonPath('data.publicKey', null);

        $this->postJson('/api/web-push/test')->assertServiceUnavailable();
    }

    private function customer(string $mobile = '09123456780'): Customer
    {
        return Customer::query()->create([
            'mobile' => $mobile,
            'mobile_verified_at' => now(),
            'is_active' => true,
        ]);
    }
}
