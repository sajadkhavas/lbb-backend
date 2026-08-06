<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Illuminate\Support\Facades\Cache;

class BlogPost extends Model
{
    use HasSlug, SoftDeletes, LogsActivity;

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            Cache::forget("blog.{$model->slug}");
            Cache::forget("blog.latest");
            Cache::tags(["blog"])->flush();
        });
        static::deleted(function (self $model) {
            Cache::forget("blog.{$model->slug}");
            Cache::forget("blog.latest");
        });
    }

    protected $fillable = [
        'title', 'slug', 'excerpt', 'content', 'author', 'category',
        'product_categories', 'product_types', 'tags',
        'image', 'read_time',
        'meta_title', 'meta_description', 'meta_keywords',
        'status', 'published_at', 'view_count',
    ];

    protected $casts = [
        'product_categories' => 'array',
        'product_types'      => 'array',
        'tags'               => 'array',
        'published_at'       => 'datetime',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['title', 'status', 'author'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->setDescriptionForEvent(fn(string $eventName) => "مقاله {$eventName} شد");
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('title')->saveSlugsTo('slug');
    }

    public function scopePublished($q) { return $q->where('status', 'published'); }
}
