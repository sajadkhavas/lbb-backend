<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bakery_categories', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->string('name', 120);
            $table->string('slug', 140)->unique();
            $table->text('description')->nullable();
            $table->string('image_path')->nullable();
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 180)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('bakery_products', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('category_id')
                ->constrained('bakery_categories')
                ->restrictOnDelete();
            $table->string('name', 180);
            $table->string('slug', 200)->unique();
            $table->string('product_code', 80)->unique();
            $table->string('short_description', 320)->nullable();
            $table->longText('description')->nullable();
            $table->json('ingredients')->nullable();
            $table->json('allergens')->nullable();
            $table->string('shelf_life', 220)->nullable();
            $table->text('storage_instructions')->nullable();
            $table->unsignedSmallInteger('preparation_time_days')->nullable();
            $table->boolean('requires_cooling')->default(false)->index();
            $table->boolean('content_verified')->default(false)->index();
            $table->boolean('media_verified')->default(false)->index();
            $table->boolean('is_active')->default(false)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->string('meta_title', 70)->nullable();
            $table->string('meta_description', 180)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'is_active', 'sort_order'], 'bakery_products_listing_index');
        });

        Schema::create('bakery_product_variants', function (Blueprint $table): void {
            $table->id();
            $table->char('public_id', 26)->unique();
            $table->foreignId('product_id')
                ->constrained('bakery_products')
                ->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('sku', 100)->unique();
            $table->unsignedInteger('weight_grams')->nullable();
            $table->unsignedBigInteger('regular_price_toman');
            $table->unsignedBigInteger('sale_price_toman')->nullable();
            $table->unsignedInteger('stock_quantity')->default(0);
            $table->unsignedInteger('low_stock_threshold')->default(5);
            $table->boolean('is_default')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->unique(['product_id', 'name'], 'bakery_variant_product_name_unique');
            $table->index(['product_id', 'is_active', 'sort_order'], 'bakery_variants_listing_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bakery_product_variants');
        Schema::dropIfExists('bakery_products');
        Schema::dropIfExists('bakery_categories');
    }
};
