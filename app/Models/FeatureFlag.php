<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class FeatureFlag extends Model
{
    protected $fillable = [
        'name', 'key', 'description', 'is_enabled', 'conditions',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'conditions' => 'array',
    ];

    public static function isEnabled(string $key): bool
    {
        return Cache::remember("feature_flag_{$key}", 60, function () use ($key) {
            return static::where('key', $key)->value('is_enabled') ?? false;
        });
    }

    public static function enable(string $key): void
    {
        static::where('key', $key)->update(['is_enabled' => true]);
        Cache::forget("feature_flag_{$key}");
    }

    public static function disable(string $key): void
    {
        static::where('key', $key)->update(['is_enabled' => false]);
        Cache::forget("feature_flag_{$key}");
    }
}
