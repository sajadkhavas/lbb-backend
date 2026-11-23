<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $category = DB::table('categories')
                ->where('slug', 'shloar')
                ->whereNull('deleted_at')
                ->lockForUpdate()
                ->first(['id']);

            if ($category === null) {
                return;
            }

            if (DB::table('categories')->where('slug', 'pants')->exists()) {
                throw new RuntimeException('Cannot rename shloar: the pants category slug already exists.');
            }

            DB::table('categories')->where('id', $category->id)->update([
                'slug' => 'pants',
                'updated_at' => now(),
            ]);
        });
    }

    public function down(): void
    {
        // Keep the public URL stable after publishing the permanent redirect.
    }
};
