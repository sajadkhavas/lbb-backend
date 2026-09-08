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
    private const FREIGHT_COLLECT_NOTICE = 'هزینه ارسال خارج از پرداخت آنلاین فروشگاه و به‌صورت پس‌کرایه دریافت می‌شود.';

    /** @return array{zone: ?DeliveryZone, fee_toman: int, packaging_fee_toman: int, preparation_min_days: int, preparation_max_days: int} */
    public function quote(
        DeliveryMethod $method,
        ?string $province,
        ?string $city,
        int $subtotalToman,
    ): array {
        $this->assertEmployerPolicy($method, $city);

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
            'fee_toman' => 0,
            'packaging_fee_toman' => (int) $zone->packaging_fee_toman,
            'preparation_min_days' => (int) $zone->preparation_min_days,
            'preparation_max_days' => max((int) $zone->preparation_min_days, (int) $zone->preparation_max_days),
        ];
    }

    /**
     * @return array<int, array{
     *     method: string,
     *     label: string,
     *     enabled: bool,
     *     feeToman: int,
     *     paymentMode: string,
     *     isFree: bool,
     *     feeNotice: string,
     *     carrier: array{label: string},
     *     coverage: array{label: string},
     *     eta: array{label: string, minDays: ?int, maxDays: ?int}
     * }>
     */
    public function options(?string $province, ?string $city, int $subtotalToman): array
    {
        $zone = $this->resolve($province, $city);

        return collect(DeliveryMethod::cases())
            ->filter(static fn (DeliveryMethod $method): bool => $method->isEmployerApprovedMethod())
            ->map(function (DeliveryMethod $method) use ($zone, $city): array {
                $configured = $zone
                    ? $zone->methodEnabled($method)
                    : (bool) (config("lbb.checkout.delivery_methods.{$method->value}.enabled", false));
                $eligible = $method !== DeliveryMethod::ImmediateCourier || $this->isImmediateCity($city);
                $etaDays = $method->etaDays();

                return [
                    'method' => $method->value,
                    'label' => $method->label(),
                    'enabled' => $configured && $eligible,
                    'feeToman' => 0,
                    'paymentMode' => 'freight_collect',
                    'isFree' => false,
                    'feeNotice' => self::FREIGHT_COLLECT_NOTICE,
                    'carrier' => ['label' => (string) $method->carrierLabel()],
                    'coverage' => ['label' => (string) $method->coverageLabel()],
                    'eta' => [
                        'label' => (string) $method->etaLabel(),
                        'minDays' => $etaDays['minDays'],
                        'maxDays' => $etaDays['maxDays'],
                    ],
                ];
            })
            ->values()
            ->all();
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
            'fee_toman' => 0,
            'packaging_fee_toman' => (int) config('lbb.checkout.packaging_fee_toman', 0),
            'preparation_min_days' => 0,
            'preparation_max_days' => 0,
        ];
    }

    private function assertEmployerPolicy(DeliveryMethod $method, ?string $city): void
    {
        if (! $method->isEmployerApprovedMethod()) {
            throw ValidationException::withMessages([
                'deliveryMethod' => ['روش ارسال انتخاب‌شده در سیاست فعلی فروشگاه فعال نیست.'],
            ]);
        }

        if ($method === DeliveryMethod::ImmediateCourier && ! $this->isImmediateCity($city)) {
            throw ValidationException::withMessages([
                'deliveryMethod' => ['ارسال فوری فقط برای مقصد تهران یا کرج در دسترس است.'],
            ]);
        }
    }

    private function isImmediateCity(?string $city): bool
    {
        $city = $this->normalizeCity($city);

        return in_array($city, ['تهران', 'کرج', 'tehran', 'karaj'], true);
    }

    private function normalizeCity(?string $value): ?string
    {
        $value = $this->normalize($value);
        if ($value === null) {
            return null;
        }

        $value = str_replace(['ي', 'ك'], ['ی', 'ک'], $value);
        $value = preg_replace('/\s+/u', ' ', $value) ?? $value;

        return strtolower($value);
    }

    private function normalize(?string $value): ?string
    {
        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
