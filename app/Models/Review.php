<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'reviewable_type', 'reviewable_id',
        'reviewer_name', 'reviewer_email',
        'rating', 'title', 'body', 'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function reviewable()
    {
        return $this->morphTo();
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }
}
