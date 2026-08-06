<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('model')->nullable();
            $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained();
            $table->foreignId('subcategory_id')->nullable()->constrained();
            $table->foreignId('brand_id')->nullable()->constrained();
            $table->string('country', 10)->nullable();

            $table->text('description');
            $table->longText('long_description')->nullable();
            $table->string('excerpt', 300)->nullable();

            $table->json('specs')->nullable();
            $table->json('usage')->nullable();
            $table->json('applications')->nullable();
            $table->json('gallery')->nullable();
            $table->string('image')->nullable();
            $table->string('price_range')->nullable();

            $table->boolean('in_stock')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->enum('status', ['published', 'draft', 'archived'])->default('draft');

            $table->string('meta_title', 60)->nullable();
            $table->string('meta_description', 160)->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('og_image')->nullable();
            $table->string('schema_type')->default('Product');

            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedInteger('rfq_count')->default(0);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status']);
            $table->index(['brand_id', 'status']);
            $table->index(['price_range', 'in_stock']);
        });
    }

    public function down(): void { Schema::dropIfExists('products'); }
};
