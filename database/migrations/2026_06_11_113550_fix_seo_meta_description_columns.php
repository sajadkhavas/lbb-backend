<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->text('meta_description')->nullable()->change();
            $table->text('og_description')->nullable()->change();
            $table->text('meta_keywords')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('seo_meta', function (Blueprint $table) {
            $table->string('meta_description', 500)->nullable()->change();
            $table->string('og_description', 500)->nullable()->change();
            $table->string('meta_keywords', 500)->nullable()->change();
        });
    }
};
