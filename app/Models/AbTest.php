<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AbTest extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'status', 'started_at', 'ended_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
    ];

    public function variants()
    {
        return $this->hasMany(AbTestVariant::class);
    }

    public function results()
    {
        return $this->hasMany(AbTestResult::class);
    }
}
