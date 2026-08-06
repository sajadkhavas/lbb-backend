<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class IpBlacklist extends Model
{
    protected $fillable = [
        'ip_address', 'reason', 'is_active', 'blocked_at', 'expires_at',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'blocked_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        // هر بار که IP ذخیره یا حذف شد، cache پاک بشه
        static::saved(function (self $model) {
            Cache::forget("ip_blocked:{$model->ip_address}");
        });

        static::deleted(function (self $model) {
            Cache::forget("ip_blocked:{$model->ip_address}");
        });
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()));
    }

    public static function isBlocked(string $ip): bool
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            return false;
        }
        return static::active()->where('ip_address', $ip)->exists();
    }

    public static function block(string $ip, string $reason = '', ?string $expiresAt = null): self
    {
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            throw new \InvalidArgumentException("آدرس IP نامعتبر است: {$ip}");
        }

        return static::firstOrCreate(
            ['ip_address' => $ip],
            [
                'reason'     => $reason,
                'is_active'  => true,
                'blocked_at' => now(),
                'expires_at' => $expiresAt,
            ]
        );
    }

    public static function unblock(string $ip): void
    {
        static::where('ip_address', $ip)->update(['is_active' => false]);
        Cache::forget("ip_blocked:{$ip}");
    }
}
