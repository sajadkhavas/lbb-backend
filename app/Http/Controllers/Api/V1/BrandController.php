<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\BrandResource;
use App\Http\Resources\ProductResource;
use App\Models\Brand;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BrandController extends Controller
{
    public function index()
    {
        $brands = Cache::remember('brands.all', 600, fn () =>
            Brand::active()->withCount('products')->orderBy('sort_order')->get()
        );

        return BrandResource::collection($brands);
    }

    public function show(string $slug)
    {
        $brand = Cache::remember("brands.{$slug}", 600, fn () =>
            Brand::active()->withCount('products')->where('slug', $slug)->firstOrFail()
        );

        return new BrandResource($brand);
    }

    public function products(string $slug, Request $request)
    {
        $perPage = min((int) $request->get('per_page', 15), 50);
        $page    = (int) $request->get('page', 1);

        $brand = Cache::remember("brands.{$slug}", 600, fn () =>
            Brand::where('slug', $slug)->firstOrFail()
        );

        $cacheKey = "brands.{$slug}.products.page{$page}.per{$perPage}";

        $products = Cache::remember($cacheKey, 300, fn () =>
            Product::published()
                ->where('brand_id', $brand->id)
                ->with(['category', 'brand'])
                ->paginate($perPage)
        );

        return ProductResource::collection($products);
    }
}
