<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use InvalidArgumentException;

class BakeryProductVariant extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'sku',
        'weight_grams',
        'regular_price_toman',
        'sale_price_toman',
        'stock_quantity',
        'low_stock_threshold',
        'is_default',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'weight_grams' => 'integer',
        'regular_price_toman' => 'integer',
        'sale_price_toman' => 'integer',
        'stock_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'active_reserved_quantity' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $variant): void {
            $variant->public_id ??= (string) Str::ulid();
            self::validatePrices($variant);
        });

        static::updating(function (self $variant): void {
            self::validatePrices($variant);
        });

        static::saved(function (self $variant): void {
            if ($variant->is_default) {
                self::query()
                    ->where('product_id', $variant->product_id)
                    ->whereKeyNot($variant->getKey())
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'public_id';
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(BakeryProduct::class, 'product_id');
    }

    public function inventoryReservations(): HasMany
    {
        return $this->hasMany(InventoryReservation::class, 'variant_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function getCurrentPriceTomanAttribute(): int
    {
        return $this->hasValidSalePrice()
            ? (int) $this->sale_price_toman
            : (int) $this->regular_price_toman;
    }

    public function getReservedQuantityAttribute(): int
    {
        if (array_key_exists('active_reserved_quantity', $this->attributes)) {
            return (int) ($this->attributes['active_reserved_quantity'] ?? 0);
        }

        return (int) $this->inventoryReservations()->active()->sum('quantity');
    }

    public function getAvailableStockQuantityAttribute(): int
    {
        return max(0, (int) $this->stock_quantity - $this->reserved_quantity);
    }

    public function getAvailableAttribute(): bool
    {
        return $this->is_active && $this->available_stock_quantity > 0;
    }

    public function getLowStockAttribute(): bool
    {
        return $this->available_stock_quantity > 0
            && $this->available_stock_quantity <= $this->low_stock_threshold;
    }

    public function hasValidSalePrice(): bool
    {
        return $this->sale_price_toman !== null
            && $this->sale_price_toman > 0
            && $this->sale_price_toman < $this->regular_price_toman;
    }

    private static function validatePrices(self $variant): void
    {
        if ($variant->regular_price_toman < 1) {
            throw new InvalidArgumentException('قیمت عادی Variant باید بیشتر از صفر باشد.');
        }

        if (
            $variant->sale_price_toman !== null
            && $variant->sale_price_toman >= $variant->regular_price_toman
        ) {
            throw new InvalidArgumentException('قیمت فروش باید کمتر از قیمت عادی باشد.');
        }
    }
}
