<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $fillable = [
        'from_url', 'to_url', 'status_code', 'is_active', 'hit_count', 'note',
    ];

    protected $casts = [
        'is_active'   => 'boolean',
        'hit_count'   => 'integer',
        'status_code' => 'integer',
    ];

    public static function findRedirect(string $url): ?self
    {
        return static::where('from_url', $url)
            ->where('is_active', true)
            ->first();
    }
}
