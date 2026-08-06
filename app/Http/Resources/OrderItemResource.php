<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'productId' => $this->product_public_id,
            'variantId' => $this->variant_public_id,
            'productName' => $this->product_name,
            'variantName' => $this->variant_name,
            'productCode' => $this->product_code,
            'sku' => $this->sku,
            'weightGrams' => $this->weight_grams,
            'requiresCooling' => $this->requires_cooling,
            'unitPriceToman' => $this->unit_price_toman,
            'quantity' => $this->quantity,
            'lineTotalToman' => $this->line_total_toman,
        ];
    }
}
