<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('schema_markups', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('type'); // Product, Article, FAQ, Organization, BreadcrumbList
            $table->string('page_type')->nullable(); // product, blog, page, global
            $table->string('page_slug')->nullable();
            $table->json('data');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('schema_markups');
    }
};
