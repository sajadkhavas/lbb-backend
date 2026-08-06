<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RfqRequest extends Model
{
    protected $fillable = [
        'reference_number', 'name', 'email', 'phone',
        'company', 'position', 'notes', 'status', 'ip_address',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($m) {
            if (empty($m->reference_number)) {
                $m->reference_number = static::generateReference();
            }
        });
    }

    public static function generateReference(): string
    {
        $year  = now()->format('Y');
        $count = static::whereYear('created_at', $year)->count() + 1;
        return "RFQ-{$year}-" . str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    public function items(): HasMany { return $this->hasMany(RfqItem::class); }
}
