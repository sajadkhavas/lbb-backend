<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            // اطلاعات شرکت
            ['key' => 'company_name',    'value' => 'شرکت مهندسی تول‌مستر',           'group' => 'company', 'label' => 'نام شرکت'],
            ['key' => 'company_name_en', 'value' => 'ToolMaster Engineering Co.',       'group' => 'company', 'label' => 'نام شرکت (انگلیسی)'],
            ['key' => 'tagline',         'value' => 'مرجع تخصصی ابزار دقیق و اتوماسیون صنعتی ایران', 'group' => 'company', 'label' => 'شعار شرکت'],
            ['key' => 'founded_year',    'value' => '۱۳۸۹',                            'group' => 'company', 'label' => 'سال تأسیس'],

            // اطلاعات تماس
            ['key' => 'phone',           'value' => '021-66120746',                    'group' => 'contact', 'label' => 'شماره تلفن'],
            ['key' => 'email',           'value' => 'info@toolmaster.com',             'group' => 'contact', 'label' => 'ایمیل'],
            ['key' => 'address',         'value' => 'تهران، خیابان توحید، خیابان طوسی، پلاک ۱۶۲، واحد ۹', 'group' => 'contact', 'label' => 'آدرس'],
            ['key' => 'working_hours',   'value' => 'شنبه تا چهارشنبه: ۹:۰۰-۱۷:۰۰', 'group' => 'contact', 'label' => 'ساعات کاری'],

            // شبکه‌های اجتماعی
            ['key' => 'instagram',       'value' => '',                                'group' => 'social',  'label' => 'اینستاگرام'],
            ['key' => 'linkedin',        'value' => '',                                'group' => 'social',  'label' => 'لینکدین'],
            ['key' => 'whatsapp',        'value' => '',                                'group' => 'social',  'label' => 'واتساپ'],

            // SEO
            ['key' => 'meta_title',      'value' => 'تول‌مستر | تامین‌کننده تجهیزات ابزار دقیق', 'group' => 'seo', 'label' => 'عنوان متا'],
            ['key' => 'meta_description','value' => 'تامین تجهیزات ابزار دقیق و اتوماسیون صنعتی', 'group' => 'seo', 'label' => 'توضیح متا'],
        ];

        foreach ($settings as $setting) {
            DB::table('settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }

        $this->command->info('✅ تنظیمات سایت وارد شد!');
    }
}
