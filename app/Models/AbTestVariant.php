<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbTestVariant extends Model
{
    protected $fillable = [
        'ab_test_id', 'name', 'slug', 'weight', 'config',
    ];

    protected $casts = [
        'config' => 'array',
    ];

    public function test()
    {
        return $this->belongsTo(AbTest::class);
    }

    public function results()
    {
        return $this->hasMany(AbTestResult::class, 'variant_id');
    }
}
