<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BakeryCityPage extends Model
{
    protected $fillable = [
        'city',
        'slug',
        'title',
        'description',
        'content',
        'meta_title',
        'meta_description',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $page): void {
            $page->public_id ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }
}
