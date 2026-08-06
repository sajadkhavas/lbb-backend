<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BakeryCatalogFilamentTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_open_bakery_catalog_create_forms(): void
    {
        Role::create([
            'name' => 'super_admin',
            'guard_name' => 'web',
        ]);

        $admin = User::create([
            'name' => 'Winimi Admin',
            'email' => 'admin@example.test',
            'password' => 'catalog-test-password',
        ]);
        $admin->assignRole('super_admin');

        $this->actingAs($admin)
            ->get('/admin/bakery-categories/create')
            ->assertOk();

        $this->actingAs($admin)
            ->get('/admin/bakery-products/create')
            ->assertOk();
    }
}
