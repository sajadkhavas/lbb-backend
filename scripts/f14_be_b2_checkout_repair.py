from pathlib import Path


def write(path: str, content: str) -> None:
    Path(path).write_text(content.rstrip() + '\n', encoding='utf-8')


write('app/Enums/DeliveryMethod.php', r'''<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case Standard = 'standard';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'ارسال معمولی',
            self::Pickup => 'تحویل حضوری',
        };
    }

    public function requiresAddress(): bool
    {
        return $this !== self::Pickup;
    }
}
''')

write('app/Models/DeliveryZone.php', r'''<?php

namespace App\Models;

use App\Enums\DeliveryMethod;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class DeliveryZone extends Model
{
    protected $fillable = [
        'name',
        'province',
        'city',
        'standard_enabled',
        'pickup_enabled',
        'standard_fee_toman',
        'pickup_fee_toman',
        'packaging_fee_toman',
        'minimum_order_toman',
        'free_delivery_threshold_toman',
        'preparation_min_days',
        'preparation_max_days',
        'daily_order_limit',
        'priority',
        'is_active',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $zone): void {
            $zone->public_id ??= (string) Str::ulid();
        });
    }

    protected function casts(): array
    {
        return [
            'standard_enabled' => 'boolean',
            'pickup_enabled' => 'boolean',
            'standard_fee_toman' => 'integer',
            'pickup_fee_toman' => 'integer',
            'packaging_fee_toman' => 'integer',
            'minimum_order_toman' => 'integer',
            'free_delivery_threshold_toman' => 'integer',
            'preparation_min_days' => 'integer',
            'preparation_max_days' => 'integer',
            'daily_order_limit' => 'integer',
            'priority' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function methodEnabled(DeliveryMethod $method): bool
    {
        return (bool) $this->getAttribute("{$method->value}_enabled");
    }

    public function feeFor(DeliveryMethod $method, int $subtotalToman): int
    {
        $threshold = $this->free_delivery_threshold_toman;
        if ($threshold !== null && $subtotalToman >= $threshold && $method !== DeliveryMethod::Pickup) {
            return 0;
        }

        return (int) $this->getAttribute("{$method->value}_fee_toman");
    }
}
''')

