<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

class WinimiStagingSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment('production')) {
            throw new RuntimeException('Winimi staging data must never be seeded in production.');
        }

        DB::transaction(function (): void {
            $now = now();

            DB::table('bakery_categories')->updateOrInsert(
                ['slug' => 'staging-cookies'],
                [
                    'public_id' => $this->existingPublicId('bakery_categories', 'slug', 'staging-cookies'),
                    'name' => 'کوکی‌های تست پذیرش',
                    'description' => 'داده مشخص و قابل حذف برای تست اتصال فرانت و بک‌اند.',
                    'is_active' => true,
                    'sort_order' => 900,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
            $categoryId = DB::table('bakery_categories')->where('slug', 'staging-cookies')->value('id');

            $products = [
                [
                    'slug' => 'staging-chocolate-cookie',
                    'name' => 'کوکی شکلاتی تست',
                    'code' => 'STG-COOKIE-CHOCO',
                    'short' => 'محصول خشک برای تست ارسال سراسری.',
                    'cooling' => false,
                    'featured' => true,
                    'sku' => 'STG-COOKIE-CHOCO-6',
                    'variant' => 'بسته ۶ عددی',
                    'price' => 180000,
                    'stock' => 25,
                ],
                [
                    'slug' => 'staging-chilled-cake',
                    'name' => 'کیک سرد تست',
                    'code' => 'STG-CAKE-CHILLED',
                    'short' => 'محصول سرد برای تست محدودیت تهران، کرج و اندیشه.',
                    'cooling' => true,
                    'featured' => false,
                    'sku' => 'STG-CAKE-CHILLED-1',
                    'variant' => 'یک عدد',
                    'price' => 420000,
                    'stock' => 12,
                ],
                [
                    'slug' => 'staging-gift-box',
                    'name' => 'باکس هدیه تست',
                    'code' => 'STG-GIFT-BOX',
                    'short' => 'محصول تست برای سفارش هدیه و سازمانی.',
                    'cooling' => false,
                    'featured' => true,
                    'sku' => 'STG-GIFT-BOX-1',
                    'variant' => 'باکس استاندارد',
                    'price' => 690000,
                    'stock' => 10,
                ],
            ];

            foreach ($products as $position => $product) {
                DB::table('bakery_products')->updateOrInsert(
                    ['slug' => $product['slug']],
                    [
                        'public_id' => $this->existingPublicId('bakery_products', 'slug', $product['slug']),
                        'category_id' => $categoryId,
                        'name' => $product['name'],
                        'product_code' => $product['code'],
                        'short_description' => $product['short'],
                        'description' => 'این رکورد فقط برای staging و تست پذیرش ایجاد شده است.',
                        'ingredients' => json_encode(['داده تست'], JSON_UNESCAPED_UNICODE),
                        'allergens' => json_encode([], JSON_UNESCAPED_UNICODE),
                        'preparation_time_days' => 1,
                        'requires_cooling' => $product['cooling'],
                        'content_verified' => true,
                        'media_verified' => true,
                        'is_active' => true,
                        'is_featured' => $product['featured'],
                        'sort_order' => 900 + $position,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
                $productId = DB::table('bakery_products')->where('slug', $product['slug'])->value('id');

                DB::table('bakery_product_variants')->updateOrInsert(
                    ['sku' => $product['sku']],
                    [
                        'public_id' => $this->existingPublicId('bakery_product_variants', 'sku', $product['sku']),
                        'product_id' => $productId,
                        'name' => $product['variant'],
                        'regular_price_toman' => $product['price'],
                        'sale_price_toman' => null,
                        'stock_quantity' => $product['stock'],
                        'low_stock_threshold' => 3,
                        'is_default' => true,
                        'is_active' => true,
                        'sort_order' => 0,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }

            $zones = [
                ['name' => 'تهران تست', 'province' => 'تهران', 'city' => 'تهران', 'standard' => true, 'chilled' => true, 'pickup' => true, 'priority' => 10],
                ['name' => 'کرج تست', 'province' => 'البرز', 'city' => 'کرج', 'standard' => true, 'chilled' => true, 'pickup' => false, 'priority' => 20],
                ['name' => 'اندیشه تست', 'province' => 'تهران', 'city' => 'اندیشه', 'standard' => true, 'chilled' => true, 'pickup' => false, 'priority' => 20],
                ['name' => 'ارسال خشک سراسری تست', 'province' => null, 'city' => null, 'standard' => true, 'chilled' => false, 'pickup' => false, 'priority' => 900],
            ];

            foreach ($zones as $zone) {
                DB::table('delivery_zones')->updateOrInsert(
                    ['name' => $zone['name']],
                    [
                        'public_id' => $this->existingPublicId('delivery_zones', 'name', $zone['name']),
                        'province' => $zone['province'],
                        'city' => $zone['city'],
                        'standard_enabled' => $zone['standard'],
                        'chilled_enabled' => $zone['chilled'],
                        'pickup_enabled' => $zone['pickup'],
                        'standard_fee_toman' => 45000,
                        'chilled_fee_toman' => 85000,
                        'pickup_fee_toman' => 0,
                        'packaging_fee_toman' => 15000,
                        'minimum_order_toman' => 100000,
                        'free_delivery_threshold_toman' => 1500000,
                        'preparation_min_days' => 1,
                        'preparation_max_days' => 2,
                        'daily_order_limit' => 100,
                        'priority' => $zone['priority'],
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }

            $contentPages = [
                [
                    'slug' => 'about',
                    'type' => 'about',
                    'title' => 'درباره وینیمی',
                    'excerpt' => 'معرفی وینیمی و مسیر شکل‌گیری فروشگاه در محیط پذیرش.',
                    'content' => 'این صفحه معرفی برند برای تست SSR، لینک‌های داخلی و موجودی نهایی Sitemap منتشر شده است.',
                ],
                [
                    'slug' => 'quality',
                    'type' => 'quality',
                    'title' => 'شفافیت و کیفیت',
                    'excerpt' => 'سیاست شفافیت اطلاعات محصول و کنترل محتوای منتشرشده وینیمی.',
                    'content' => 'اطلاعات محصول، موجودی و رسانه‌ها فقط از داده‌های تأییدشده و منتشرشده نمایش داده می‌شوند.',
                ],
                [
                    'slug' => 'shipping',
                    'type' => 'shipping',
                    'title' => 'شرایط ارسال',
                    'excerpt' => 'شرایط عمومی ارسال و وابستگی روش تحویل به نوع محصول و مقصد.',
                    'content' => 'روش تحویل، هزینه و بازه آماده‌سازی در Checkout و براساس محصول، مقصد و تنظیمات فعال سرور تعیین می‌شود.',
                ],
                [
                    'slug' => 'privacy',
                    'type' => 'legal',
                    'title' => 'حریم خصوصی',
                    'excerpt' => 'شرح نحوه استفاده از اطلاعات حساب و سفارش در محیط فروشگاه.',
                    'content' => 'اطلاعات حساب و سفارش فقط برای ارائه خدمات فروشگاه، پیگیری سفارش و الزامات عملیاتی استفاده می‌شود.',
                ],
                [
                    'slug' => 'terms',
                    'type' => 'legal',
                    'title' => 'شرایط استفاده',
                    'excerpt' => 'شرایط استفاده از فروشگاه، ثبت سفارش و مسئولیت‌های طرفین.',
                    'content' => 'ثبت سفارش به معنی پذیرش قیمت، موجودی، شرایط تحویل و سیاست‌های منتشرشده در زمان ثبت نهایی است.',
                ],
                [
                    'slug' => 'staging-shipping-policy',
                    'type' => 'shipping',
                    'title' => 'سیاست ارسال تست',
                ],
                [
                    'slug' => 'staging-privacy',
                    'type' => 'legal',
                    'title' => 'حریم خصوصی تست',
                ],
                [
                    'slug' => 'staging-returns',
                    'type' => 'legal',
                    'title' => 'شرایط مرجوعی تست',
                ],
            ];

            foreach ($contentPages as $page) {
                DB::table('bakery_content_pages')->updateOrInsert(
                    ['slug' => $page['slug']],
                    [
                        'public_id' => $this->existingPublicId('bakery_content_pages', 'slug', $page['slug']),
                        'type' => $page['type'],
                        'title' => $page['title'],
                        'excerpt' => $page['excerpt'] ?? 'محتوای staging برای تست اتصال فرانت.',
                        'content' => $page['content'] ?? 'این صفحه داده تست پذیرش است و پیش از ورود محتوای نهایی جایگزین می‌شود.',
                        'status' => 'published',
                        'published_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ],
                );
            }

            DB::table('bakery_faqs')->updateOrInsert(
                ['question' => 'این پرسش فقط برای staging است؟'],
                [
                    'category' => 'staging',
                    'answer' => 'بله، این داده برای تست پذیرش فازهای ۱۷ و ۱۸ ساخته شده است.',
                    'sort_order' => 900,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );

            DB::table('bakery_posts')->updateOrInsert(
                ['slug' => 'staging-welcome'],
                [
                    'public_id' => $this->existingPublicId('bakery_posts', 'slug', 'staging-welcome'),
                    'title' => 'مقاله تست پذیرش وینیمی',
                    'excerpt' => 'رکورد مشخص برای تست لیست و جزئیات وبلاگ.',
                    'content' => 'این نوشته فقط در محیط staging استفاده می‌شود.',
                    'category' => 'staging',
                    'tags' => json_encode(['staging', 'acceptance'], JSON_UNESCAPED_UNICODE),
                    'author' => 'Winimi QA',
                    'status' => 'published',
                    'published_at' => $now,
                    'view_count' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );

            DB::table('bakery_city_pages')->updateOrInsert(
                ['slug' => 'staging-tehran'],
                [
                    'public_id' => $this->existingPublicId('bakery_city_pages', 'slug', 'staging-tehran'),
                    'city' => 'تهران',
                    'title' => 'سفارش کوکی تست در تهران',
                    'description' => 'صفحه شهری staging.',
                    'content' => 'محتوای تست پذیرش صفحه شهری.',
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );

            DB::table('customers')->updateOrInsert(
                ['mobile' => '09000000000'],
                [
                    'public_id' => $this->existingPublicId('customers', 'mobile', '09000000000'),
                    'full_name' => 'مشتری تست پذیرش',
                    'email' => 'staging-customer@winimi.test',
                    'mobile_verified_at' => $now,
                    'is_active' => true,
                    'marketing_consent' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );

            DB::table('store_settings')->where('key', 'contact.phone')->update(['value' => '02100000000', 'updated_at' => $now]);
            DB::table('store_settings')->where('key', 'contact.email')->update(['value' => 'staging@winimi.test', 'updated_at' => $now]);
            DB::table('store_settings')->where('key', 'trust.enamad_enabled')->update(['value' => '0', 'updated_at' => $now]);
            DB::table('store_settings')->where('key', 'trust.enamad_badge_code')->update(['value' => '', 'updated_at' => $now]);
        }, 3);
    }

    private function existingPublicId(string $table, string $column, string $value): string
    {
        return (string) (DB::table($table)->where($column, $value)->value('public_id') ?: Str::ulid());
    }
}
