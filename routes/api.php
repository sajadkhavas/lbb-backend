<?php

use App\Http\Controllers\Api\AccountAddressController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AccountOrderController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\DeliveryController;
use App\Http\Controllers\Api\InquiryController;
use App\Http\Controllers\Api\OtpAuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\PerformanceMetricController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\StoreContentController;
use App\Http\Controllers\Api\SystemController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\BlogController;
use App\Http\Controllers\Api\V1\BrandController;
use App\Http\Controllers\Api\V1\CategoryController;
use App\Http\Controllers\Api\V1\ContactController;
use App\Http\Controllers\Api\V1\NavigationController;
use App\Http\Controllers\Api\V1\NewsletterController;
use App\Http\Controllers\Api\V1\PageController;
use App\Http\Controllers\Api\V1\ProductController;
use App\Http\Controllers\Api\V1\RFQController;
use App\Http\Controllers\Api\V1\SchemaController;
use App\Http\Controllers\Api\V1\SearchController;
use App\Http\Controllers\Api\V1\SeoController;
use App\Http\Controllers\Api\V1\SettingsController;
use App\Http\Controllers\Api\V1\SliderController;
use Illuminate\Support\Facades\Route;

Route::prefix('system')->middleware('throttle:60,1')->group(function () {
    Route::get('health', [SystemController::class, 'health']);
    Route::get('ready', [SystemController::class, 'ready']);
    Route::get('meta', [SystemController::class, 'meta']);
    Route::get('contracts', [SystemController::class, 'contracts']);
    Route::get('openapi', [SystemController::class, 'openapi']);
});

Route::prefix('catalog')->middleware('throttle:120,1')->group(function () {
    Route::get('products', [CatalogController::class, 'products']);
    Route::get('products/{slug}', [CatalogController::class, 'product']);
    Route::get('products/{slug}/reviews', [ReviewController::class, 'index']);
    Route::get('categories', [CatalogController::class, 'categories']);
});

Route::get('delivery/options', [DeliveryController::class, 'options'])
    ->middleware('throttle:120,1');

Route::prefix('store')->middleware('throttle:120,1')->group(function () {
    Route::get('settings', [StoreContentController::class, 'settings']);
    Route::get('pages/{slug}', [StoreContentController::class, 'page']);
    Route::get('faqs', [StoreContentController::class, 'faqs']);
    Route::get('gallery', [StoreContentController::class, 'gallery']);
    Route::get('posts', [StoreContentController::class, 'posts']);
    Route::get('posts/{slug}', [StoreContentController::class, 'post']);
    Route::get('cities/{slug}', [StoreContentController::class, 'city']);
});

Route::post('inquiries', [InquiryController::class, 'store'])
    ->middleware('throttle:5,1');

Route::prefix('auth')->group(function () {
    Route::post('otp/request', [OtpAuthController::class, 'requestOtp'])
        ->middleware('throttle:otp-request');
    Route::post('otp/verify', [OtpAuthController::class, 'verify'])
        ->middleware('throttle:otp-verify');

    Route::middleware(['auth:customer', 'customer.active', 'throttle:60,1'])->group(function () {
        Route::get('me', [OtpAuthController::class, 'me']);
        Route::post('logout', [OtpAuthController::class, 'logout']);
    });
});

Route::middleware(['auth:customer', 'customer.active'])->group(function () {
    Route::post('checkout', [CheckoutController::class, 'store'])
        ->middleware('throttle:20,1');

    Route::post('orders/{orderId}/payments', [PaymentController::class, 'store'])
        ->middleware('throttle:10,1');
    Route::post('payments/verify', [PaymentController::class, 'verify'])
        ->middleware('throttle:20,1');
    Route::post('payments/zarinpal/verify', [PaymentController::class, 'verify'])
        ->middleware('throttle:20,1');

    Route::prefix('account')->middleware('throttle:60,1')->group(function () {
        Route::patch('profile', [AccountController::class, 'updateProfile']);
        Route::get('addresses', [AccountAddressController::class, 'index']);
        Route::post('addresses', [AccountAddressController::class, 'store']);
        Route::put('addresses/{addressId}', [AccountAddressController::class, 'update']);
        Route::delete('addresses/{addressId}', [AccountAddressController::class, 'destroy']);
        Route::get('orders', [AccountOrderController::class, 'index']);
        Route::get('orders/{orderId}', [AccountOrderController::class, 'show']);
        Route::post('orders/{orderId}/cancel', [AccountOrderController::class, 'cancel'])
            ->middleware('throttle:10,1');
        Route::post('orders/{orderId}/reviews', [ReviewController::class, 'store'])
            ->middleware('throttle:10,1');
    });
});

/*
|--------------------------------------------------------------------------
| Legacy ToolMaster API
|--------------------------------------------------------------------------
|
| These endpoints are preserved temporarily so the existing database and
| Filament resources can be migrated incrementally. Responses include
| deprecation headers. New Winimi commerce endpoints must not be added here.
|
*/
Route::prefix('v1')->middleware('api.legacy')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::middleware('throttle:10,1')->group(function () {
            Route::post('login', [AuthController::class, 'login']);
            Route::post('register', [AuthController::class, 'register']);
        });
        Route::middleware(['auth:sanctum', 'throttle:60,1'])->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });

    Route::middleware('throttle:120,1')->group(function () {
        Route::get('products', [ProductController::class, 'index']);
        Route::get('products/featured', [ProductController::class, 'featured']);
        Route::get('products/{slug}', [ProductController::class, 'show']);
        Route::get('products/{slug}/similar', [ProductController::class, 'similar']);

        Route::get('categories', [CategoryController::class, 'index']);
        Route::get('categories/{slug}', [CategoryController::class, 'show']);
        Route::get('categories/{slug}/products', [CategoryController::class, 'products']);
        Route::get('categories/{slug}/subcategories', [CategoryController::class, 'subcategories']);

        Route::get('brands', [BrandController::class, 'index']);
        Route::get('brands/{slug}', [BrandController::class, 'show']);
        Route::get('brands/{slug}/products', [BrandController::class, 'products']);

        Route::get('blog', [BlogController::class, 'index']);
        Route::get('blog/latest', [BlogController::class, 'latest']);
        Route::get('blog/{slug}', [BlogController::class, 'show']);

        Route::get('pages/{slug}', [PageController::class, 'show']);
        Route::get('navigation', [NavigationController::class, 'index']);
        Route::get('settings', [SettingsController::class, '__invoke']);
        Route::get('sliders', [SliderController::class, '__invoke']);

        Route::get('seo', [SeoController::class, 'index']);
        Route::get('seo/{type}/{slug}', [SeoController::class, 'show']);
        Route::get('schema', [SchemaController::class, 'index']);
    });

    Route::middleware('throttle:60,1')->group(function () {
        Route::get('search', SearchController::class);
        Route::post('performance-metrics', [PerformanceMetricController::class, 'store']);
    });

    Route::middleware('throttle:10,1')->group(function () {
        Route::post('rfq', [RFQController::class, 'store']);
        Route::post('contact', [ContactController::class, 'store']);
        Route::post('newsletter', [NewsletterController::class, 'store']);
    });

    Route::middleware('throttle:30,1')->group(function () {
        Route::get('rfq/{reference}', [RFQController::class, 'show']);
    });
});