write('app/Services/Store/DeliveryConfigurationService.php', r'''<?php

namespace App\Services\Store;

use App\Enums\DeliveryMethod;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

final class DeliveryConfigurationService
{
    /** @return array{zone: ?DeliveryZone, fee_toman: int, packaging_fee_toman: int, preparation_min_days: int, preparation_max_days: int} */
    public function quote(
        DeliveryMethod $method,
        ?string $province,
        ?string $city,
        int $subtotalToman,
    ): array {
        if (! StoreSetting::value('orders.accepting_orders', true)) {
            throw ValidationException::withMessages(['checkout' => ['پذیرش سفارش جدید موقتاً متوقف شده است.']]);
        }

        $globalMinimum = max(0, (int) StoreSetting::value('orders.minimum_total_toman', 0));
        if ($subtotalToman < $globalMinimum) {
            throw ValidationException::withMessages(['items' => ["حداقل مبلغ سفارش {$globalMinimum} تومان است."]]);
        }

        $zone = $this->resolve($province, $city);
        if (! $zone) {
            return $this->fallbackQuote($method);
        }

        if (! $zone->methodEnabled($method)) {
            throw ValidationException::withMessages(['deliveryMethod' => ['روش تحویل انتخاب‌شده در منطقه مقصد فعال نیست.']]);
        }

        if ($zone->minimum_order_toman !== null && $subtotalToman < $zone->minimum_order_toman) {
            throw ValidationException::withMessages(['items' => ["حداقل مبلغ سفارش در این منطقه {$zone->minimum_order_toman} تومان است."]]);
        }

        if ($zone->daily_order_limit !== null) {
            $todayCount = Order::query()
                ->where('delivery_zone_id', $zone->getKey())
                ->whereDate('placed_at', today())
                ->count();
            if ($todayCount >= $zone->daily_order_limit) {
                throw ValidationException::withMessages(['deliveryMethod' => ['ظرفیت سفارش امروز برای این منطقه تکمیل شده است.']]);
            }
        }

        return [
            'zone' => $zone,
            'fee_toman' => $zone->feeFor($method, $subtotalToman),
            'packaging_fee_toman' => (int) $zone->packaging_fee_toman,
            'preparation_min_days' => (int) $zone->preparation_min_days,
            'preparation_max_days' => max((int) $zone->preparation_min_days, (int) $zone->preparation_max_days),
        ];
    }

    /** @return array<int, array{method: string, label: string, enabled: bool, feeToman: int}> */
    public function options(?string $province, ?string $city, int $subtotalToman): array
    {
        $zone = $this->resolve($province, $city);

        return collect(DeliveryMethod::cases())->map(function (DeliveryMethod $method) use ($zone, $subtotalToman): array {
            if ($zone) {
                return [
                    'method' => $method->value,
                    'label' => $method->label(),
                    'enabled' => $zone->methodEnabled($method),
                    'feeToman' => $zone->feeFor($method, $subtotalToman),
                ];
            }

            $fallback = config("lbb.checkout.delivery_methods.{$method->value}", []);
            return [
                'method' => $method->value,
                'label' => $method->label(),
                'enabled' => (bool) ($fallback['enabled'] ?? false),
                'feeToman' => (int) ($fallback['fee_toman'] ?? 0),
            ];
        })->values()->all();
    }

    public function resolve(?string $province, ?string $city): ?DeliveryZone
    {
        $province = $this->normalize($province);
        $city = $this->normalize($city);

        return DeliveryZone::query()
            ->active()
            ->where(function (Builder $query) use ($province): void {
                $query->whereNull('province');
                if ($province !== null) {
                    $query->orWhere('province', $province);
                }
            })
            ->where(function (Builder $query) use ($city): void {
                $query->whereNull('city');
                if ($city !== null) {
                    $query->orWhere('city', $city);
                }
            })
            ->orderByRaw('CASE WHEN city IS NULL THEN 1 ELSE 0 END')
            ->orderByRaw('CASE WHEN province IS NULL THEN 1 ELSE 0 END')
            ->orderBy('priority')
            ->first();
    }

    /** @return array{zone: null, fee_toman: int, packaging_fee_toman: int, preparation_min_days: int, preparation_max_days: int} */
    private function fallbackQuote(DeliveryMethod $method): array
    {
        $delivery = config("lbb.checkout.delivery_methods.{$method->value}", []);
        if (! ($delivery['enabled'] ?? false)) {
            throw ValidationException::withMessages(['deliveryMethod' => ['روش تحویل انتخاب‌شده فعال نیست.']]);
        }

        return [
            'zone' => null,
            'fee_toman' => (int) ($delivery['fee_toman'] ?? 0),
            'packaging_fee_toman' => (int) config('lbb.checkout.packaging_fee_toman', 0),
            'preparation_min_days' => 0,
            'preparation_max_days' => 0,
        ];
    }

    private function normalize(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
''')

write('app/Http/Controllers/Api/DeliveryController.php', r'''<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Store\DeliveryConfigurationService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function options(Request $request, DeliveryConfigurationService $delivery): JsonResponse
    {
        $validated = $request->validate([
            'province' => ['nullable', 'string', 'max:100'],
            'city' => ['nullable', 'string', 'max:100'],
            'subtotalToman' => ['nullable', 'integer', 'min:0'],
        ]);
        $subtotal = (int) ($validated['subtotalToman'] ?? 0);
        $zone = $delivery->resolve($validated['province'] ?? null, $validated['city'] ?? null);

        return ApiResponse::success([
            'zone' => $zone ? [
                'id' => $zone->public_id,
                'name' => $zone->name,
                'minimumOrderToman' => $zone->minimum_order_toman,
                'freeDeliveryThresholdToman' => $zone->free_delivery_threshold_toman,
                'packagingFeeToman' => $zone->packaging_fee_toman,
                'processing' => [
                    'minDays' => $zone->preparation_min_days,
                    'maxDays' => max($zone->preparation_min_days, $zone->preparation_max_days),
                ],
            ] : null,
            'methods' => $delivery->options(
                $validated['province'] ?? null,
                $validated['city'] ?? null,
                $subtotal,
            ),
        ]);
    }
}
''')

