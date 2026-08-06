<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'description', 'type', 'value',
        'min_order_amount', 'max_discount_amount',
        'usage_limit', 'used_count', 'is_active',
        'starts_at', 'expires_at',
    ];

    protected $casts = [
        'is_active'          => 'boolean',
        'starts_at'          => 'datetime',
        'expires_at'         => 'datetime',
        'value'              => 'decimal:2',
        'min_order_amount'   => 'decimal:2',
        'max_discount_amount'=> 'decimal:2',
    ];

    public function isValid(): bool
    {
        if (! $this->is_active) return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->expires_at && now()->gt($this->expires_at)) return false;
        return true;
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
