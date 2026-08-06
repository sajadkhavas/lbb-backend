<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\BlogPost;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class SearchController extends Controller
{
    public function __invoke(Request $request)
    {
        $query = trim((string) $request->get('q', ''));

        if (mb_strlen($query) < 2) {
            return response()->json(['data' => ['products' => [], 'articles' => [], 'categories' => []]]);
        }

        // حداکثر ۱۰۰ کاراکتر
        $query = mb_substr($query, 0, 100);

        // escape کاراکترهای خاص LIKE
        $safe = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $query);
        $like = "%{$safe}%";

        $cacheKey = 'search:' . md5($query);

        $results = Cache::remember($cacheKey, 120, function () use ($like) {
            $products = Product::published()
                ->with(['category', 'brand'])
                ->where(fn($q) => $q
                    ->where('name', 'like', $like)
                    ->orWhere('model', 'like', $like)
                    ->orWhere('description', 'like', $like)
                )
                ->limit(5)
                ->get();

            $articles = BlogPost::published()
                ->where(fn($q) => $q
                    ->where('title', 'like', $like)
                    ->orWhere('excerpt', 'like', $like)
                )
                ->limit(3)
                ->get(['id', 'title', 'slug', 'category', 'image']);

            $categories = Category::active()
                ->where('name', 'like', $like)
                ->limit(3)
                ->get(['id', 'name', 'slug', 'icon']);

            return compact('products', 'articles', 'categories');
        });

        return response()->json([
            'data' => [
                'products'   => ProductResource::collection($results['products']),
                'articles'   => $results['articles'],
                'categories' => $results['categories'],
            ],
            'meta' => [
                'total' => $results['products']->count()
                         + $results['articles']->count()
                         + $results['categories']->count(),
            ],
        ]);
    }
}
