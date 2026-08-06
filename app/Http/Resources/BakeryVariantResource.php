<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BakeryVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'name' => $this->name,
            'productCode' => $this->sku,
            'weightGrams' => $this->weight_grams,
            'weight' => $this->weight_grams
                ? number_format($this->weight_grams).' گرم'
                : null,
            'priceToman' => $this->current_price_toman,
            'regularPriceToman' => $this->regular_price_toman,
            'salePriceToman' => $this->hasValidSalePrice()
                ? $this->sale_price_toman
                : null,
            'stock' => $this->available_stock_quantity,
            'available' => $this->available,
            'lowStock' => $this->low_stock,
            'isDefault' => $this->is_default,
        ];
    }
}
