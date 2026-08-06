<?php

namespace App\Http\Controllers\Api;

use App\Enums\InventoryReservationStatus;
use App\Http\Controllers\Controller;
use App\Http\Resources\BakeryCategoryResource;
use App\Http\Resources\BakeryProductResource;
use App\Models\BakeryCategory;
use App\Models\BakeryProduct;
use App\Support\ApiResponse;
use App\Support\Pagination;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    public function products(Request $request): JsonResponse
    {
        $filters = $request->validate([
            'category' => ['nullable', 'string', 'max:140'],
            'search' => ['nullable', 'string', 'max:100'],
            'featured' => ['nullable', 'boolean'],
            'requiresCooling' => ['nullable', 'boolean'],
            'inStock' => ['nullable', 'boolean'],
            'sort' => ['nullable', 'in:featured,newest,name,price-asc,price-desc'],
            'page' => ['nullable', 'integer', 'min:1'],
            'perPage' => ['nullable', 'integer', 'min:1', 'max:'.config('winimi.policies.pagination.catalog_max', 48)],
        ]);

        $featured = $request->has('featured')
            ? $request->boolean('featured')
            : false;
        $requiresCooling = $request->has('requiresCooling')
            ? $request->boolean('requiresCooling')
            : null;
        $inStock = $request->has('inStock')
            ? $request->boolean('inStock')
            : false;

        $query = BakeryProduct::query()
            ->active()
            ->with($this->catalogRelations());

        if (! empty($filters['category'])) {
            $query->whereHas(
                'category',
                fn (Builder $category): Builder => $category->where('slug', $filters['category']),
            );
        }

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function (Builder $nested) use ($search): void {
                $nested->where('name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('short_description', 'like', "%{$search}%");
            });
        }

        if ($featured) {
            $query->featured();
        }

        if ($requiresCooling !== null) {
            $query->where('requires_cooling', $requiresCooling);
        }

        if ($inStock) {
            $query->whereHas('activeVariants', function (Builder $variant): void {
                $variant->whereRaw(
                    'stock_quantity > COALESCE((SELECT SUM(quantity) FROM inventory_reservations WHERE inventory_reservations.variant_id = bakery_product_variants.id AND status = ? AND expires_at > ?), 0)',
                    [InventoryReservationStatus::Active->value, now()],
                );
            });
        }

        $this->applySort($query, $filters['sort'] ?? 'featured');

        $paginator = $query->paginate((int) ($filters['perPage'] ?? config(
            'winimi.policies.pagination.catalog_default',
            12,
        )));
        $items = BakeryProductResource::collection($paginator->getCollection())->resolve($request);

        return ApiResponse::success($items, meta: [
            'pagination' => Pagination::meta($paginator),
            'filters' => [
                'category' => $filters['category'] ?? null,
                'search' => $filters['search'] ?? null,
                'featured' => $featured,
                'requiresCooling' => $requiresCooling,
                'inStock' => $inStock,
                'sort' => $filters['sort'] ?? 'featured',
            ],
        ]);
    }

    public function product(string $slug): JsonResponse
    {
        $product = BakeryProduct::query()
            ->active()
            ->with($this->catalogRelations())
            ->where('slug', $slug)
            ->firstOrFail();

        return ApiResponse::success(
            (new BakeryProductResource($product))->resolve(),
        );
    }

    public function categories(Request $request): JsonResponse
    {
        $categories = BakeryCategory::query()
            ->active()
            ->withCount([
                'products' => fn (Builder $products): Builder => $products->active(),
            ])
            ->ordered()
            ->get();

        return ApiResponse::success(
            BakeryCategoryResource::collection($categories)->resolve($request),
        );
    }

    private function catalogRelations(): array
    {
        return [
            'category',
            'media',
            'activeVariants' => fn (HasMany $variants) => $variants->withSum([
                'inventoryReservations as active_reserved_quantity' => fn (Builder $reservations): Builder => $reservations->active(),
            ], 'quantity'),
        ];
    }

    private function applySort(Builder $query, string $sort): void
    {
        $currentPriceSql = <<<'SQL'
            (SELECT MIN(
                CASE
                    WHEN sale_price_toman IS NOT NULL
                        AND sale_price_toman > 0
                        AND sale_price_toman < regular_price_toman
                    THEN sale_price_toman
                    ELSE regular_price_toman
                END
            )
            FROM bakery_product_variants
            WHERE bakery_product_variants.product_id = bakery_products.id
                AND bakery_product_variants.is_active = 1)
        SQL;

        match ($sort) {
            'newest' => $query->latest('bakery_products.created_at'),
            'name' => $query->orderBy('bakery_products.name'),
            'price-asc' => $query->orderByRaw("{$currentPriceSql} ASC"),
            'price-desc' => $query->orderByRaw("{$currentPriceSql} DESC"),
            default => $query->ordered(),
        };
    }
}
