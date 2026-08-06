<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SeoMeta extends Model
{
    protected $table = 'seo_meta';

    protected $fillable = [
        'page_key',
        'page_label',
        'meta_title',
        'meta_description',
        'meta_keywords',
        'og_title',
        'og_description',
        'og_image',
        'canonical_url',
        'robots',
        'schema_json',
        'is_active',
    ];

    protected $casts = [
        'schema_json' => 'array',
        'is_active'   => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saved(function (self $model) {
            Cache::forget('seo:' . md5($model->page_key));
            Cache::forget('seo:default');
        });
        static::deleted(function (self $model) {
            Cache::forget('seo:' . md5($model->page_key));
            Cache::forget('seo:default');
        });
    }

    public static function forPage(string $key): ?self
    {
        return static::where('page_key', $key)->where('is_active', true)->first();
    }
}
