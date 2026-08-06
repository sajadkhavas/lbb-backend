<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_url')->unique()->comment('آدرس قدیمی');
            $table->string('to_url')->comment('آدرس جدید');
            $table->smallInteger('status_code')->default(301)->comment('301 یا 302');
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('hit_count')->default(0)->comment('تعداد دفعات استفاده');
            $table->string('note')->nullable()->comment('یادداشت');
            $table->timestamps();
            $table->index(['from_url', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
