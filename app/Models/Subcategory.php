<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subcategory extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'full_name_en',
        'description', 'long_description',
        'meta_title', 'meta_description', 'meta_keywords',
        'faq_schema', 'image', 'sort_order', 'is_active',
    ];

    protected $casts = [
        'faq_schema' => 'array',
        'is_active'  => 'boolean',
    ];

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function products(): HasMany   { return $this->hasMany(Product::class); }
    public function scopeActive($q)        { return $q->where('is_active', true); }
}
