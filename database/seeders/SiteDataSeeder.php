<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SiteDataSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        $pages = [
            [
                'slug'             => 'about',
                'title'            => 'درباره ما',
                'hero_title'       => 'تول‌مستر؛ تامین‌کننده تخصصی تجهیزات ابزار دقیق و اتوماسیون صنعتی',
                'hero_description' => 'با بیش از یک دهه تجربه در تامین، نصب و پشتیبانی تجهیزات صنعتی از معتبرترین برندهای جهانی',
                'content'          => '<p>شرکت تول‌مستر با هدف تامین تجهیزات ابزار دقیق و اتوماسیون صنعتی برای صنایع مختلف کشور فعالیت می‌کند.</p>',
                'meta_title'       => 'درباره تول‌مستر | تامین‌کننده تجهیزات ابزار دقیق',
                'meta_description' => 'آشنایی با شرکت تول‌مستر، تامین‌کننده تخصصی تجهیزات ابزار دقیق.',
                'meta_keywords'    => 'درباره تول‌مستر, شرکت ابزار دقیق',
                'status'           => 'published',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'projects',
                'title'            => 'پروژه‌های اجرا شده',
                'hero_title'       => 'رزومه پروژه‌های صنعتی تول‌مستر',
                'hero_description' => 'نمونه اجراهای واقعی در صنایع مختلف',
                'content'          => '<p>تول‌مستر تاکنون پروژه‌های متعددی در حوزه ابزار دقیق و اتوماسیون صنعتی انجام داده است.</p>',
                'meta_title'       => 'پروژه‌های ابزار دقیق | تول‌مستر',
                'meta_description' => 'مشاهده پروژه‌های اجرا شده تول‌مستر.',
                'meta_keywords'    => 'پروژه ابزار دقیق, اتوماسیون صنعتی',
                'status'           => 'published',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'faq',
                'title'            => 'سوالات متداول',
                'hero_title'       => 'سوالات متداول مشتریان تول‌مستر',
                'hero_description' => 'پاسخ به رایج‌ترین سوالات',
                'content'          => '<p><strong>چگونه استعلام قیمت بدهم؟</strong></p><p>از طریق فرم استعلام قیمت در صفحه هر محصول اقدام کنید.</p>',
                'meta_title'       => 'سوالات متداول | تول‌مستر',
                'meta_description' => 'پاسخ به سوالات متداول درباره خرید تجهیزات.',
                'meta_keywords'    => 'سوالات متداول, راهنمای خرید',
                'status'           => 'published',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'terms',
                'title'            => 'شرایط و ضوابط',
                'hero_title'       => 'شرایط و ضوابط استفاده از خدمات تول‌مستر',
                'hero_description' => 'قوانین شفاف برای ثبت سفارش و ارائه خدمات',
                'content'          => '<p>استفاده از خدمات تول‌مستر به منزله پذیرش شرایط زیر است.</p>',
                'meta_title'       => 'شرایط و ضوابط | تول‌مستر',
                'meta_description' => 'شرایط و ضوابط خرید و خدمات تول‌مستر.',
                'meta_keywords'    => 'شرایط خرید, ضوابط',
                'status'           => 'published',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
            [
                'slug'             => 'privacy',
                'title'            => 'حریم خصوصی',
                'hero_title'       => 'سیاست حریم خصوصی',
                'hero_description' => 'تعهد به حفظ محرمانگی داده‌های کاربران',
                'content'          => '<p>اطلاعات شخصی شما فقط برای ارائه خدمات استفاده می‌شود.</p>',
                'meta_title'       => 'حریم خصوصی | تول‌مستر',
                'meta_description' => 'سیاست حریم خصوصی تول‌مستر.',
                'meta_keywords'    => 'حریم خصوصی, امنیت اطلاعات',
                'status'           => 'published',
                'created_at'       => $now,
                'updated_at'       => $now,
            ],
        ];

        foreach ($pages as $page) {
            DB::table('site_pages')->updateOrInsert(['slug' => $page['slug']], $page);
        }

        $this->command->info('✅ ' . count($pages) . ' صفحه وارد شد!');

        // منو
        DB::table('navigation_items')->truncate();

        $mainMenus = [
            ['label' => 'دسته‌بندی محصولات', 'href' => '/products',  'sort_order' => 1, 'description' => 'مرور کامل تجهیزات ابزار دقیق'],
            ['label' => 'برندها',            'href' => '/brands',    'sort_order' => 2, 'description' => '۱۲ برند معتبر جهانی'],
            ['label' => 'مقالات و راهنما',   'href' => '/blog',      'sort_order' => 3, 'description' => 'مقالات تخصصی'],
            ['label' => 'پروژه‌ها',          'href' => '/projects',  'sort_order' => 4, 'description' => null],
            ['label' => 'درباره ما',         'href' => '/about',     'sort_order' => 5, 'description' => null],
        ];

        foreach ($mainMenus as $menu) {
            DB::table('navigation_items')->insert(array_merge($menu, [
                'parent_id'  => null,
                'is_active'  => true,
                'icon'       => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]));
        }

        $productsId = DB::table('navigation_items')->where('href', '/products')->value('id');
        $productSubs = [
            ['label' => 'ژنراتورهای گاز',     'href' => '/products/category/gas-generators', 'sort_order' => 1, 'icon' => 'Flame'],
            ['label' => 'دتکتورهای گاز',       'href' => '/products/category/gas-detectors',  'sort_order' => 2, 'icon' => 'Gauge'],
            ['label' => 'فلومتر و فلوکنترلر',  'href' => '/products/category/flow-meters',    'sort_order' => 3, 'icon' => 'Gauge'],
            ['label' => 'تجهیزات PLC',         'href' => '/products/category/plc-equipment',  'sort_order' => 4, 'icon' => 'Cpu'],
            ['label' => 'پمپ‌های آزمایشگاهی',  'href' => '/products/category/lab-pumps',      'sort_order' => 5, 'icon' => 'Droplets'],
        ];
        foreach ($productSubs as $sub) {
            DB::table('navigation_items')->insert(array_merge($sub, [
                'parent_id'   => $productsId,
                'is_active'   => true,
                'description' => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]));
        }

        $blogId = DB::table('navigation_items')->where('href', '/blog')->value('id');
        $blogSubs = [
            ['label' => 'راهنمای خرید', 'href' => '/blog?category=buying-guide', 'sort_order' => 1],
            ['label' => 'آموزش تخصصی',  'href' => '/blog?category=technical',    'sort_order' => 2],
            ['label' => 'تحلیل بازار',  'href' => '/blog?category=market',       'sort_order' => 3],
        ];
        foreach ($blogSubs as $sub) {
            DB::table('navigation_items')->insert(array_merge($sub, [
                'parent_id'   => $blogId,
                'is_active'   => true,
                'icon'        => null,
                'description' => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]));
        }

        $aboutId = DB::table('navigation_items')->where('href', '/about')->value('id');
        $aboutSubs = [
            ['label' => 'معرفی شرکت', 'href' => '/about',   'sort_order' => 1],
            ['label' => 'تماس با ما', 'href' => '/contact', 'sort_order' => 2],
        ];
        foreach ($aboutSubs as $sub) {
            DB::table('navigation_items')->insert(array_merge($sub, [
                'parent_id'   => $aboutId,
                'is_active'   => true,
                'icon'        => null,
                'description' => null,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]));
        }

        $total = DB::table('navigation_items')->count();
        $this->command->info("✅ {$total} آیتم منو وارد شد!");
    }
}
