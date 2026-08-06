<?php

namespace Tests\Feature;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\BakeryCategory;
use App\Models\BakeryProduct;
use App\Models\BakeryProductVariant;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\Order;
use Database\Seeders\WinimiStagingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackendContractFreezeTest extends TestCase
{
    use RefreshDatabase;

    public function test_openapi_and_machine_readable_backend_gate_are_frozen(): void
    {
        $response = $this->getJson('/api/system/openapi')->assertOk();
        $document = $response->json();

        $this->assertSame('3.1.0', $document['openapi']);
        $this->assertSame('2026-07-20-phase-16', $document['info']['version']);
        $this->assertArrayHasKey('/api/checkout', $document['paths']);
        $this->assertArrayHasKey('/api/system/openapi', $document['paths']);
        $this->assertArrayNotHasKey('/api/v1/products', $document['paths']);

        $this->getJson('/api/system/contracts')
            ->assertOk()
            ->assertJsonPath('data.contracts.backend_freeze.status', 'ready')
            ->assertJsonPath('data.launch.internal_gates.backend_complete.status', 'ready')
            ->assertJsonPath('data.policies.pagination.catalog_max', 48)
            ->assertJsonPath('meta.contractVersion', '2026-07-20-phase-16');

        $this->assertSame(0, Artisan::call('backend:readiness', ['--json' => true]));
    }

    public function test_errors_use_frozen_codes_and_contract_metadata(): void
    {
        $this->getJson('/api/catalog/products?perPage=999')
            ->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'validation_failed')
            ->assertJsonPath('meta.contractVersion', '2026-07-20-phase-16');

        $this->getJson('/api/account/orders')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'authentication_required');

        $this->getJson('/api/does-not-exist')
            ->assertNotFound()
            ->assertJsonPath('code', 'resource_not_found');

        config(['winimi.legacy.enabled' => false]);
        $this->getJson('/api/v1/products')
            ->assertNotFound()
            ->assertJsonPath('code', 'legacy_api_disabled');
    }

    public function test_paginated_surfaces_use_the_same_metadata_shape(): void
    {
        $category = BakeryCategory::query()->create([
            'name' => 'قرارداد تست',
            'slug' => 'contract-test',
            'is_active' => true,
        ]);

        foreach ([1, 2] as $position) {
            $product = BakeryProduct::query()->create([
                'category_id' => $category->getKey(),
                'name' => "محصول قرارداد {$position}",
                'slug' => "contract-product-{$position}",
                'product_code' => "CONTRACT-{$position}",
                'requires_cooling' => false,
                'content_verified' => true,
                'media_verified' => true,
                'is_active' => true,
                'is_featured' => false,
                'sort_order' => $position,
            ]);
            BakeryProductVariant::query()->create([
                'product_id' => $product->getKey(),
                'name' => 'استاندارد',
                'sku' => "CONTRACT-SKU-{$position}",
                'regular_price_toman' => 100000 + $position,
                'stock_quantity' => 5,
                'is_default' => true,
                'is_active' => true,
            ]);
        }

        $this->getJson('/api/catalog/products?perPage=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.pagination.page', 1)
            ->assertJsonPath('meta.pagination.perPage', 1)
            ->assertJsonPath('meta.pagination.total', 2)
            ->assertJsonPath('meta.pagination.totalPages', 2)
            ->assertJsonPath('meta.pagination.from', 1)
            ->assertJsonPath('meta.pagination.to', 1)
            ->assertJsonPath('meta.pagination.hasMore', true);
    }

    public function test_owned_resources_do_not_disclose_cross_customer_records(): void
    {
        $owner = $this->customer('09120000001');
        $other = $this->customer('09120000002');
        $address = CustomerAddress::query()->create([
            'customer_id' => $owner->getKey(),
            'title' => 'خانه مالک',
            'recipient_name' => 'مالک سفارش',
            'mobile' => '09120000001',
            'province' => 'تهران',
            'city' => 'تهران',
            'address_line' => 'آدرس کامل تست مالک',
            'postal_code' => '1234567890',
            'is_default' => true,
            'is_active' => true,
        ]);
        $order = Order::query()->create([
            'customer_id' => $owner->getKey(),
            'order_number' => 'WNM-FREEZE-0001',
            'idempotency_key' => 'backend-freeze-order-key',
            'request_hash' => hash('sha256', 'backend-freeze-order'),
            'status' => OrderStatus::AwaitingPayment,
            'payment_status' => PaymentStatus::Unpaid,
            'delivery_method' => DeliveryMethod::Pickup,
            'requires_cooling' => false,
            'subtotal_toman' => 100000,
            'delivery_fee_toman' => 0,
            'packaging_fee_toman' => 0,
            'discount_total_toman' => 0,
            'grand_total_toman' => 100000,
            'item_count' => 1,
            'preparation_time_days' => 1,
            'preparation_max_days' => 1,
            'customer_name' => 'مالک سفارش',
            'customer_mobile' => '09120000001',
            'placed_at' => now(),
        ]);

        $this->actingAs($other, 'customer')
            ->putJson("/api/account/addresses/{$address->public_id}", [
                'title' => 'تلاش غیرمجاز',
                'recipientName' => 'کاربر دیگر',
                'mobile' => '09120000002',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'آدرس معتبر اما متعلق به کاربر دیگر',
                'postalCode' => '1234567890',
                'isDefault' => false,
            ])
            ->assertNotFound()
            ->assertJsonPath('code', 'resource_not_found');

        $this->actingAs($other, 'customer')
            ->getJson("/api/account/orders/{$order->public_id}")
            ->assertNotFound()
            ->assertJsonPath('code', 'resource_not_found');
    }

    public function test_staging_seeder_is_idempotent_and_keeps_external_integrations_disabled(): void
    {
        $seeder = app(WinimiStagingSeeder::class);
        $seeder->run();
        $seeder->run();

        $this->assertDatabaseCount('bakery_products', 3);
        $this->assertDatabaseCount('bakery_product_variants', 3);
        $this->assertDatabaseCount('delivery_zones', 4);
        $this->assertDatabaseHas('store_settings', [
            'key' => 'trust.enamad_enabled',
            'value' => '0',
        ]);
        $this->assertDatabaseHas('store_settings', [
            'key' => 'trust.enamad_badge_code',
            'value' => '',
        ]);
    }

    private function customer(string $mobile): Customer
    {
        return Customer::query()->create([
            'mobile' => $mobile,
            'mobile_verified_at' => now(),
            'is_active' => true,
        ]);
    }
}
