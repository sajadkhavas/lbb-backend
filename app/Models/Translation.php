<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Translation extends Model
{
    protected $fillable = ['group', 'key', 'value'];

    protected $casts = ['value' => 'array'];

    public static function getTranslation(string $group, string $key, string $locale = 'fa'): ?string
    {
        $cacheKey = "translation_{$group}_{$key}_{$locale}";
        return Cache::remember($cacheKey, 3600, function () use ($group, $key, $locale) {
            $translation = static::where('group', $group)->where('key', $key)->first();
            return $translation?->value[$locale] ?? null;
        });
    }
}
