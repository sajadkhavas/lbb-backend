<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchemaMarkup extends Model
{
    protected $fillable = [
        'name', 'type', 'page_type', 'page_slug', 'data', 'is_active',
    ];

    protected $casts = [
        'data'      => 'array',
        'is_active' => 'boolean',
    ];
}
