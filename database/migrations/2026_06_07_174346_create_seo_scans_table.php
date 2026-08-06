<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_scans', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->string('title')->nullable();
            $table->integer('title_length')->nullable();
            $table->text('meta_description')->nullable();
            $table->integer('meta_description_length')->nullable();
            $table->boolean('has_h1')->default(false);
            $table->integer('h1_count')->default(0);
            $table->boolean('has_canonical')->default(false);
            $table->boolean('has_og_tags')->default(false);
            $table->boolean('has_schema')->default(false);
            $table->integer('images_without_alt')->default(0);
            $table->integer('word_count')->default(0);
            $table->float('page_size_kb')->nullable();
            $table->tinyInteger('score')->default(0)->comment('0-100');
            $table->json('issues')->nullable();
            $table->timestamp('scanned_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_scans');
    }
};