write('app/Services/Orders/CheckoutService.php', r'''<?php

namespace App\Services\Orders;

use App\Enums\DeliveryMethod;
use App\Enums\InventoryReservationStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\IdempotencyConflict;
use App\Exceptions\InventoryUnavailable;
use App\Models\Customer;
use App\Models\CustomerAddress;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ProductVariant;
use App\Services\Store\DeliveryConfigurationService;
use App\Support\IranianMobile;
use Illuminate\Database\QueryException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use JsonException;

final class CheckoutService
{
    public function __construct(private readonly DeliveryConfigurationService $delivery) {}

    /** @return array{order: Order, replayed: bool} @throws JsonException */
    public function create(Customer $customer, array $payload, string $idempotencyKey): array
    {
        $payload = $this->resolveCustomerPayload($customer, $payload);
        $canonical = $this->canonicalize($payload);
        $requestHash = hash('sha256', json_encode($canonical, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        $existing = $this->findExisting($customer, $idempotencyKey);
        if ($existing) {
            return $this->replay($existing, $requestHash);
        }

        try {
            return DB::transaction(function () use ($customer, $canonical, $idempotencyKey, $requestHash): array {
                $existing = Order::query()->ownedBy($customer)->where('idempotency_key', $idempotencyKey)->lockForUpdate()->first();
                if ($existing) {
                    return $this->replay($existing, $requestHash);
                }

                return [
                    'order' => $this->createLocked($customer, $canonical, $idempotencyKey, $requestHash),
                    'replayed' => false,
                ];
            }, 3);
        } catch (QueryException $exception) {
            if (! $this->isUniqueConstraintViolation($exception)) {
                throw $exception;
            }
            $existing = $this->findExisting($customer, $idempotencyKey);
            if (! $existing) {
                throw $exception;
            }
            return $this->replay($existing, $requestHash);
        }
    }

    private function createLocked(Customer $customer, array $payload, string $idempotencyKey, string $requestHash): Order
    {
        $items = collect($payload['items']);
        $variantIds = $items->pluck('variantId')->sort()->values();
        $variants = ProductVariant::query()
            ->whereIn('public_id', $variantIds)
            ->where('is_active', true)
            ->with(['product.category'])
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('public_id');

        if ($variants->count() !== $variantIds->count()) {
            throw ValidationException::withMessages(['items' => ['یک یا چند محصول دیگر قابل سفارش نیستند.']]);
        }

        $deliveryMethod = DeliveryMethod::from($payload['deliveryMethod']);
        if ($deliveryMethod->requiresAddress()) {
            foreach (['province', 'city', 'address'] as $field) {
                if (trim((string) ($payload['customer'][$field] ?? '')) === '') {
                    throw ValidationException::withMessages(["customer.{$field}" => ['این مقدار برای ارسال سفارش الزامی است.']]);
                }
            }
        }

        $itemSnapshots = [];
        $subtotal = 0;
        foreach ($items as $item) {
            /** @var ProductVariant $variant */
            $variant = $variants->get($item['variantId']);
            $product = $variant->product;
            if (! $product || ! $product->is_active || ! $product->category?->is_active) {
                throw ValidationException::withMessages(['items' => ['یک یا چند محصول دیگر قابل سفارش نیستند.']]);
            }

            $reserved = (int) InventoryReservation::query()->where('variant_id', $variant->getKey())->active()->sum('quantity');
            $available = max(0, (int) $variant->stock_quantity - $reserved);
            $quantity = (int) $item['quantity'];
            if ($quantity > $available) {
                throw new InventoryUnavailable($variant->public_id, $variant->name, $quantity, $available);
            }

            $unitPrice = $variant->current_price_toman;
            $lineTotal = $unitPrice * $quantity;
            $subtotal += $lineTotal;
            $itemSnapshots[] = [
                'product_id' => $product->getKey(),
                'variant_id' => $variant->getKey(),
                'product_public_id' => $product->public_id,
                'variant_public_id' => $variant->public_id,
                'product_name' => $product->name,
                'variant_name' => $variant->name,
                'product_code' => $product->product_code,
                'sku' => $variant->sku,
                'unit_price_toman' => $unitPrice,
                'quantity' => $quantity,
                'line_total_toman' => $lineTotal,
            ];
        }

        $quote = $this->delivery->quote(
            $deliveryMethod,
            $payload['customer']['province'] ?? null,
            $payload['customer']['city'] ?? null,
            $subtotal,
        );
        $processingMinDays = (int) $quote['preparation_min_days'];
        $processingMaxDays = max($processingMinDays, (int) $quote['preparation_max_days']);
        $reservationExpiresAt = now()->addMinutes(max(1, (int) config('lbb.checkout.reservation_minutes', 20)));

        $order = Order::query()->create([
            'customer_id' => $customer->getKey(),
            'order_number' => $this->nextOrderNumber(),
            'idempotency_key' => $idempotencyKey,
            'request_hash' => $requestHash,
            'status' => OrderStatus::AwaitingPayment,
            'payment_status' => PaymentStatus::Unpaid,
            'delivery_method' => $deliveryMethod,
            'delivery_zone_id' => $quote['zone']?->getKey(),
            'subtotal_toman' => $subtotal,
            'delivery_fee_toman' => $quote['fee_toman'],
            'packaging_fee_toman' => $quote['packaging_fee_toman'],
            'discount_total_toman' => 0,
            'grand_total_toman' => $subtotal + $quote['fee_toman'] + $quote['packaging_fee_toman'],
            'item_count' => $items->sum('quantity'),
            'preparation_time_days' => $processingMinDays,
            'preparation_max_days' => $processingMaxDays,
            'customer_name' => trim($payload['customer']['fullName']),
            'customer_mobile' => IranianMobile::normalize($payload['customer']['mobile']),
            'province' => $deliveryMethod->requiresAddress() ? trim($payload['customer']['province']) : null,
            'city' => $deliveryMethod->requiresAddress() ? trim($payload['customer']['city']) : null,
            'address' => $deliveryMethod->requiresAddress() ? trim($payload['customer']['address']) : null,
            'postal_code' => $deliveryMethod->requiresAddress() ? $this->nullableTrim($payload['customer']['postalCode'] ?? null) : null,
            'notes' => $this->nullableTrim($payload['customer']['notes'] ?? null),
            'reservation_expires_at' => $reservationExpiresAt,
            'placed_at' => now(),
        ]);

        $order->items()->createMany($itemSnapshots);
        foreach ($itemSnapshots as $snapshot) {
            $order->reservations()->create([
                'variant_id' => $snapshot['variant_id'],
                'quantity' => $snapshot['quantity'],
                'status' => InventoryReservationStatus::Active,
                'expires_at' => $reservationExpiresAt,
            ]);
        }

        OrderStatusHistory::query()->create([
            'order_id' => $order->getKey(),
            'from_status' => null,
            'to_status' => OrderStatus::AwaitingPayment,
            'actor_type' => 'customer',
            'actor_id' => $customer->getKey(),
            'note' => 'سفارش از Checkout ثبت شد و موجودی به‌صورت موقت رزرو شد.',
            'created_at' => now(),
        ]);

        return $this->loadOrder($order);
    }

    private function resolveCustomerPayload(Customer $customer, array $payload): array
    {
        $addressId = trim((string) ($payload['addressId'] ?? ''));
        if ($addressId === '') {
            return $payload;
        }
        $address = CustomerAddress::query()->ownedBy($customer)->where('public_id', $addressId)->where('is_active', true)->first();
        if (! $address) {
            throw ValidationException::withMessages(['addressId' => ['آدرس انتخاب‌شده معتبر یا متعلق به این حساب نیست.']]);
        }
        $payload['customer'] = [
            'fullName' => $address->recipient_name,
            'mobile' => $address->mobile,
            'province' => $address->province,
            'city' => $address->city,
            'address' => $address->address_line,
            'postalCode' => $address->postal_code,
            'notes' => $payload['customer']['notes'] ?? null,
        ];
        return $payload;
    }

    private function canonicalize(array $payload): array
    {
        $items = collect($payload['items'])
            ->map(fn (array $item): array => ['variantId' => trim($item['variantId']), 'quantity' => (int) $item['quantity']])
            ->groupBy('variantId')
            ->map(fn (Collection $group, string $variantId): array => ['variantId' => $variantId, 'quantity' => $group->sum('quantity')])
            ->sortBy('variantId')->values()->all();

        return [
            'customer' => [
                'fullName' => trim($payload['customer']['fullName']),
                'mobile' => IranianMobile::normalize($payload['customer']['mobile']),
                'province' => trim((string) ($payload['customer']['province'] ?? '')),
                'city' => trim((string) ($payload['customer']['city'] ?? '')),
                'address' => trim((string) ($payload['customer']['address'] ?? '')),
                'postalCode' => trim((string) ($payload['customer']['postalCode'] ?? '')),
                'notes' => trim((string) ($payload['customer']['notes'] ?? '')),
            ],
            'deliveryMethod' => $payload['deliveryMethod'],
            'items' => $items,
        ];
    }

    private function findExisting(Customer $customer, string $idempotencyKey): ?Order
    {
        return Order::query()->ownedBy($customer)->where('idempotency_key', $idempotencyKey)->first();
    }

    private function replay(Order $order, string $requestHash): array
    {
        if (! hash_equals($order->request_hash, $requestHash)) {
            throw new IdempotencyConflict;
        }
        return ['order' => $this->loadOrder($order), 'replayed' => true];
    }

    private function loadOrder(Order $order): Order
    {
        return $order->load(['items', 'reservations', 'deliveryZone']);
    }

    private function nextOrderNumber(): string
    {
        return 'LBB-'.now()->format('ymd').'-'.Str::upper(Str::random(8));
    }

    private function nullableTrim(mixed $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        return in_array((string) $exception->getCode(), ['23000', '23505'], true);
    }
}
''')

print('f14_be_b2_checkout_repair=complete')
