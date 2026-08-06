<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class SettingsController extends Controller
{
    public function __invoke()
    {
        $rows = DB::table('site_settings')->get();
        $settings = [];
        foreach ($rows as $row) {
            $settings[$row->key] = $row->value;
        }

        // Map به فیلدهایی که Footer.tsx انتظار داره
        return response()->json([
            'company_name'    => $settings['site_name'] ?? 'شرکت مهندسی تول‌مستر',
            'company_name_en' => 'ToolMaster Engineering Co.',
            'tagline'         => $settings['seo_site_description'] ?? 'مرجع تخصصی ابزار دقیق و اتوماسیون صنعتی ایران',
            'phone'           => $settings['site_phone'] ?? '021-66120746',
            'email'           => $settings['site_email'] ?? 'info@toolmaster.com',
            'address'         => $settings['site_address'] ?? 'تهران',
            'working_hours'   => $settings['site_working_hours'] ?? 'شنبه تا چهارشنبه: ۹:۰۰-۱۷:۰۰',
            'founded_year'    => $settings['founded_year'] ?? '۱۳۸۹',
            // همه تنظیمات خام هم برمیگردونیم
            'raw'             => $settings,
        ]);
    }
}
