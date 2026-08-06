<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Cache;

class ProductObserver
{
    public function saved(Product $product): void
    {
        $this->clearCache($product);
    }

    public function deleted(Product $product): void
    {
        $this->clearCache($product);
    }

    public function restored(Product $product): void
    {
        $this->clearCache($product);
    }

    private function clearCache(Product $product): void
    {
        Cache::forget("product.{$product->slug}");
        Cache::forget('products.featured');
        // پاک کردن همه cache های لیست محصولات
        Cache::flush();
    }
}