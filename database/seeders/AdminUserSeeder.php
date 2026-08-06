<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@toolmaster.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('Admin@2025!Change'),
                'email_verified_at' => now(),
            ]
        );
    }
}
