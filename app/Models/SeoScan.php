<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SeoScan extends Model
{
    protected $fillable = [
        'url', 'title', 'title_length', 'meta_description',
        'meta_description_length', 'has_h1', 'h1_count',
        'has_canonical', 'has_og_tags', 'has_schema',
        'images_without_alt', 'word_count', 'page_size_kb',
        'score', 'issues', 'scanned_at',
    ];

    protected $casts = [
        'has_h1'        => 'boolean',
        'has_canonical' => 'boolean',
        'has_og_tags'   => 'boolean',
        'has_schema'    => 'boolean',
        'issues'        => 'array',
        'scanned_at'    => 'datetime',
    ];

    public function getScoreColorAttribute(): string
    {
        if ($this->score >= 80) return 'success';
        if ($this->score >= 50) return 'warning';
        return 'danger';
    }

    public function scopeGoodScore($query)
    {
        return $query->where('score', '>=', 80);
    }

    public function scopeWarningScore($query)
    {
        return $query->whereBetween('score', [50, 79]);
    }

    public function scopePoorScore($query)
    {
        return $query->where('score', '<', 50);
    }
}
