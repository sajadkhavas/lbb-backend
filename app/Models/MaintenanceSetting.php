<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceSetting extends Model
{
    protected $fillable = [
        'is_enabled', 'title', 'message', 'allowed_ips', 'scheduled_end',
    ];

    protected $casts = [
        'is_enabled'    => 'boolean',
        'scheduled_end' => 'datetime',
    ];

    public static function current(): self
    {
        return static::firstOrCreate([], [
            'is_enabled' => false,
            'title'      => 'سایت در حال بروزرسانی است',
            'message'    => 'به زودی برمیگردیم.',
        ]);
    }

    public function getAllowedIpsArrayAttribute(): array
    {
        if (!$this->allowed_ips) return [];
        return array_map('trim', explode(',', $this->allowed_ips));
    }

    public function isIpAllowed(string $ip): bool
    {
        return in_array($ip, $this->allowed_ips_array);
    }
}
