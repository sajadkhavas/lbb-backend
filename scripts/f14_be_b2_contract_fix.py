from __future__ import annotations

import json
import re
from pathlib import Path

ROOT = Path('.')
CONTRACT_VERSION = '2026-08-07-f14-be-b2'


def write(path: str, content: str) -> None:
    target = ROOT / path
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_text(content.rstrip() + '\n', encoding='utf-8')


config = r'''<?php

$frontendOrigins = array_values(array_filter(array_map(
    static fn (string $origin): string => rtrim(trim($origin), '/'),
    explode(',', (string) env('FRONTEND_URLS', env('FRONTEND_URL', 'http://localhost:3000'))),
)));

$boolean = static fn (string $key, bool $default = false): bool => filter_var(
    env($key, $default),
    FILTER_VALIDATE_BOOL,
);

$zarinpalSandbox = $boolean('ZARINPAL_SANDBOX', true);

return [
    'brand' => [
        'name' => env('LBB_BRAND_NAME', 'LBB'),
        'name_en' => env('LBB_BRAND_NAME_EN', 'LBB'),
    ],
    'api' => [
        'version' => '1',
        'contract_version' => '2026-08-07-f14-be-b2',
        'request_id_header' => 'X-Request-ID',
        'openapi_path' => base_path('docs/openapi.json'),
    ],
    'frontend_origins' => $frontendOrigins,
    'stateful_domains' => env('SANCTUM_STATEFUL_DOMAINS', 'lbb.ir,www.lbb.ir'),
    'otp' => [
        'provider' => env('SMS_PROVIDER', 'disabled'),
        'length' => (int) env('OTP_LENGTH', 6),
        'expires_seconds' => (int) env('OTP_EXPIRES_SECONDS', 120),
        'retry_after_seconds' => (int) env('OTP_RETRY_AFTER_SECONDS', 60),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
        'expose_test_code' => $boolean('OTP_EXPOSE_TEST_CODE'),
        'kavenegar' => [
            'api_key' => env('KAVENEGAR_API_KEY'),
            'template' => env('KAVENEGAR_TEMPLATE'),
            'base_url' => env('KAVENEGAR_BASE_URL', 'https://api.kavenegar.com/v1'),
        ],
    ],
    'checkout' => [
        'enabled' => $boolean('CHECKOUT_ENABLED'),
        'reservation_minutes' => (int) env('INVENTORY_RESERVATION_MINUTES', 20),
        'max_quantity_per_line' => (int) env('CHECKOUT_MAX_QUANTITY_PER_LINE', 20),
        'max_total_units' => (int) env('CHECKOUT_MAX_TOTAL_UNITS', 50),
        'packaging_fee_toman' => 0,
        'delivery_methods' => [
            'standard' => [
                'enabled' => $boolean('DELIVERY_STANDARD_ENABLED'),
                'fee_toman' => (int) env('DELIVERY_STANDARD_FEE_TOMAN', 0),
            ],
            'pickup' => [
                'enabled' => $boolean('DELIVERY_PICKUP_ENABLED'),
                'fee_toman' => (int) env('DELIVERY_PICKUP_FEE_TOMAN', 0),
            ],
        ],
    ],
    'payment' => [
        'enabled' => $boolean('PAYMENT_ENABLED'),
        'provider' => env('PAYMENT_PROVIDER', 'disabled'),
        'callback_url' => env('PAYMENT_CALLBACK_URL', 'http://localhost:3000/payment/result'),
        'currency' => env('PAYMENT_CURRENCY', 'IRR'),
        'amount_multiplier' => (int) env('PAYMENT_AMOUNT_MULTIPLIER', 10),
        'attempt_ttl_minutes' => (int) env('PAYMENT_ATTEMPT_TTL_MINUTES', 20),
        'timeout_seconds' => (int) env('PAYMENT_TIMEOUT_SECONDS', 10),
        'zarinpal' => [
            'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
            'sandbox' => $zarinpalSandbox,
            'request_url' => env('ZARINPAL_REQUEST_URL', $zarinpalSandbox
                ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
                : 'https://api.zarinpal.com/pg/v4/payment/request.json'),
            'verify_url' => env('ZARINPAL_VERIFY_URL', $zarinpalSandbox
                ? 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json'
                : 'https://api.zarinpal.com/pg/v4/payment/verify.json'),
            'start_pay_url' => env('ZARINPAL_START_PAY_URL', $zarinpalSandbox
                ? 'https://sandbox.zarinpal.com/pg/StartPay'
                : 'https://www.zarinpal.com/pg/StartPay'),
        ],
    ],
    'notifications' => [
        'sms_provider' => env('ORDER_SMS_PROVIDER', 'disabled'),
        'max_attempts' => (int) env('NOTIFICATION_MAX_ATTEMPTS', 5),
        'retry_seconds' => (int) env('NOTIFICATION_RETRY_SECONDS', 60),
        'timeout_seconds' => (int) env('NOTIFICATION_TIMEOUT_SECONDS', 8),
        'kavenegar' => [
            'api_key' => env('KAVENEGAR_API_KEY'),
            'sender' => env('KAVENEGAR_ORDER_SENDER'),
            'base_url' => env('KAVENEGAR_BASE_URL', 'https://api.kavenegar.com/v1'),
        ],
    ],
    'policies' => [
        'pagination' => [
            'shape' => ['page', 'perPage', 'total', 'totalPages', 'from', 'to', 'hasMore'],
            'catalog_default' => 12,
            'catalog_max' => 48,
            'account_default' => 10,
            'account_max' => 30,
        ],
        'cache' => [
            'store' => env('CACHE_STORE', 'database'),
            'prefix' => env('CACHE_PREFIX', 'lbb'),
        ],
        'storage' => [
            'application_disk' => env('FILESYSTEM_DISK', 'local'),
            'media_disk' => env('MEDIA_DISK', 'public'),
        ],
        'backup' => [
            'disk' => env('BACKUP_DISK', 'local'),
            'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14),
        ],
    ],
    'contracts' => [
        'system' => ['status' => 'implemented'],
        'domain_cleanup' => [
            'status' => 'ready',
            'source' => 'neutral-commerce-baseline',
        ],
        'catalog' => [
            'status' => 'neutral-baseline-ready',
            'source' => 'generic-commerce-only',
        ],
        'apparel_domain' => [
            'status' => 'not-started',
            'target_phase' => 'F14-BE-C',
        ],
        'authentication' => ['status' => 'imported-pending-lbb-verification'],
        'orders' => ['status' => 'imported-pending-lbb-verification'],
        'payments' => ['status' => 'disabled-pending-lbb-verification'],
        'store_operations' => ['status' => 'neutral-baseline-ready'],
        'backend_freeze' => ['status' => 'not-ready'],
    ],
    'launch' => [
        'strategy' => 'fail-closed-until-apparel-contract-freeze',
        'backend_complete' => false,
        'frontend_integrated' => false,
        'production_deployed' => false,
    ],
];
'''
write('config/lbb.php', config)

