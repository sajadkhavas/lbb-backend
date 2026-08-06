<?php

namespace Tests\Feature;

use Database\Seeders\WinimiStagingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ManagedContentFixtureTest extends TestCase
{
    use RefreshDatabase;

    public function test_required_managed_content_pages_are_published_for_acceptance(): void
    {
        $this->seed(WinimiStagingSeeder::class);

        $pages = [
            'about' => 'درباره وینیمی',
            'quality' => 'شفافیت و کیفیت',
            'shipping' => 'شرایط ارسال',
            'privacy' => 'حریم خصوصی',
            'terms' => 'شرایط استفاده',
        ];

        foreach ($pages as $slug => $title) {
            $this->getJson("/api/store/pages/{$slug}")
                ->assertOk()
                ->assertJsonPath('success', true)
                ->assertJsonPath('data.page.slug', $slug)
                ->assertJsonPath('data.page.title', $title)
                ->assertJsonPath('data.page.content', fn (mixed $content): bool => is_string($content) && mb_strlen($content) >= 40);
        }
    }

    public function test_staging_seeder_remains_blocked_in_production(): void
    {
        $originalEnvironment = app()->environment();
        app()->detectEnvironment(static fn (): string => 'production');

        try {
            $this->expectException(RuntimeException::class);
            $this->expectExceptionMessage('Winimi staging data must never be seeded in production.');

            (new WinimiStagingSeeder)->run();
        } finally {
            app()->detectEnvironment(static fn (): string => $originalEnvironment);
        }
    }
}
