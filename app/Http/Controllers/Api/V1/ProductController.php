<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $filters = $request->only(['category', 'brand', 'type', 'country', 'price_range', 'in_stock', 'usage', 'search']);
        $perPage = (int) $request->get('per_page', 15);
        $page    = (int) $request->get('page', 1);
        $cacheKey = 'products.' . md5(serialize($filters + ['per_page' => $perPage, 'page' => $page]));

        $products = Cache::remember($cacheKey, 300, fn () =>
            Product::published()
                ->with(['category', 'subcategory', 'brand'])
                ->filtered($filters)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->paginate($perPage)
        );

        return ProductResource::collection($products);
    }

    public function show(string $slug)
    {
        $product = Cache::remember("product.{$slug}", 600, fn () =>
            Product::with(['category', 'subcategory', 'brand'])
                ->where('slug', $slug)
                ->published()
                ->firstOrFail()
        );

        // increment بدون دست زدن به cache
        Product::withoutTimestamps(fn() =>
            Product::where('id', $product->id)->increment('view_count')
        );

        return new ProductResource($product);
    }

    public function featured()
    {
        $products = Cache::remember('products.featured', 600, fn () =>
            Product::published()
                ->where('is_featured', true)
                ->with(['category', 'brand'])
                ->orderBy('sort_order')
                ->limit(8)
                ->get()
        );

        return ProductResource::collection($products);
    }

    public function similar(string $slug)
    {
        $product = Product::where('slug', $slug)->firstOrFail();

        $similar = Product::published()
            ->where('id', '!=', $product->id)
            ->where('category_id', $product->category_id)
            ->with(['category', 'brand'])
            ->limit(4)
            ->get();

        return ProductResource::collection($similar);
    }
}
