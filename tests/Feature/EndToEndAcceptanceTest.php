<?php

namespace Tests\Feature;

use App\Enums\InventoryReservationStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\BakeryProductVariant;
use App\Models\Order;
use Database\Seeders\WinimiStagingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EndToEndAcceptanceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'session.driver' => 'array',
            'sanctum.stateful' => ['localhost:5173', '127.0.0.1:4173'],
            'winimi.otp.provider' => 'testing',
            'winimi.otp.expose_test_code' => true,
            'winimi.otp.retry_after_seconds' => 0,
            'winimi.checkout.enabled' => true,
            'winimi.payment.enabled' => true,
            'winimi.payment.provider' => 'testing',
            'winimi.payment.callback_url' => 'http://127.0.0.1:4173/payment/callback',
        ]);

        $this->seed(WinimiStagingSeeder::class);
    }

    public function test_public_contract_catalog_content_and_external_activation_boundary_are_acceptance_ready(): void
    {
        $this->stateful()->getJson('/api/system/contracts')
            ->assertOk()
            ->assertJsonPath('meta.contractVersion', '2026-07-20-phase-16')
            ->assertJsonPath('data.launch.internal_gates.backend_complete.status', 'ready')
            ->assertJsonPath('data.launch.internal_gates.frontend_integrated.status', 'ready')
            ->assertJsonPath('data.launch.internal_gates.end_to_end_verified.status', 'ready')
            ->assertJsonPath('data.launch.internal_gates.production_deployed.status', 'not-started');

        $this->assertCount(3, config('winimi.launch.external_only'));

        $this->stateful()->getJson('/api/catalog/products?sort=featured&perPage=12')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.pagination.total', 3)
            ->assertJsonFragment(['slug' => 'staging-chocolate-cookie'])
            ->assertJsonFragment(['slug' => 'staging-chilled-cake']);

        $this->stateful()->getJson('/api/catalog/products/staging-chocolate-cookie')
            ->assertOk()
            ->assertJsonPath('data.inventoryVerified', true)
            ->assertJsonPath('data.requiresCooling', false);

        $this->stateful()->getJson('/api/store/pages/staging-shipping-policy')
            ->assertOk()
            ->assertJsonPath('data.page.title', 'سیاست ارسال تست');

        $this->stateful()->getJson('/api/store/posts/staging-welcome')
            ->assertOk()
            ->assertJsonPath('data.post.author', 'Winimi QA');

        $this->stateful()->getJson('/api/store/faqs?category=staging')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    public function test_customer_can_complete_otp_address_checkout_testing_payment_and_duplicate_callback_flow(): void
    {
        $challenge = $this->stateful()->postJson('/api/auth/otp/request', [
            'mobile' => '09000000000',
        ])->assertAccepted();

        $this->stateful()->postJson('/api/auth/otp/verify', [
            'mobile' => '09000000000',
            'challengeId' => $challenge->json('data.challengeId'),
            'code' => $challenge->json('data.debugCode'),
        ])->assertOk()
            ->assertJsonPath('data.user.mobile', '09000000000');

        $this->stateful()->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.user.fullName', 'مشتری تست پذیرش');

        $addressId = $this->stateful()->postJson('/api/account/addresses', [
            'title' => 'خانه تست تهران',
            'recipientName' => 'مشتری تست پذیرش',
            'mobile' => '09000000000',
            'province' => 'تهران',
            'city' => 'تهران',
            'address' => 'خیابان تست پذیرش، پلاک ۱۸',
            'postalCode' => '1234567890',
            'isDefault' => true,
        ])->assertCreated()
            ->assertJsonPath('data.address.isDefault', true)
            ->json('data.address.id');

        $dryVariant = BakeryProductVariant::query()
            ->where('sku', 'STG-COOKIE-CHOCO-6')
            ->firstOrFail();
        $chilledVariant = BakeryProductVariant::query()
            ->where('sku', 'STG-CAKE-CHILLED-1')
            ->firstOrFail();

        $dryPayload = [
            'customer' => [
                'fullName' => 'مشتری تست پذیرش',
                'mobile' => '09000000000',
                'province' => 'اصفهان',
                'city' => 'اصفهان',
                'address' => 'آدرس تست ارسال خشک سراسری',
                'postalCode' => '1234567890',
            ],
            'deliveryMethod' => 'standard',
            'items' => [[
                'variantId' => $dryVariant->public_id,
                'quantity' => 2,
            ]],
        ];

        $created = $this->stateful()->postJson('/api/checkout', $dryPayload, [
            'Idempotency-Key' => 'phase18-dry-checkout-0001',
        ])->assertCreated()
            ->assertJsonPath('data.order.delivery.method', 'standard')
            ->assertJsonPath('data.order.delivery.requiresCooling', false)
            ->assertJsonPath('data.payment.available', true)
            ->assertJsonPath('meta.replayed', false);

        $orderId = (string) $created->json('data.order.id');
        $order = Order::query()->where('public_id', $orderId)->firstOrFail();

        $this->stateful()->postJson('/api/checkout', $dryPayload, [
            'Idempotency-Key' => 'phase18-dry-checkout-0001',
        ])->assertOk()
            ->assertJsonPath('data.order.id', $orderId)
            ->assertJsonPath('meta.replayed', true);

        $this->stateful()->postJson('/api/checkout', [
            ...$dryPayload,
            'items' => [[
                'variantId' => $dryVariant->public_id,
                'quantity' => 3,
            ]],
        ], [
            'Idempotency-Key' => 'phase18-dry-checkout-0001',
        ])->assertConflict();

        $this->stateful()->postJson('/api/checkout', [
            'customer' => [
                'fullName' => 'مشتری تست پذیرش',
                'mobile' => '09000000000',
                'province' => 'اصفهان',
                'city' => 'اصفهان',
                'address' => 'آدرس خارج از محدوده سرد',
                'postalCode' => '1234567890',
            ],
            'deliveryMethod' => 'chilled',
            'items' => [[
                'variantId' => $chilledVariant->public_id,
                'quantity' => 1,
            ]],
        ], [
            'Idempotency-Key' => 'phase18-chilled-rejected-0001',
        ])->assertUnprocessable();

        $this->stateful()->postJson('/api/checkout', [
            'addressId' => $addressId,
            'deliveryMethod' => 'chilled',
            'items' => [[
                'variantId' => $chilledVariant->public_id,
                'quantity' => 1,
            ]],
        ], [
            'Idempotency-Key' => 'phase18-chilled-tehran-0001',
        ])->assertCreated()
            ->assertJsonPath('data.order.delivery.method', 'chilled')
            ->assertJsonPath('data.order.delivery.requiresCooling', true)
            ->assertJsonPath('data.order.recipient.city', 'تهران');

        $payment = $this->stateful()->postJson("/api/orders/{$orderId}/payments", [], [
            'Idempotency-Key' => 'phase18-payment-attempt-0001',
        ])->assertCreated()
            ->assertJsonPath('data.payment.provider', 'testing')
            ->assertJsonPath('data.order.paymentStatus', PaymentStatus::Pending->value);

        $authority = (string) $payment->json('data.payment.authority');

        $this->stateful()->postJson('/api/payments/verify', [
            'authority' => $authority,
            'status' => 'OK',
        ])->assertOk()
            ->assertJsonPath('data.verified', true)
            ->assertJsonPath('data.order.status', OrderStatus::Paid->value)
            ->assertJsonPath('data.order.paymentStatus', PaymentStatus::Paid->value)
            ->assertJsonPath('meta.replayed', false);

        $this->stateful()->postJson('/api/payments/verify', [
            'authority' => $authority,
            'status' => 'OK',
        ])->assertOk()
            ->assertJsonPath('data.verified', true)
            ->assertJsonPath('meta.replayed', true);

        $this->assertSame(23, $dryVariant->fresh()->stock_quantity);
        $this->assertDatabaseHas('inventory_reservations', [
            'order_id' => $order->getKey(),
            'status' => InventoryReservationStatus::Consumed->value,
            'quantity' => 2,
        ]);

        $this->stateful()->getJson('/api/account/orders')
            ->assertOk()
            ->assertJsonFragment(['id' => $orderId]);

        $this->stateful()->postJson('/api/auth/logout')->assertOk();
        $this->stateful()->getJson('/api/auth/me')->assertUnauthorized();
    }

    public function test_inquiry_honeypot_and_rate_limit_remain_enforced_in_acceptance_environment(): void
    {
        $payload = [
            'type' => 'corporate',
            'fullName' => 'شرکت تست فاز هجده',
            'mobile' => '09000000000',
            'subject' => 'سفارش سازمانی پذیرش',
            'message' => 'این پیام برای تست ذخیره‌سازی فرم سازمانی در فاز هجده است.',
            'metadata' => ['quantity' => 18],
            'website' => '',
        ];

        $this->stateful()->postJson('/api/inquiries', $payload)
            ->assertCreated()
            ->assertJsonPath('data.inquiry.status', 'new');

        $this->stateful()->postJson('/api/inquiries', $payload)
            ->assertTooManyRequests();

        $this->stateful()->postJson('/api/inquiries', [
            ...$payload,
            'message' => 'پیام متفاوت برای بررسی honeypot.',
            'website' => 'bot.example',
        ])->assertUnprocessable();
    }

    private function stateful(): static
    {
        return $this->withHeaders([
            'Origin' => 'http://localhost:5173',
            'Referer' => 'http://localhost:5173/',
            'User-Agent' => 'Winimi-Phase18-Acceptance/1.0',
        ]);
    }
}
