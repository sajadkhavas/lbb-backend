<?php

namespace App\Models;

use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class BakeryProduct extends Model implements HasMedia
{
    use HasSlug, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'product_code',
        'short_description',
        'description',
        'ingredients',
        'allergens',
        'shelf_life',
        'storage_instructions',
        'preparation_time_days',
        'requires_cooling',
        'content_verified',
        'media_verified',
        'is_active',
        'is_featured',
        'sort_order',
        'meta_title',
        'meta_description',
    ];

    protected $casts = [
        'ingredients' => 'array',
        'allergens' => 'array',
        'preparation_time_days' => 'integer',
        'requires_cooling' => 'boolean',
        'content_verified' => 'boolean',
        'media_verified' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $product): void {
            $product->public_id ??= (string) Str::ulid();
        });

        static::saved(function (self $product): void {
            self::flushCatalogCache($product->slug);
        });

        static::deleted(function (self $product): void {
            self::flushCatalogCache($product->slug);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'name',
                'product_code',
                'category_id',
                'requires_cooling',
                'content_verified',
                'media_verified',
                'is_active',
                'is_featured',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName): string => "محصول بیکری {$this->name} {$eventName} شد");
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('catalog-main')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif']);

        $this->addMediaCollection('catalog-gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp', 'image/avif']);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(BakeryCategory::class, 'category_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(BakeryProductVariant::class, 'product_id')
            ->orderByDesc('is_default')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    public function activeVariants(): HasMany
    {
        return $this->variants()->where('is_active', true);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->whereHas('category', fn (Builder $category): Builder => $category->active())
            ->whereHas('activeVariants');
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderBy('name');
    }

    private static function flushCatalogCache(string $slug): void
    {
        Cache::forget("bakery.catalog.product.{$slug}");

        if (Cache::getStore() instanceof TaggableStore) {
            Cache::tags(['bakery-catalog'])->flush();
        }
    }
}
