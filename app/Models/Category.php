<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use HasSlug;

    protected $fillable = [
        'name', 'name_en', 'slug', 'description', 'long_description',
        'meta_title', 'meta_description', 'meta_keywords', 'og_image',
        'faq_schema', 'hero_title', 'hero_subtitle', 'icon', 'image',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'faq_schema' => 'array',
        'is_active'  => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            Cache::forget("category.{$model->slug}");
            Cache::forget("categories.all");
        });
        static::deleted(function (self $model) {
            Cache::forget("category.{$model->slug}");
            Cache::forget("categories.all");
        });
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name_en')
            ->saveSlugsTo('slug')
            ->doNotGenerateSlugsOnUpdate();
    }

    public function subcategories(): HasMany
    {
        return $this->hasMany(Subcategory::class)->orderBy('sort_order');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query) { return $query->where('is_active', true); }

    public function getSeoTitleAttribute(): string
    {
        return $this->meta_title ?: "خرید و استعلام {$this->name} | تول‌مستر";
    }

    public function getSeoDescriptionAttribute(): string
    {
        return $this->meta_description ?: ($this->description ?? '');
    }

    public function getBreadcrumbSchemaAttribute(): array
    {
        $frontend = config('app.frontend_url');
        return [
            '@context' => 'https://schema.org',
            '@type'    => 'BreadcrumbList',
            'itemListElement' => [
                ['@type' => 'ListItem', 'position' => 1, 'name' => 'خانه', 'item' => $frontend],
                ['@type' => 'ListItem', 'position' => 2, 'name' => 'محصولات', 'item' => "{$frontend}/products"],
                ['@type' => 'ListItem', 'position' => 3, 'name' => $this->name, 'item' => "{$frontend}/products/category/{$this->slug}"],
            ],
        ];
    }
}
