<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ab_test_results', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ab_test_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('ab_test_variants')->cascadeOnDelete();
            $table->string('session_id');
            $table->enum('event', ['impression', 'conversion']);
            $table->string('page_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ab_test_results');
    }
};
