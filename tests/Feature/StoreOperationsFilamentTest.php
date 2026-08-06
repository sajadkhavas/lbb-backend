<?php

namespace Tests\Feature;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Resources\BakeryCityPageResource;
use App\Filament\Resources\BakeryContentPageResource;
use App\Filament\Resources\BakeryFaqResource;
use App\Filament\Resources\BakeryGalleryItemResource;
use App\Filament\Resources\BakeryPostResource;
use App\Filament\Resources\CustomerAddressResource;
use App\Filament\Resources\DeliveryZoneResource;
use App\Filament\Resources\InquiryResource;
use App\Filament\Resources\NotificationOutboxResource;
use App\Filament\Resources\NotificationTemplateResource;
use App\Filament\Resources\OrderResource;
use App\Filament\Resources\ProductReviewResource;
use App\Filament\Resources\StoreSettingResource;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StoreOperationsFilamentTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_all_store_operations_resources_and_order_console(): void
    {
        Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = User::create([
            'name' => 'Operations Admin',
            'email' => 'operations-admin@example.test',
            'password' => 'operations-test-password',
        ]);
        $admin->assignRole('super_admin');

        foreach ([
            DeliveryZoneResource::class,
            StoreSettingResource::class,
            BakeryContentPageResource::class,
            BakeryFaqResource::class,
            BakeryGalleryItemResource::class,
            BakeryCityPageResource::class,
            BakeryPostResource::class,
            ProductReviewResource::class,
            InquiryResource::class,
            NotificationTemplateResource::class,
            NotificationOutboxResource::class,
            CustomerAddressResource::class,
            OrderResource::class,
        ] as $resource) {
            $this->actingAs($admin)->get($resource::getUrl('index'))->assertOk();
        }

        $customer = Customer::query()->create([
            'mobile' => '09120000000',
            'mobile_verified_at' => now(),
            'is_active' => true,
        ]);
        $order = Order::query()->create([
            'customer_id' => $customer->getKey(),
            'order_number' => 'WNM-ADMIN-0001',
            'idempotency_key' => 'admin-order-idempotency-key',
            'request_hash' => hash('sha256', 'admin-order'),
            'status' => OrderStatus::Paid,
            'payment_status' => PaymentStatus::Paid,
            'delivery_method' => DeliveryMethod::Pickup,
            'requires_cooling' => false,
            'subtotal_toman' => 100_000,
            'delivery_fee_toman' => 0,
            'packaging_fee_toman' => 0,
            'discount_total_toman' => 0,
            'grand_total_toman' => 100_000,
            'item_count' => 1,
            'preparation_time_days' => 1,
            'preparation_max_days' => 1,
            'customer_name' => 'مشتری پنل',
            'customer_mobile' => '09120000000',
            'placed_at' => now(),
            'paid_at' => now(),
        ]);

        $this->actingAs($admin)
            ->get(OrderResource::getUrl('view', ['record' => $order]))
            ->assertOk();
    }
}
