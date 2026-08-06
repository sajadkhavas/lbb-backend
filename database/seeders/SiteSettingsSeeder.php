<?php

namespace Database\Seeders;

use App\Models\SiteSetting;
use Illuminate\Database\Seeder;

class SiteSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Company
            ['group' => 'general', 'key' => 'site_name',     'value' => 'پارس ابزار دقیق', 'label' => 'نام سایت'],
            ['group' => 'general', 'key' => 'site_phone',    'value' => '021-00000000',   'label' => 'تلفن'],
            ['group' => 'general', 'key' => 'site_email',    'value' => 'info@toolmaster.com', 'label' => 'ایمیل'],
            ['group' => 'general', 'key' => 'site_address',  'value' => 'تهران',          'label' => 'آدرس'],

            // Hero
            ['group' => 'home', 'key' => 'home_hero_title',    'value' => 'تأمین‌کننده تجهیزات ابزار دقیق صنعتی', 'label' => 'عنوان هیرو'],
            ['group' => 'home', 'key' => 'home_hero_subtitle', 'value' => 'استعلام و خرید برندهای معتبر اروپایی و آمریکایی', 'label' => 'زیرعنوان'],
            ['group' => 'home', 'key' => 'home_hero_badge',    'value' => 'نماینده رسمی',  'label' => 'بج'],
            ['group' => 'home', 'key' => 'home_hero_button_primary',   'value' => 'مشاهده محصولات', 'label' => 'دکمه اصلی'],
            ['group' => 'home', 'key' => 'home_hero_button_secondary', 'value' => 'استعلام قیمت',   'label' => 'دکمه ثانویه'],

            // Stats
            ['group' => 'home', 'key' => 'home_stats_products',  'value' => '500+', 'label' => 'محصولات'],
            ['group' => 'home', 'key' => 'home_stats_brands',    'value' => '40+',  'label' => 'برندها'],
            ['group' => 'home', 'key' => 'home_stats_customers', 'value' => '1200+','label' => 'مشتریان'],

            // SEO
            ['group' => 'seo', 'key' => 'seo_site_title',       'value' => 'پارس ابزار دقیق | تجهیزات ابزار دقیق صنعتی', 'label' => 'عنوان کلی سایت'],
            ['group' => 'seo', 'key' => 'seo_site_description', 'value' => 'تأمین‌کننده رسمی تجهیزات ابزار دقیق، اتوماسیون صنعتی و سنسورهای پیشرفته از برندهای اروپایی و آمریکایی.', 'label' => 'توضیح کلی'],
        ];

        foreach ($defaults as $row) {
            SiteSetting::updateOrCreate(['key' => $row['key']], $row);
        }
    }
}
