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
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\StoreContentController;
use App\Http\Controllers\Api\SystemController;
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
