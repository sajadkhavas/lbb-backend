<?php

namespace Tests\Feature;

use App\Enums\DeliveryMethod;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\ReviewStatus;
use App\Models\BakeryCategory;
use App\Models\BakeryCityPage;
use App\Models\BakeryContentPage;
use App\Models\BakeryFaq;
use App\Models\BakeryGalleryItem;
use App\Models\BakeryPost;
use App\Models\BakeryProduct;
use App\Models\BakeryProductVariant;
use App\Models\Customer;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\ProductReview;
use App\Models\StoreSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class StoreOperationsTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;

    private BakeryProduct $product;

    private BakeryProductVariant $variant;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'session.driver' => 'array',
            'winimi.checkout.enabled' => true,
            'winimi.checkout.reservation_minutes' => 20,
            'winimi.checkout.max_quantity_per_line' => 20,
            'winimi.checkout.max_total_units' => 50,
            'winimi.checkout.packaging_fee_toman' => 0,
            'winimi.checkout.delivery_methods.standard' => ['enabled' => false, 'fee_toman' => 0],
            'winimi.checkout.delivery_methods.chilled' => ['enabled' => false, 'fee_toman' => 0],
            'winimi.checkout.delivery_methods.pickup' => ['enabled' => false, 'fee_toman' => 0],
        ]);

        $this->customer = Customer::query()->create([
            'mobile' => '09123456780',
            'full_name' => 'سجاد خواص',
            'mobile_verified_at' => now(),
            'is_active' => true,
        ]);

        $category = BakeryCategory::query()->create([
            'name' => 'کوکی',
            'slug' => 'cookies',
            'is_active' => true,
        ]);

        $this->product = BakeryProduct::query()->create([
            'category_id' => $category->getKey(),
            'name' => 'کوکی شکلاتی',
            'slug' => 'chocolate-cookie',
            'product_code' => 'COOKIE-001',
            'preparation_time_days' => 2,
            'requires_cooling' => false,
            'is_active' => true,
        ]);

        $this->variant = BakeryProductVariant::query()->create([
            'product_id' => $this->product->getKey(),
            'name' => 'بسته ۶ عددی',
            'sku' => 'COOKIE-001-6',
            'regular_price_toman' => 80_000,
            'stock_quantity' => 10,
            'low_stock_threshold' => 2,
            'is_default' => true,
            'is_active' => true,
        ]);
    }

    public function test_customer_addresses_are_owned_and_checkout_snapshots_database_delivery_rules(): void
    {
        $address = $this->actingAs($this->customer, 'customer')
            ->postJson('/api/account/addresses', [
                'title' => 'خانه',
                'recipientName' => 'سجاد خواص',
                'mobile' => '۰۹۱۲۳۴۵۶۷۸۰',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'خیابان نمونه، پلاک یک',
                'postalCode' => '1234567890',
                'isDefault' => true,
            ])
            ->assertCreated()
            ->assertJsonPath('data.address.mobile', '09123456780')
            ->assertJsonPath('data.address.isDefault', true)
            ->json('data.address.id');

        $other = Customer::query()->create([
            'mobile' => '09123456781',
            'mobile_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->actingAs($other, 'customer')
            ->putJson("/api/account/addresses/{$address}", [
                'title' => 'سرقت آدرس',
                'recipientName' => 'کاربر دیگر',
                'mobile' => '09123456781',
                'province' => 'تهران',
                'city' => 'تهران',
                'address' => 'آدرس نامعتبر',
            ])
            ->assertNotFound();

        $zone = DeliveryZone::query()->create([
            'name' => 'مرکز تهران',
            'province' => 'تهران',
            'city' => 'تهران',
            'standard_enabled' => true,
            'standard_fee_toman' => 25_000,
            'packaging_fee_toman' => 5_000,
            'preparation_min_days' => 1,
            'preparation_max_days' => 3,
            'priority' => 1,
            'is_active' => true,
        ]);

        $this->actingAs($this->customer, 'customer')
            ->postJson('/api/checkout', [
                'addressId' => $address,
                'deliveryMethod' => DeliveryMethod::Standard->value,
                'items' => [[
                    'variantId' => $this->variant->public_id,
                    'quantity' => 1,
                ]],
            ], ['Idempotency-Key' => 'phase15-checkout-key-0001'])
            ->assertCreated()
            ->assertJsonPath('data.order.delivery.zone.id', $zone->public_id)
            ->assertJsonPath('data.order.totals.deliveryFeeToman', 25_000)
            ->assertJsonPath('data.order.totals.packagingFeeToman', 5_000)
            ->assertJsonPath('data.order.totals.grandTotalToman', 110_000)
            ->assertJsonPath('data.order.preparation.minDays', 2)
            ->assertJsonPath('data.order.preparation.maxDays', 3)
            ->assertJsonPath('data.order.recipient.address', 'خیابان نمونه، پلاک یک');

        $this->assertDatabaseHas('orders', [
            'delivery_zone_id' => $zone->getKey(),
            'delivery_fee_toman' => 25_000,
            'packaging_fee_toman' => 5_000,
            'preparation_time_days' => 2,
            'preparation_max_days' => 3,
        ]);
    }

    public function test_store_content_is_published_separately_and_enamad_stays_hidden_until_enabled(): void
    {
        StoreSetting::query()->where('key', 'contact.phone')->update(['value' => '02100000000']);
        StoreSetting::query()->where('key', 'trust.enamad_badge_code')->update([
            'value' => '<a id="enamad">badge</a>',
        ]);

        $this->getJson('/api/store/settings')
            ->assertOk()
            ->assertJsonPath('data.settings.contact.phone', '02100000000')
            ->assertJsonPath('data.trust.enamad.enabled', false)
            ->assertJsonPath('data.trust.enamad.badgeCode', null);

        StoreSetting::query()->where('key', 'trust.enamad_enabled')->update(['value' => '1']);

        $this->getJson('/api/store/settings')
            ->assertOk()
            ->assertJsonPath('data.trust.enamad.enabled', true)
            ->assertJsonPath('data.trust.enamad.badgeCode', '<a id="enamad">badge</a>');

        BakeryContentPage::query()->create([
            'type' => 'legal',
            'slug' => 'privacy',
            'title' => 'حریم خصوصی',
            'content' => 'متن حریم خصوصی',
            'status' => 'published',
            'published_at' => now(),
        ]);
        BakeryContentPage::query()->create([
            'type' => 'page',
            'slug' => 'draft-page',
            'title' => 'پیش‌نویس',
            'status' => 'draft',
        ]);
        BakeryFaq::query()->create([
            'category' => 'delivery',
            'question' => 'ارسال چگونه است؟',
            'answer' => 'با توجه به منطقه.',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        BakeryGalleryItem::query()->create([
            'title' => 'ویترین',
            'image_url' => 'https://example.test/gallery.jpg',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        BakeryPost::query()->create([
            'slug' => 'fresh-cookies',
            'title' => 'کوکی تازه',
            'content' => 'مقاله کوکی تازه',
            'status' => 'published',
            'published_at' => now(),
        ]);
        BakeryCityPage::query()->create([
            'city' => 'تهران',
            'slug' => 'tehran',
            'title' => 'سفارش کوکی در تهران',
            'is_active' => true,
        ]);

        $this->getJson('/api/store/pages/privacy')->assertOk()->assertJsonPath('data.page.type', 'legal');
        $this->getJson('/api/store/pages/draft-page')->assertNotFound();
        $this->getJson('/api/store/faqs?category=delivery')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/store/gallery')->assertOk()->assertJsonPath('data.0.title', 'ویترین');
        $this->getJson('/api/store/posts/fresh-cookies')->assertOk()->assertJsonPath('data.post.title', 'کوکی تازه');
        $this->getJson('/api/store/cities/tehran')->assertOk()->assertJsonPath('data.city.city', 'تهران');
    }

    public function test_only_delivered_owned_items_can_receive_one_moderated_verified_review(): void
    {
        $order = $this->createDeliveredOrder();
        $item = $order->items()->firstOrFail();

        $other = Customer::query()->create([
            'mobile' => '09123456782',
            'mobile_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->actingAs($other, 'customer')
            ->postJson("/api/account/orders/{$order->public_id}/reviews", [
                'orderItemId' => $item->public_id,
                'rating' => 5,
                'body' => 'نظر غیرمجاز کاربر دیگر',
            ])
            ->assertNotFound();

        $this->actingAs($this->customer, 'customer')
            ->postJson("/api/account/orders/{$order->public_id}/reviews", [
                'orderItemId' => $item->public_id,
                'rating' => 5,
                'title' => 'عالی',
                'body' => 'کوکی تازه و باکیفیت بود.',
            ])
            ->assertCreated()
            ->assertJsonPath('data.review.status', ReviewStatus::Pending->value);

        $this->actingAs($this->customer, 'customer')
            ->postJson("/api/account/orders/{$order->public_id}/reviews", [
                'orderItemId' => $item->public_id,
                'rating' => 4,
            ])
            ->assertUnprocessable();

        $this->getJson('/api/catalog/products/chocolate-cookie/reviews')
            ->assertOk()
            ->assertJsonPath('meta.summary.count', 0);

        $review = ProductReview::query()->firstOrFail();
        $review->update([
            'status' => ReviewStatus::Approved,
            'published_at' => now(),
        ]);

        $this->getJson('/api/catalog/products/chocolate-cookie/reviews')
            ->assertOk()
            ->assertJsonPath('meta.summary.count', 1)
            ->assertJsonPath('data.0.verifiedPurchase', true)
            ->assertJsonPath('data.0.rating', 5);
    }

    public function test_inquiries_use_honeypot_and_duplicate_protection(): void
    {
        $payload = [
            'type' => 'corporate',
            'fullName' => 'شرکت نمونه',
            'mobile' => '09123456780',
            'subject' => 'سفارش سازمانی',
            'message' => 'برای یک رویداد سازمانی به صد بسته کوکی نیاز داریم.',
            'metadata' => ['quantity' => 100],
            'website' => '',
        ];

        $this->postJson('/api/inquiries', $payload)
            ->assertCreated()
            ->assertJsonPath('data.inquiry.type', 'corporate')
            ->assertJsonPath('data.inquiry.status', 'new');

        $this->postJson('/api/inquiries', $payload)
            ->assertTooManyRequests();

        $this->postJson('/api/inquiries', [
            ...$payload,
            'message' => 'یک پیام متفاوت و معتبر برای فرم آزمایشی.',
            'website' => 'spam.example',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('inquiries', 1);
        $this->assertNotNull((string) DB::table('inquiries')->value('ip_hash'));
    }

    private function createDeliveredOrder(): Order
    {
        $order = Order::query()->create([
            'customer_id' => $this->customer->getKey(),
            'order_number' => 'WNM-REVIEW-0001',
            'idempotency_key' => 'review-order-idempotency-key',
            'request_hash' => hash('sha256', 'review-order'),
            'status' => OrderStatus::Delivered,
            'payment_status' => PaymentStatus::Paid,
            'delivery_method' => DeliveryMethod::Pickup,
            'requires_cooling' => false,
            'subtotal_toman' => 80_000,
            'delivery_fee_toman' => 0,
            'packaging_fee_toman' => 0,
            'discount_total_toman' => 0,
            'grand_total_toman' => 80_000,
            'item_count' => 1,
            'preparation_time_days' => 2,
            'preparation_max_days' => 2,
            'customer_name' => 'سجاد خواص',
            'customer_mobile' => '09123456780',
            'placed_at' => now()->subDays(3),
            'paid_at' => now()->subDays(3),
            'delivered_at' => now(),
        ]);

        $order->items()->create([
            'product_id' => $this->product->getKey(),
            'variant_id' => $this->variant->getKey(),
            'product_public_id' => $this->product->public_id,
            'variant_public_id' => $this->variant->public_id,
            'product_name' => $this->product->name,
            'variant_name' => $this->variant->name,
            'product_code' => $this->product->product_code,
            'sku' => $this->variant->sku,
            'requires_cooling' => false,
            'unit_price_toman' => 80_000,
            'quantity' => 1,
            'line_total_toman' => 80_000,
        ]);

        return $order->fresh('items');
    }
}
