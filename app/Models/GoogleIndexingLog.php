<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GoogleIndexingLog extends Model
{
    protected $fillable = [
        'url', 'type', 'status', 'response', 'submitted_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
    ];

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }
}