readiness = r'''<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Throwable;

class BackendReadiness extends Command
{
    protected $signature = 'backend:readiness {--json : Emit machine-readable JSON}';

    protected $description = 'Validate the current LBB backend contract and fail closed until apparel freeze';

    public function handle(): int
    {
        $checks = [
            'contract_version' => $this->check(
                config('lbb.api.contract_version') === '2026-08-07-f14-be-b2',
                (string) config('lbb.api.contract_version'),
            ),
            'domain_cleanup' => $this->check(
                config('lbb.contracts.domain_cleanup.status') === 'ready',
                (string) config('lbb.contracts.domain_cleanup.status'),
            ),
            'openapi' => $this->openApiCheck(),
            'database' => $this->databaseCheck(),
            'apparel_domain' => $this->check(
                config('lbb.contracts.apparel_domain.status') === 'ready',
                (string) config('lbb.contracts.apparel_domain.status'),
            ),
            'backend_freeze' => $this->check(
                config('lbb.contracts.backend_freeze.status') === 'ready',
                (string) config('lbb.contracts.backend_freeze.status'),
            ),
        ];

        $ready = collect($checks)->every(fn (array $check): bool => $check['ok']);
        $payload = [
            'ready' => $ready,
            'contractVersion' => config('lbb.api.contract_version'),
            'checks' => $checks,
        ];

        if ($this->option('json')) {
            $this->line((string) json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } else {
            $this->table(
                ['Check', 'Status', 'Detail'],
                collect($checks)->map(fn (array $check, string $name): array => [
                    $name,
                    $check['ok'] ? 'ready' : 'failed',
                    $check['detail'],
                ])->values()->all(),
            );
        }

        return $ready ? self::SUCCESS : self::FAILURE;
    }

    private function openApiCheck(): array
    {
        try {
            $path = (string) config('lbb.api.openapi_path');
            $document = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
            $valid = ($document['openapi'] ?? null) === '3.1.0'
                && ($document['info']['version'] ?? null) === config('lbb.api.contract_version')
                && isset($document['paths']['/api/system/openapi'])
                && ! isset($document['paths']['/api/catalog/products']);

            return $this->check($valid, $path);
        } catch (Throwable $exception) {
            return $this->check(false, $exception->getMessage());
        }
    }

    private function databaseCheck(): array
    {
        try {
            DB::connection()->getPdo();

            return $this->check(true, DB::connection()->getDriverName());
        } catch (Throwable $exception) {
            return $this->check(false, $exception->getMessage());
        }
    }

    private function check(bool $ok, string $detail): array
    {
        return ['ok' => $ok, 'detail' => $detail];
    }
}
'''
write('app/Console/Commands/BackendReadiness.php', readiness)

