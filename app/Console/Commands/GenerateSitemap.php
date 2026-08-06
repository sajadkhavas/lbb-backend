<?php

namespace App\Console\Commands;

use App\Models\{BlogPost, Brand, Category, Product, Subcategory};
use Illuminate\Console\Command;
use Spatie\Sitemap\Sitemap;
use Spatie\Sitemap\Tags\Url;

class GenerateSitemap extends Command
{
    protected $signature   = 'sitemap:generate';
    protected $description = 'Generate XML sitemap';

    public function handle(): void
    {
        $frontend = config('app.frontend_url', 'https://toolmaster.com');
        $sitemap  = Sitemap::create();

        foreach (['', '/products', '/brands', '/blog', '/about', '/contact'] as $p) {
            $sitemap->add(Url::create($frontend . $p)->setPriority($p === '' ? 1.0 : 0.8));
        }

        Category::query()->where('is_active', true)->get()->each(fn ($c) =>
            $sitemap->add(Url::create("{$frontend}/products/category/{$c->slug}")
                ->setPriority(0.85)->setLastModificationDate($c->updated_at))
        );

        Subcategory::query()->where('is_active', true)->with('category')->get()->each(fn ($s) =>
            $sitemap->add(Url::create("{$frontend}/products/category/{$s->category->slug}/{$s->slug}")
                ->setPriority(0.8))
        );

        Brand::query()->where('is_active', true)->get()->each(fn ($b) =>
            $sitemap->add(Url::create("{$frontend}/brands/{$b->slug}")->setPriority(0.75))
        );

        Product::query()->where('status', 'published')->get()->each(fn ($p) =>
            $sitemap->add(Url::create("{$frontend}/products/{$p->slug}")
                ->setPriority(0.7)->setLastModificationDate($p->updated_at))
        );

        BlogPost::query()->where('status', 'published')->get()->each(fn ($b) =>
            $sitemap->add(Url::create("{$frontend}/blog/{$b->slug}")
                ->setPriority(0.65)->setLastModificationDate($b->updated_at))
        );

        $sitemap->writeToFile(public_path('sitemap.xml'));
        $this->info('Sitemap generated successfully at public/sitemap.xml');
    }
}
