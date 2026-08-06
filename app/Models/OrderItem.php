<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class OrderItem extends Model
{
    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'product_public_id',
        'variant_public_id',
        'product_name',
        'variant_name',
        'product_code',
        'sku',
        'weight_grams',
        'requires_cooling',
        'unit_price_toman',
        'quantity',
        'line_total_toman',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $item): void {
            $item->public_id ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'weight_grams' => 'integer',
            'requires_cooling' => 'boolean',
            'unit_price_toman' => 'integer',
            'quantity' => 'integer',
            'line_total_toman' => 'integer',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(BakeryProduct::class);
    }

    public function variant(): BelongsTo
    {
        return $this->belongsTo(BakeryProductVariant::class);
    }
}
