<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_dashboard_renders_without_legacy_widget_dependencies(): void
    {
        $user = User::factory()->create();
        $user->assignRole(Role::findOrCreate('super_admin', 'web'));

        $this->actingAs($user)
            ->get('/admin')
            ->assertOk()
            ->assertSee('محصولات')
            ->assertSee('دسته‌ها')
            ->assertSee('مشتریان')
            ->assertSee('سفارش‌ها');
    }
}
