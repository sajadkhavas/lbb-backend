<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class ApiKey extends Model
{
    protected $fillable = [
        'name', 'key', 'permissions', 'rate_limit',
        'is_active', 'expires_at', 'last_used_at', 'usage_count',
    ];

    protected $casts = [
        'permissions'  => 'array',
        'is_active'    => 'boolean',
        'expires_at'   => 'datetime',
        'last_used_at' => 'datetime',
    ];

    protected $hidden = ['key'];

    public static function generate(string $name): self
    {
        return static::create([
            'name' => $name,
            'key'  => 'tk_' . Str::random(40),
        ]);
    }

    public function isValid(): bool
    {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        return true;
    }

    public function recordUsage(): void
    {
        $this->increment('usage_count');
        $this->update(['last_used_at' => now()]);
    }
}