openapi = {
    'openapi': '3.1.0',
    'info': {
        'title': 'LBB Backend API',
        'version': CONTRACT_VERSION,
        'description': 'F14-BE-B2 neutral commerce baseline. Apparel contracts are intentionally not frozen.',
    },
    'paths': {
        '/api/system/health': {'get': {'responses': {'200': {'description': 'Healthy'}}}},
        '/api/system/ready': {'get': {'responses': {'200': {'description': 'Runtime readiness'}}}},
        '/api/system/meta': {'get': {'responses': {'200': {'description': 'API metadata'}}}},
        '/api/system/contracts': {'get': {'responses': {'200': {'description': 'Current contract gates'}}}},
        '/api/system/openapi': {'get': {'responses': {'200': {'description': 'OpenAPI document'}}}},
    },
}
write('docs/openapi.json', json.dumps(openapi, ensure_ascii=False, indent=2))

system_test = r'''<?php

namespace Tests\Feature;

use Tests\TestCase;

class SystemApiTest extends TestCase
{
    public function test_health_endpoint_returns_b2_metadata(): void
    {
        $this->getJson('/api/system/health', ['X-Request-ID' => 'test-request-id'])
            ->assertOk()
            ->assertHeader('X-Request-ID', 'test-request-id')
            ->assertHeader('X-API-Version', '1')
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'ok')
            ->assertJsonPath('data.service', 'lbb-backend')
            ->assertJsonPath('meta.contractVersion', '2026-08-07-f14-be-b2');
    }

    public function test_meta_reports_neutral_baseline_without_claiming_backend_completion(): void
    {
        $this->getJson('/api/system/meta')
            ->assertOk()
            ->assertJsonPath('data.brand.nameEn', 'LBB')
            ->assertJsonPath('data.contractVersion', '2026-08-07-f14-be-b2')
            ->assertJsonPath('data.backendComplete', false)
            ->assertJsonPath('data.openApiUrl', '/api/system/openapi');
    }

    public function test_contract_endpoint_is_fail_closed_until_apparel_domain_is_built(): void
    {
        $this->getJson('/api/system/contracts')
            ->assertOk()
            ->assertJsonPath('data.contracts.system.status', 'implemented')
            ->assertJsonPath('data.contracts.domain_cleanup.status', 'ready')
            ->assertJsonPath('data.contracts.catalog.status', 'neutral-baseline-ready')
            ->assertJsonPath('data.contracts.apparel_domain.status', 'not-started')
            ->assertJsonPath('data.contracts.apparel_domain.target_phase', 'F14-BE-C')
            ->assertJsonPath('data.contracts.backend_freeze.status', 'not-ready')
            ->assertJsonPath('data.launch.backend_complete', false)
            ->assertJsonPath('data.launch.production_deployed', false);
    }

    public function test_unknown_api_routes_use_standard_json_error(): void
    {
        $this->getJson('/api/does-not-exist')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'resource_not_found')
            ->assertJsonPath('meta.contractVersion', '2026-08-07-f14-be-b2');
    }
}
'''
write('tests/Feature/SystemApiTest.php', system_test)

