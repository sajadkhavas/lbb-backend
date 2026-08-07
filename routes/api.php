<?php

use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\V1\PublicCatalogController;
use Illuminate\Support\Facades\Route;

Route::get('/health', fn () => response()->json([
    'status' => 'ok',
    'service' => config('app.name'),
]));

Route::get('/products', [CatalogController::class, 'index']);
Route::get('/products/{product:slug}', [CatalogController::class, 'show']);

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::middleware('throttle:public-catalog')->group(function (): void {
        Route::get('/categories', [PublicCatalogController::class, 'categories'])->name('categories.index');
        Route::get('/categories/{slug}', [PublicCatalogController::class, 'category'])->name('categories.show');

        Route::get('/products', [PublicCatalogController::class, 'products'])->name('products.index');
        Route::get('/products/{slug}', [PublicCatalogController::class, 'product'])->name('products.show');

        Route::get('/collections', [PublicCatalogController::class, 'collections'])->name('collections.index');
        Route::get('/collections/{slug}', [PublicCatalogController::class, 'collection'])->name('collections.show');

        Route::get('/drops', [PublicCatalogController::class, 'drops'])->name('drops.index');
        Route::get('/drops/{slug}', [PublicCatalogController::class, 'drop'])->name('drops.show');

        Route::get('/colors', [PublicCatalogController::class, 'colors'])->name('colors.index');
        Route::get('/sizes', [PublicCatalogController::class, 'sizes'])->name('sizes.index');
        Route::get('/catalog/facets', [PublicCatalogController::class, 'facets'])->name('catalog.facets');
    });

    Route::get('/search', [PublicCatalogController::class, 'search'])
        ->middleware('throttle:public-search')
        ->name('search');
});
