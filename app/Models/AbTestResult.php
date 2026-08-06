<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbTestResult extends Model
{
    protected $fillable = [
        'ab_test_id', 'variant_id', 'session_id', 'event', 'page_url',
    ];

    public function test()
    {
        return $this->belongsTo(AbTest::class);
    }

    public function variant()
    {
        return $this->belongsTo(AbTestVariant::class, 'variant_id');
    }
}