foundation_test = r'''<?php

namespace Tests\Unit;

use Tests\TestCase;

class BackendFoundationTest extends TestCase
{
    public function test_frontend_origins_are_configured_as_an_array(): void
    {
        $origins = config('lbb.frontend_origins');

        $this->assertIsArray($origins);
        $this->assertNotEmpty($origins);
        $this->assertSame($origins, config('cors.allowed_origins'));
        $this->assertTrue((bool) config('cors.supports_credentials'));
    }

    public function test_b2_contract_reports_only_the_neutral_baseline_as_ready(): void
    {
        $contracts = config('lbb.contracts');

        $this->assertSame('implemented', $contracts['system']['status']);
        $this->assertSame('ready', $contracts['domain_cleanup']['status']);
        $this->assertSame('neutral-baseline-ready', $contracts['catalog']['status']);
        $this->assertSame('not-started', $contracts['apparel_domain']['status']);
        $this->assertSame('F14-BE-C', $contracts['apparel_domain']['target_phase']);
        $this->assertSame('imported-pending-lbb-verification', $contracts['authentication']['status']);
        $this->assertSame('imported-pending-lbb-verification', $contracts['orders']['status']);
        $this->assertSame('disabled-pending-lbb-verification', $contracts['payments']['status']);
        $this->assertSame('not-ready', $contracts['backend_freeze']['status']);
        $this->assertFalse((bool) config('lbb.launch.backend_complete'));
    }
}
'''
write('tests/Unit/BackendFoundationTest.php', foundation_test)

freeze_test = r'''<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class BackendContractFreezeTest extends TestCase
{
    use RefreshDatabase;

    public function test_b2_openapi_is_system_only_and_backend_freeze_remains_closed(): void
    {
        $document = $this->getJson('/api/system/openapi')
            ->assertOk()
            ->json();

        $this->assertSame('3.1.0', $document['openapi']);
        $this->assertSame('2026-08-07-f14-be-b2', $document['info']['version']);
        $this->assertArrayHasKey('/api/system/openapi', $document['paths']);
        $this->assertArrayNotHasKey('/api/catalog/products', $document['paths']);

        $this->getJson('/api/system/contracts')
            ->assertOk()
            ->assertJsonPath('data.contracts.domain_cleanup.status', 'ready')
            ->assertJsonPath('data.contracts.apparel_domain.status', 'not-started')
            ->assertJsonPath('data.contracts.backend_freeze.status', 'not-ready');

        $this->assertSame(1, Artisan::call('backend:readiness', ['--json' => true]));
    }
}
'''
write('tests/Feature/BackendContractFreezeTest.php', freeze_test)

otp_path = ROOT / 'tests/Feature/CustomerOtpAuthTest.php'
if otp_path.exists():
    otp = otp_path.read_text(encoding='utf-8')
    otp = re.sub(
        r"    public function test_authentication_orders_and_payments_contracts_are_implemented_with_external_activation_disabled\(\): void\n    \{.*?\n    \}\n\n    private function requestChallenge",
        """    public function test_imported_customer_contracts_remain_fail_closed_during_b2(): void\n    {\n        $this->getJson('/api/system/contracts')\n            ->assertOk()\n            ->assertJsonPath('data.contracts.authentication.status', 'imported-pending-lbb-verification')\n            ->assertJsonPath('data.contracts.orders.status', 'imported-pending-lbb-verification')\n            ->assertJsonPath('data.contracts.payments.status', 'disabled-pending-lbb-verification')\n            ->assertJsonPath('data.contracts.backend_freeze.status', 'not-ready');\n    }\n\n    private function requestChallenge""",
        otp,
        flags=re.S,
    )
    otp_path.write_text(otp, encoding='utf-8')

status_path = ROOT / 'docs/F14_BE_B_CLEANUP_STATUS.md'
status = status_path.read_text(encoding='utf-8') if status_path.exists() else '# F14-BE-B Cleanup Status\n'
status += '''\n\n## B2 contract state\n\n- Domain cleanup gate: ready.\n- Generic catalog baseline: neutral-baseline-ready.\n- Apparel domain: not-started; owned by F14-BE-C.\n- Backend freeze: not-ready and fail-closed by design.\n- Production readiness is not claimed by this phase.\n'''
status_path.write_text(status, encoding='utf-8')

print('f14_be_b2_contract_fix=complete')
