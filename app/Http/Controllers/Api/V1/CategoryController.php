<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Http\Resources\ProductResource;
use App\Http\Resources\SubcategoryResource;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    public function index()
    {
        $cats = Cache::remember('categories.all', 600, fn () =>
            Category::active()
                ->with('subcategories')
                ->withCount('products')
                ->orderBy('sort_order')
                ->get()
        );

        return CategoryResource::collection($cats);
    }

    public function show(string $slug)
    {
        $cat = Cache::remember("category.{$slug}", 600, fn () =>
            Category::with('subcategories')->where('slug', $slug)->active()->firstOrFail()
        );
        return new CategoryResource($cat);
    }

    public function products(string $slug, Request $request)
    {
        $cat = Category::where('slug', $slug)->firstOrFail();
        $products = Product::published()
            ->where('category_id', $cat->id)
            ->with(['category', 'brand', 'subcategory'])
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->paginate((int) $request->get('per_page', 15));

        return ProductResource::collection($products);
    }

    public function subcategories(string $slug)
    {
        $cat = Category::where('slug', $slug)->firstOrFail();
        return SubcategoryResource::collection($cat->subcategories()->active()->get());
    }
}
