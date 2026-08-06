<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RfqItem extends Model
{
    protected $fillable = [
        'rfq_request_id', 'product_id', 'product_name',
        'product_model', 'quantity', 'notes',
    ];

    public function rfqRequest(): BelongsTo { return $this->belongsTo(RfqRequest::class); }
    public function product(): BelongsTo    { return $this->belongsTo(Product::class); }
}
