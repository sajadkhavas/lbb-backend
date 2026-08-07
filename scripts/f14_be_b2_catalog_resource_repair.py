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

print('f14_be_b2_catalog_resource_repair=complete')
