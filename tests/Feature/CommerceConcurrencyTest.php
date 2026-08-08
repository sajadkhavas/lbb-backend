<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\CommerceException;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\Commerce\InventoryLedgerService;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CommerceConcurrencyTest extends TestCase
{
    use DatabaseMigrations;

    public function test_last_unit_cannot_be_reserved_twice_and_mysql_gate_uses_two_real_processes(): void
    {
        [$order, $variant] = $this->fixture();
        if (DB::getDriverName() !== 'mysql') {
            app(InventoryLedgerService::class)->reserveForOrder($order, $variant, 1, now()->addMinutes(10), 'test', 'sqlite-sequential-1');
            try {
                app(InventoryLedgerService::class)->reserveForOrder($order, $variant, 1, now()->addMinutes(10), 'test', 'sqlite-sequential-2');
                $this->fail('Second reservation should not succeed.');
            } catch (CommerceException $exception) {
                $this->assertSame('commerce_out_of_stock', $exception->commerceCode->value);
            }
            $this->assertDatabaseCount('inventory_reservations', 1);
            return;
        }

        $barrier = storage_path('framework/testing/commerce-race-'.uniqid('', true));
        @unlink($barrier);
        $probe = base_path('tests/Support/commerce_reservation_probe.php');
        $commands = [
            [PHP_BINARY, $probe, $order->public_id, $variant->public_id, 'mysql-race-a', $barrier],
            [PHP_BINARY, $probe, $order->public_id, $variant->public_id, 'mysql-race-b', $barrier],
        ];
        $processes = [];
        foreach ($commands as $command) {
            $spec = [0 => ['pipe', 'r'], 1 => ['pipe', 'w'], 2 => ['pipe', 'w']];
            $process = proc_open($command, $spec, $pipes, base_path());
            $this->assertIsResource($process);
            fclose($pipes[0]);
            $processes[] = [$process, $pipes];
        }
        file_put_contents($barrier, 'go');
        $outputs = [];
        foreach ($processes as [$process, $pipes]) {
            $stdout = stream_get_contents($pipes[1]);
            $stderr = stream_get_contents($pipes[2]);
            fclose($pipes[1]); fclose($pipes[2]);
            $exit = proc_close($process);
            $this->assertSame(0, $exit, $stderr);
            $outputs[] = trim($stdout);
        }
        @unlink($barrier);

        $reserved = count(array_filter($outputs, fn (string $value): bool => $value === 'reserved'));
        $rejected = count(array_filter($outputs, fn (string $value): bool => str_contains($value, 'موجودی کافی')));
        $this->assertSame(1, $reserved, implode(' | ', $outputs));
        $this->assertSame(1, $rejected, implode(' | ', $outputs));
        $this->assertDatabaseCount('inventory_reservations', 1);
        $this->assertDatabaseCount('inventory_ledger_entries', 2); // opening balance + reservation
    }

    private function fixture(): array
    {
        $customer = Customer::query()->create(['mobile' => '09123334444', 'mobile_verified_at' => now(), 'is_active' => true]);
        $category = Category::query()->create(['name' => 'Race', 'slug' => 'race', 'is_active' => true]);
        $product = Product::query()->create(['category_id' => $category->id, 'name' => 'Race Product', 'slug' => 'race-product', 'product_code' => 'RACE', 'is_active' => true]);
        $variant = ProductVariant::query()->create([
            'product_id' => $product->id, 'name' => 'Race Variant', 'sku' => 'RACE-1', 'regular_price_toman' => 1000,
            'stock_quantity' => 1, 'low_stock_threshold' => 0, 'is_default' => true, 'is_active' => true,
        ]);
        $order = Order::query()->create([
            'customer_id' => $customer->id, 'order_number' => 'LBB-RACE-1', 'idempotency_key' => 'race-order-key-0001',
            'request_hash' => hash('sha256', 'race'), 'status' => OrderStatus::AwaitingPayment, 'payment_status' => PaymentStatus::Unpaid,
            'delivery_method' => 'pickup', 'subtotal_toman' => 1000, 'delivery_fee_toman' => 0, 'packaging_fee_toman' => 0,
            'discount_total_toman' => 0, 'grand_total_toman' => 1000, 'currency' => 'TOMAN', 'item_count' => 1,
            'preparation_time_days' => 0, 'preparation_max_days' => 0, 'customer_name' => 'Race', 'customer_mobile' => '09123334444',
            'reservation_expires_at' => now()->addMinutes(20), 'placed_at' => now(),
        ]);
        return [$order, $variant];
    }
}
