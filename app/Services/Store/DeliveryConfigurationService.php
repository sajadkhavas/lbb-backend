<?php

namespace App\Services\Store;

use App\Enums\DeliveryMethod;
use App\Models\DeliveryZone;
use App\Models\Order;
use App\Models\StoreSetting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;

final class DeliveryConfigurationService
{
    /**
     * @return array{zone: ?DeliveryZone, fee_toman: int, packaging_fee_toman: int, preparation_min_days: int, preparation_max_days: int}
     */
    public function quote(
        DeliveryMethod $method,
        ?string $province,
        ?string $city,
        int $subtotalToman,
        bool $requiresCooling,
    ): array {
        if (! StoreSetting::value('orders.accepting_orders', true)) {
            throw ValidationException::withMessages([
                'checkout' => ['پذیرش سفارش جدید موقتاً متوقف شده است.'],
            ]);
        }

        $globalMinimum = max(0, (int) StoreSetting::value('orders.minimum_total_toman', 0));
        if ($subtotalToman < $globalMinimum) {
            throw ValidationException::withMessages([
                'items' => ["حداقل مبلغ سفارش {$globalMinimum} تومان است."],
            ]);
        }

        if ($requiresCooling && $method === DeliveryMethod::Standard) {
            throw ValidationException::withMessages([
                'deliveryMethod' => ['این سبد به ارسال سرد یا تحویل حضوری نیاز دارد.'],
            ]);
        }

        $zone = $this->resolve($province, $city);
        if (! $zone) {
            return $this->fallbackQuote($method, $subtotalToman);
        }

        if (! $zone->methodEnabled($method)) {
            throw ValidationException::withMessages([
                'deliveryMethod' => ['روش تحویل انتخاب‌شده در منطقه مقصد فعال نیست.'],
            ]);
        }

        if ($zone->minimum_order_toman !== null && $subtotalToman < $zone->minimum_order_toman) {
            throw ValidationException::withMessages([
                'items' => ["حداقل مبلغ سفارش در این منطقه {$zone->minimum_order_toman} تومان است."],
            ]);
        }

        if ($zone->daily_order_limit !== null) {
            $todayCount = Order::query()
                ->where('delivery_zone_id', $zone->getKey())
                ->whereDate('placed_at', today())
                ->count();

            if ($todayCount >= $zone->daily_order_limit) {
                throw ValidationException::withMessages([
                    'deliveryMethod' => ['ظرفیت سفارش امروز برای این منطقه تکمیل شده است.'],
                ]);
            }
        }

        return [
            'zone' => $zone,
            'fee_toman' => $zone->feeFor($method, $subtotalToman),
            'packaging_fee_toman' => (int) $zone->packaging_fee_toman,
            'preparation_min_days' => (int) $zone->preparation_min_days,
            'preparation_max_days' => max(
                (int) $zone->preparation_min_days,
                (int) $zone->preparation_max_days,
            ),
        ];
    }

    /**
     * @return array<int, array{method: string, label: string, enabled: bool, feeToman: int}>
     */
    public function options(?string $province, ?string $city, int $subtotalToman, bool $requiresCooling): array
    {
        $zone = $this->resolve($province, $city);

        return collect(DeliveryMethod::cases())->map(function (DeliveryMethod $method) use (
            $zone,
            $subtotalToman,
            $requiresCooling,
        ): array {
            if ($requiresCooling && $method === DeliveryMethod::Standard) {
                return [
                    'method' => $method->value,
                    'label' => $method->label(),
                    'enabled' => false,
                    'feeToman' => 0,
                ];
            }

            if ($zone) {
                return [
                    'method' => $method->value,
                    'label' => $method->label(),
                    'enabled' => $zone->methodEnabled($method),
                    'feeToman' => $zone->feeFor($method, $subtotalToman),
                ];
            }

            $fallback = config("winimi.checkout.delivery_methods.{$method->value}", []);

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

    /**
     * @return array{zone: null, fee_toman: int, packaging_fee_toman: int, preparation_min_days: int, preparation_max_days: int}
     */
    private function fallbackQuote(DeliveryMethod $method, int $subtotalToman): array
    {
        $delivery = config("winimi.checkout.delivery_methods.{$method->value}", []);
        if (! ($delivery['enabled'] ?? false)) {
            throw ValidationException::withMessages([
                'deliveryMethod' => ['روش تحویل انتخاب‌شده فعال نیست.'],
            ]);
        }

        return [
            'zone' => null,
            'fee_toman' => (int) ($delivery['fee_toman'] ?? 0),
            'packaging_fee_toman' => (int) config('winimi.checkout.packaging_fee_toman', 0),
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
