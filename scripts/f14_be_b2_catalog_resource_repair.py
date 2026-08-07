from pathlib import Path


def write(path: str, content: str) -> None:
    Path(path).write_text(content.rstrip() + '\n', encoding='utf-8')


write('app/Http/Resources/ProductVariantResource.php', r'''<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductVariantResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'name' => $this->name,
            'sku' => $this->sku,
            'priceToman' => $this->current_price_toman,
            'regularPriceToman' => $this->regular_price_toman,
            'salePriceToman' => $this->hasValidSalePrice() ? $this->sale_price_toman : null,
            'stock' => $this->available_stock_quantity,
            'available' => $this->available,
            'lowStock' => $this->low_stock,
            'isDefault' => (bool) $this->is_default,
        ];
    }
}
''')

write('app/Http/Resources/ProductResource.php', r'''<?php

namespace App\Http\Resources;

use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Collection<int, ProductVariant> $variants */
        $variants = $this->resource->relationLoaded('activeVariants') ? $this->activeVariants : collect();
        $defaultVariant = $variants->firstWhere('is_default', true) ?? $variants->first();
        $priceToman = $variants->isEmpty() ? null : $variants->min(fn ($variant): int => $variant->current_price_toman);
        $stock = $variants->sum('available_stock_quantity');

        return [
            'id' => $this->public_id,
            'slug' => $this->slug,
            'name' => $this->name,
            'productCode' => $this->product_code,
            'shortDescription' => $this->short_description,
            'longDescription' => $this->description,
            'category' => $this->category?->name,
            'categorySlug' => $this->category?->slug,
            'categoryData' => $this->whenLoaded('category', fn (): CategoryResource => new CategoryResource($this->category)),
            'priceToman' => $priceToman,
            'regularPriceToman' => $defaultVariant?->regular_price_toman,
            'salePriceToman' => $defaultVariant?->hasValidSalePrice() ? $defaultVariant->sale_price_toman : null,
            'stock' => $stock,
            'available' => $stock > 0,
            'badges' => array_values(array_filter([$this->is_featured ? 'ویژه' : null])),
            'images' => $this->catalogImages(),
            'isFeatured' => (bool) $this->is_featured,
            'contentVerified' => (bool) $this->content_verified,
            'mediaVerified' => (bool) $this->media_verified,
            'inventoryVerified' => true,
            'variants' => ProductVariantResource::collection($variants),
            'seo' => [
                'title' => $this->meta_title ?: $this->name,
                'description' => $this->meta_description ?: $this->short_description,
            ],
            'updatedAt' => $this->updated_at?->toISOString(),
        ];
    }

    private function catalogImages(): array
    {
        return $this->getMedia('catalog-main')
            ->concat($this->getMedia('catalog-gallery'))
            ->map(fn ($media): array => [
                'url' => $media->getFullUrl(),
                'alt' => $media->getCustomProperty('alt', $this->name),
                'verified' => (bool) $this->media_verified,
            ])
            ->values()
            ->all();
    }
}
''')

write('app/Http/Resources/OrderResource.php', r'''<?php

namespace App\Http\Resources;

use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'number' => $this->order_number,
            'status' => $this->status->value,
            'statusLabel' => $this->status->label(),
            'paymentStatus' => $this->payment_status->value,
            'paymentStatusLabel' => $this->payment_status->label(),
            'delivery' => [
                'method' => $this->delivery_method->value,
                'methodLabel' => $this->delivery_method->label(),
                'feeToman' => $this->delivery_fee_toman,
                'zone' => $this->resource->relationLoaded('deliveryZone') && $this->deliveryZone
                    ? ['id' => $this->deliveryZone->public_id, 'name' => $this->deliveryZone->name]
                    : null,
            ],
            'totals' => [
                'subtotalToman' => $this->subtotal_toman,
                'deliveryFeeToman' => $this->delivery_fee_toman,
                'packagingFeeToman' => $this->packaging_fee_toman,
                'discountToman' => $this->discount_total_toman,
                'grandTotalToman' => $this->grand_total_toman,
            ],
            'itemCount' => $this->item_count,
            'processing' => [
                'minDays' => $this->preparation_time_days,
                'maxDays' => max($this->preparation_time_days, $this->preparation_max_days),
            ],
            'recipient' => [
                'fullName' => $this->customer_name,
                'mobile' => $this->customer_mobile,
                'province' => $this->province,
                'city' => $this->city,
                'address' => $this->address,
                'postalCode' => $this->postal_code,
                'notes' => $this->notes,
            ],
            'fulfillment' => [
                'trackingCode' => $this->tracking_code,
                'confirmedAt' => $this->confirmed_at?->toIso8601String(),
                'preparingAt' => $this->preparing_at?->toIso8601String(),
                'readyAt' => $this->ready_at?->toIso8601String(),
                'dispatchedAt' => $this->dispatched_at?->toIso8601String(),
                'deliveredAt' => $this->delivered_at?->toIso8601String(),
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items'))->resolve($request),
            'payments' => $this->resource->relationLoaded('paymentAttempts')
                ? PaymentAttemptResource::collection($this->paymentAttempts)->resolve($request)
                : [],
            'timeline' => $this->resource->relationLoaded('statusHistory')
                ? $this->statusHistory->map(fn (OrderStatusHistory $history): array => [
                    'from' => $history->from_status?->value,
                    'to' => $history->to_status->value,
                    'label' => $history->to_status->label(),
                    'createdAt' => $history->created_at?->toIso8601String(),
                ])->values()->all()
                : [],
            'reservationExpiresAt' => $this->reservation_expires_at?->toIso8601String(),
            'canCancel' => $this->canBeCancelledByCustomer(),
            'placedAt' => $this->placed_at?->toIso8601String(),
            'paidAt' => $this->paid_at?->toIso8601String(),
            'cancelledAt' => $this->cancelled_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
''')

print('f14_be_b2_catalog_resource_repair=complete')
