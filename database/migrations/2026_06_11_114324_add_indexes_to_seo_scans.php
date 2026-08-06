<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_scans', function (Blueprint $table) {
            $table->index('score');
            $table->index('scanned_at');
            $table->index('url');
        });
    }

    public function down(): void
    {
        Schema::table('seo_scans', function (Blueprint $table) {
            $table->dropIndex(['score']);
            $table->dropIndex(['scanned_at']);
            $table->dropIndex(['url']);
        });
    }
};
