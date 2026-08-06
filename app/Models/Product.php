<?php

namespace App\Models;

use Illuminate\Cache\TaggableStore;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Product extends Model implements HasMedia
{
    use HasSlug, InteractsWithMedia, LogsActivity, SoftDeletes;

    protected $fillable = [
        'name', 'model', 'slug', 'category_id', 'subcategory_id', 'brand_id',
        'country', 'description', 'long_description', 'excerpt',
        'specs', 'usage', 'applications', 'gallery', 'image', 'price_range',
        'in_stock', 'is_featured', 'status',
        'meta_title', 'meta_description', 'meta_keywords', 'og_image', 'schema_type',
        'view_count', 'rfq_count', 'sort_order',
    ];

    protected $casts = [
        'specs' => 'array',
        'usage' => 'array',
        'applications' => 'array',
        'gallery' => 'array',
        'in_stock' => 'boolean',
        'is_featured' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            Cache::forget("product.{$model->slug}");
            Cache::forget('products.featured');

            if (Cache::getStore() instanceof TaggableStore) {
                Cache::tags(['products'])->flush();
            }
        });

        static::deleted(function (self $model) {
            Cache::forget("product.{$model->slug}");
            Cache::forget('products.featured');
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'model', 'status', 'in_stock', 'is_featured'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn (string $eventName) => "محصول {$this->name} {$eventName} شد");
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main-image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('og-image')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(['name', 'model'])
            ->saveSlugsTo('slug');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subcategory(): BelongsTo
    {
        return $this->belongsTo(Subcategory::class);
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function rfqItems(): HasMany
    {
        return $this->hasMany(RfqItem::class);
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeByCategory(Builder $query, string $slug): Builder
    {
        return $query->whereHas('category', fn ($relation) => $relation->where('slug', $slug));
    }

    public function scopeByBrand(Builder $query, string $slug): Builder
    {
        return $query->whereHas('brand', fn ($relation) => $relation->where('slug', $slug));
    }

    public function scopeByType(Builder $query, string $slug): Builder
    {
        return $query->whereHas('subcategory', fn ($relation) => $relation->where('slug', $slug));
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        if (! empty($filters['category'])) {
            $query->byCategory($filters['category']);
        }
        if (! empty($filters['brand'])) {
            $query->byBrand($filters['brand']);
        }
        if (! empty($filters['type'])) {
            $query->byType($filters['type']);
        }
        if (! empty($filters['country'])) {
            $query->where('country', $filters['country']);
        }
        if (! empty($filters['price_range'])) {
            $query->where('price_range', $filters['price_range']);
        }
        if (isset($filters['in_stock'])) {
            $query->where('in_stock', filter_var($filters['in_stock'], FILTER_VALIDATE_BOOL));
        }
        if (! empty($filters['usage'])) {
            $query->whereJsonContains('usage', $filters['usage']);
        }
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(fn ($nested) => $nested
                ->where('name', 'like', "%{$search}%")
                ->orWhere('model', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%"));
        }

        return $query;
    }

    public function getProductSchemaAttribute(): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'Product',
            'name' => $this->name,
            'model' => $this->model,
            'description' => $this->description,
            'brand' => ['@type' => 'Brand', 'name' => $this->brand?->name],
            'offers' => [
                '@type' => 'Offer',
                'availability' => $this->in_stock
                    ? 'https://schema.org/InStock'
                    : 'https://schema.org/OutOfStock',
                'priceCurrency' => 'IRR',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => config('winimi.brand.name_en', 'Winimi Bakery'),
                ],
            ],
        ];
    }
}
