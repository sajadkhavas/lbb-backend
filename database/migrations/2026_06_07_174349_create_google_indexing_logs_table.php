<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('google_indexing_logs', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->enum('type', ['URL_UPDATED', 'URL_DELETED'])->default('URL_UPDATED');
            $table->enum('status', ['pending', 'success', 'failed'])->default('pending');
            $table->text('response')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('google_indexing_logs');
    }
};
