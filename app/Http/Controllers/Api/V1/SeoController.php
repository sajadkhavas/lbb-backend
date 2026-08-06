<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\SeoMeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SeoController extends Controller
{
    // GET /api/v1/seo?page=home
    public function index(Request $request)
    {
        $key = $request->query('page', 'home');
        return $this->getSeo($key);
    }

    // GET /api/v1/seo/{type}/{slug}
    // مثال: /api/v1/seo/product/drill-machine
    //        /api/v1/seo/blog/my-post
    //        /api/v1/seo/category/tools
    public function show(string $type, string $slug)
    {
        $key = "{$type}/{$slug}";
        return $this->getSeo($key);
    }

    private function getSeo(string $key)
    {
        $cacheKey = 'seo:' . md5($key);

        $seo = Cache::remember($cacheKey, 600, fn() =>
            SeoMeta::where('page_key', $key)
                ->where('is_active', true)
                ->first()
        );

        if (!$seo) {
            // fallback به تنظیمات پیش‌فرض سایت
            $default = Cache::remember('seo:default', 3600, fn() =>
                SeoMeta::where('page_key', 'home')
                    ->where('is_active', true)
                    ->first()
            );

            return response()->json([
                'success' => false,
                'message' => 'SEO data not found',
                'data'    => $default ? $this->formatSeo($default) : null,
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $this->formatSeo($seo),
        ]);
    }

    private function formatSeo(SeoMeta $seo): array
    {
        return [
            'meta_title'       => $seo->meta_title,
            'meta_description' => $seo->meta_description,
            'meta_keywords'    => $seo->meta_keywords,
            'og_title'         => $seo->og_title ?: $seo->meta_title,
            'og_description'   => $seo->og_description ?: $seo->meta_description,
            'og_image'         => $seo->og_image,
            'canonical_url'    => $seo->canonical_url,
            'robots'           => $seo->robots,
            'schema_json'      => $seo->schema_json,
        ];
    }
}
