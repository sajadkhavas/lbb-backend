<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->string('page_url');
            $table->float('lcp')->nullable()->comment('Largest Contentful Paint (ms)');
            $table->float('fid')->nullable()->comment('First Input Delay (ms)');
            $table->float('cls')->nullable()->comment('Cumulative Layout Shift');
            $table->float('fcp')->nullable()->comment('First Contentful Paint (ms)');
            $table->float('ttfb')->nullable()->comment('Time to First Byte (ms)');
            $table->string('device_type')->nullable();
            $table->string('browser')->nullable();
            $table->string('country')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('performance_metrics');
    }
};
