<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            AdminUserSeeder::class,
            CategorySeeder::class,
            BrandSeeder::class,
            SiteSettingsSeeder::class,
            SliderSeeder::class,
        ]);

        if (filter_var(env('SEED_WINIMI_STAGING', false), FILTER_VALIDATE_BOOL)) {
            $this->call(WinimiStagingSeeder::class);
        }
    }
}
