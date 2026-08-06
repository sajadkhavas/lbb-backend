<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Cache;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Brand extends Model
{
    use HasSlug;

    protected $fillable = [
        'name', 'slug', 'country', 'description', 'long_description',
        'logo', 'website', 'meta_title', 'meta_description', 'meta_keywords',
        'sort_order', 'is_featured', 'is_active',
    ];

    protected $casts = ['is_featured' => 'boolean', 'is_active' => 'boolean'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    public function products(): HasMany { return $this->hasMany(Product::class); }

    public function scopeActive($q) { return $q->where('is_active', true); }

    // cache invalidation
    protected static function booted(): void
    {
        static::saved(fn ()   => static::clearCache());
        static::deleted(fn () => static::clearCache());
    }

    public static function clearCache(): void
    {
        Cache::forget('brands.all');
        Cache::forget('brands.featured');
    }
}
