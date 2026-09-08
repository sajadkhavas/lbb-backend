<?php

namespace Tests\Feature;

use App\Enums\DeliveryMethod;
use App\Services\Store\DeliveryConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class EmployerShippingPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('lbb.checkout.delivery_methods.immediate_courier', [
            'enabled' => true,
            'fee_toman' => 120000,
        ]);
        config()->set('lbb.checkout.delivery_methods.tipax', [
            'enabled' => true,
            'fee_toman' => 85000,
        ]);
        config()->set('lbb.checkout.delivery_methods.decapost', [
            'enabled' => true,
            'fee_toman' => 75000,
        ]);
        config()->set('lbb.checkout.delivery_methods.express_post', [
            'enabled' => true,
            'fee_toman' => 65000,
        ]);
    }

    public function test_public_options_expose_only_employer_approved_freight_collect_methods(): void
    {
        $methods = collect(app(DeliveryConfigurationService::class)->options('تهران', 'تهران', 900000));

        $this->assertSame(
            ['immediate_courier', 'tipax', 'decapost'],
            $methods->pluck('method')->all(),
        );
        $this->assertTrue($methods->every(fn (array $method): bool => $method['paymentMode'] === 'freight_collect'));
        $this->assertTrue($methods->every(fn (array $method): bool => $method['feeToman'] === 0));
        $this->assertTrue($methods->every(fn (array $method): bool => $method['isFree'] === false));
        $this->assertTrue($methods->every(fn (array $method): bool => str_contains($method['feeNotice'], 'پس‌کرایه')));

        $immediate = $methods->firstWhere('method', 'immediate_courier');
        $this->assertTrue($immediate['enabled']);
        $this->assertSame('اسنپ / اسنپ‌باکس', $immediate['carrier']['label']);
        $this->assertSame('تهران و کرج', $immediate['coverage']['label']);
        $this->assertSame('فوری', $immediate['eta']['label']);

        $tipax = $methods->firstWhere('method', 'tipax');
        $this->assertSame('تمام نقاط ایران', $tipax['coverage']['label']);
        $this->assertSame(['label' => '۳ تا ۷ روز', 'minDays' => 3, 'maxDays' => 7], $tipax['eta']);
    }

    public function test_immediate_delivery_is_disabled_outside_tehran_and_karaj(): void
    {
        $methods = collect(app(DeliveryConfigurationService::class)->options('فارس', 'شیراز', 900000));

        $this->assertFalse($methods->firstWhere('method', 'immediate_courier')['enabled']);
        $this->assertTrue($methods->firstWhere('method', 'tipax')['enabled']);
        $this->assertTrue($methods->firstWhere('method', 'decapost')['enabled']);
    }

    public function test_immediate_delivery_accepts_persian_and_english_tehran_karaj_city_names(): void
    {
        $service = app(DeliveryConfigurationService::class);

        foreach (['تهران', 'کرج', 'Tehran', 'Karaj', 'كـرج'] as $city) {
            $normalizedCity = $city === 'كـرج' ? 'كرج' : $city;
            $methods = collect($service->options(null, $normalizedCity, 900000));
            $this->assertTrue(
                $methods->firstWhere('method', 'immediate_courier')['enabled'],
                "Immediate delivery should be enabled for {$normalizedCity}",
            );
        }
    }

    public function test_quote_never_charges_approved_shipping_online(): void
    {
        $quote = app(DeliveryConfigurationService::class)->quote(
            DeliveryMethod::Tipax,
            'فارس',
            'شیراز',
            900000,
        );

        $this->assertSame(0, $quote['fee_toman']);
    }

    public function test_quote_rejects_immediate_delivery_outside_tehran_and_karaj(): void
    {
        $this->expectException(ValidationException::class);

        app(DeliveryConfigurationService::class)->quote(
            DeliveryMethod::ImmediateCourier,
            'فارس',
            'شیراز',
            900000,
        );
    }

    public function test_quote_rejects_express_post_even_if_legacy_config_is_enabled(): void
    {
        $this->expectException(ValidationException::class);

        app(DeliveryConfigurationService::class)->quote(
            DeliveryMethod::ExpressPost,
            'تهران',
            'تهران',
            900000,
        );
    }
}
