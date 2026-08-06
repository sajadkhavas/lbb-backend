<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BakeryPost extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'content',
        'category',
        'tags',
        'cover_url',
        'author',
        'status',
        'published_at',
        'view_count',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $post): void {
            $post->public_id ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'tags' => 'array',
            'published_at' => 'datetime',
            'view_count' => 'integer',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }
}
