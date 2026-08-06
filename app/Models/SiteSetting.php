<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SiteSetting extends Model
{
    protected $fillable = ['group', 'key', 'value', 'type', 'label'];

    public static function get(string $key, mixed $default = null): mixed
    {
        $all = Cache::rememberForever('site_settings_kv',
            fn () => static::pluck('value', 'key')->toArray()
        );
        return $all[$key] ?? $default;
    }

    protected static function booted(): void
    {
        static::saved(fn () => Cache::forget('site_settings_kv') || Cache::forget('site_settings'));
        static::deleted(fn () => Cache::forget('site_settings_kv') || Cache::forget('site_settings'));
    }
}
