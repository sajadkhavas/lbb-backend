<?php
namespace App\Http\Controllers\Api\V1;
use App\Http\Controllers\Controller;
use App\Models\SchemaMarkup;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SchemaController extends Controller
{
    public function index(Request $request)
    {
        $pageType = $request->query('page_type', 'global');
        $slug     = $request->query('slug', '');

        $cacheKey = "schema:{$pageType}:{$slug}";

        $schemas = Cache::remember($cacheKey, 3600, function () use ($pageType, $slug) {
            return SchemaMarkup::where('is_active', true)
                ->where(function ($q) use ($pageType, $slug) {
                    $q->where('page_type', 'global')
                      ->orWhere('page_type', $pageType);
                })
                ->when($slug, fn($q) => $q->where(function ($q2) use ($slug) {
                    $q2->whereNull('page_slug')
                       ->orWhere('page_slug', $slug);
                }))
                ->get(['type', 'data']);
        });

        return response()->json([
            'success' => true,
            'data'    => $schemas,
        ]);
    }
}
