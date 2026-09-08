<?php

namespace Tests\Feature;

use App\Models\Redirect;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminLoginAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_login_is_available_without_the_unimplemented_redirect_subsystem(): void
    {
        $this->assertFalse(class_exists(Redirect::class));

        $this->get('/admin/login')
            ->assertOk();
    }
}
