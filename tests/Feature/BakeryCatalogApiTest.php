<?php

namespace Tests\Feature;

use App\Models\BakeryCategory;
use App\Models\BakeryProduct;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BakeryCatalogApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_contract_is_implemented(): void
    {
        $this->getJson('/api/system/contracts')
            ->assertOk()
            ->assertJsonPath('data.contracts.catalog.status', 'implemented')
            ->assertJsonPath('data.contracts.catalog.source', 'bakery-catalog');
    }

    public function test_categories_only_expose_active_records_with_active_product_counts(): void
    {
        $category = BakeryCategory::create([
            'name' => 'کوکی‌ها',
            'slug' => 'cookies',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        BakeryCategory::create([
            'name' => 'غیرفعال',
            'slug' => 'inactive-category',
            'is_active' => false,
        ]);

        $product = $this->createProduct($category, [
            'name' => 'کوکی شکلاتی',
            'slug' => 'chocolate-cookie',
            'product_code' => 'WIN-COOKIE-001',
        ]);
        $this->createVariant($product, [
            'name' => 'بسته ۶ عددی',
            'sku' => 'WIN-COOKIE-001-6',
        ]);

        $this->getJson('/api/catalog/categories')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $category->public_id)
            ->assertJsonPath('data.0.slug', 'cookies')
            ->assertJsonPath('data.0.productCount', 1);
    }

    public function test_product_listing_calculates_variant_price_stock_and_verification_boundaries(): void
    {
        $category = BakeryCategory::create([
            'name' => 'کوکی‌ها',
            'slug' => 'cookies',
            'is_active' => true,
        ]);

        $product = $this->createProduct($category, [
            'name' => 'کوکی شکلاتی گردویی',
            'slug' => 'walnut-chocolate-cookie',
            'product_code' => 'WIN-COOKIE-002',
            'description' => 'توضیح داخلی که تا تأیید نباید منتشر شود.',
            'ingredients' => ['آرد', 'گردو'],
            'allergens' => ['گلوتن', 'گردو'],
            'content_verified' => false,
            'requires_cooling' => false,
            'is_featured' => true,
        ]);

        $firstVariant = $this->createVariant($product, [
            'name' => 'بسته ۶ عددی',
            'sku' => 'WIN-COOKIE-002-6',
            'regular_price_toman' => 150000,
            'sale_price_toman' => 120000,
            'stock_quantity' => 3,
            'is_default' => true,
        ]);
        $this->createVariant($product, [
            'name' => 'بسته ۱۲ عددی',
            'sku' => 'WIN-COOKIE-002-12',
            'regular_price_toman' => 240000,
            'stock_quantity' => 2,
        ]);

        $this->getJson('/api/catalog/products?category=cookies&featured=1&inStock=1')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $product->public_id)
            ->assertJsonPath('data.0.priceToman', 120000)
            ->assertJsonPath('data.0.regularPriceToman', 150000)
            ->assertJsonPath('data.0.salePriceToman', 120000)
            ->assertJsonPath('data.0.stock', 5)
            ->assertJsonPath('data.0.available', true)
            ->assertJsonPath('data.0.inventoryVerified', true)
            ->assertJsonPath('data.0.contentVerified', false)
            ->assertJsonPath('data.0.longDescription', null)
            ->assertJsonPath('data.0.ingredients', [])
            ->assertJsonPath('data.0.allergens', [])
            ->assertJsonPath('data.0.variants.0.id', $firstVariant->public_id)
            ->assertJsonPath('meta.pagination.total', 1);
    }

    public function test_product_detail_exposes_verified_content_and_active_variants_only(): void
    {
        $category = BakeryCategory::create([
            'name' => 'کیک و دسر',
            'slug' => 'cakes',
            'is_active' => true,
        ]);

        $product = $this->createProduct($category, [
            'name' => 'چیزکیک',
            'slug' => 'cheesecake',
            'product_code' => 'WIN-CAKE-001',
            'description' => 'توضیح تأییدشده محصول',
            'ingredients' => ['پنیر خامه‌ای', 'بیسکویت'],
            'allergens' => ['لبنیات', 'گلوتن'],
            'shelf_life' => 'طبق برچسب بسته‌بندی',
            'storage_instructions' => 'در یخچال نگهداری شود.',
            'content_verified' => true,
            'requires_cooling' => true,
        ]);

        $this->createVariant($product, [
            'name' => 'تک‌نفره',
            'sku' => 'WIN-CAKE-001-S',
            'regular_price_toman' => 180000,
            'stock_quantity' => 4,
        ]);
        $this->createVariant($product, [
            'name' => 'غیرفعال',
            'sku' => 'WIN-CAKE-001-X',
            'regular_price_toman' => 1,
            'stock_quantity' => 99,
            'is_active' => false,
        ]);

        $this->getJson('/api/catalog/products/cheesecake')
            ->assertOk()
            ->assertJsonPath('data.longDescription', 'توضیح تأییدشده محصول')
            ->assertJsonPath('data.ingredients.0', 'پنیر خامه‌ای')
            ->assertJsonPath('data.allergens.0', 'لبنیات')
            ->assertJsonPath('data.requiresCooling', true)
            ->assertJsonPath('data.shippingScope', 'tehran-karaj')
            ->assertJsonPath('data.stock', 4)
            ->assertJsonCount(1, 'data.variants');
    }

    public function test_inactive_and_out_of_stock_products_are_filtered_correctly(): void
    {
        $category = BakeryCategory::create([
            'name' => 'باکس هدیه',
            'slug' => 'gift',
            'is_active' => true,
        ]);

        $outOfStock = $this->createProduct($category, [
            'name' => 'باکس بدون موجودی',
            'slug' => 'empty-box',
            'product_code' => 'WIN-GIFT-EMPTY',
        ]);
        $this->createVariant($outOfStock, [
            'name' => 'استاندارد',
            'sku' => 'WIN-GIFT-EMPTY-STD',
            'stock_quantity' => 0,
        ]);

        $inactive = $this->createProduct($category, [
            'name' => 'محصول غیرفعال',
            'slug' => 'inactive-product',
            'product_code' => 'WIN-GIFT-INACTIVE',
            'is_active' => false,
        ]);
        $this->createVariant($inactive, [
            'name' => 'استاندارد',
            'sku' => 'WIN-GIFT-INACTIVE-STD',
            'stock_quantity' => 10,
        ]);

        $this->getJson('/api/catalog/products?inStock=1')
            ->assertOk()
            ->assertJsonCount(0, 'data');

        $this->getJson('/api/catalog/products/inactive-product')
            ->assertNotFound();
    }

    private function createProduct(BakeryCategory $category, array $attributes = []): BakeryProduct
    {
        return BakeryProduct::create([
            'category_id' => $category->id,
            'name' => 'محصول آزمایشی',
            'slug' => 'test-product-'.uniqid(),
            'product_code' => 'WIN-TEST-'.uniqid(),
            'short_description' => 'توضیح کوتاه محصول',
            'is_active' => true,
            ...$attributes,
        ]);
    }

    private function createVariant(BakeryProduct $product, array $attributes = [])
    {
        return $product->variants()->create([
            'name' => 'انتخاب استاندارد',
            'sku' => 'WIN-SKU-'.uniqid(),
            'regular_price_toman' => 100000,
            'stock_quantity' => 5,
            'low_stock_threshold' => 2,
            'is_active' => true,
            ...$attributes,
        ]);
    }
}
