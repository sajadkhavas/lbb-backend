from pathlib import Path

root = Path('.')


def write(path: str, content: str) -> None:
    target = root / path
    target.parent.mkdir(parents=True, exist_ok=True)
    target.write_text(content)


def remove(path: str) -> None:
    target = root / path
    if target.exists():
        target.unlink()


# This command belongs to the removed ToolMaster catalog and imports deleted models.
# A dynamic LBB sitemap contract will be rebuilt after the apparel catalog is frozen.
remove('app/Console/Commands/GenerateSitemap.php')

write(
    'app/Providers/TelescopeServiceProvider.php',
    '''<?php

namespace App\\Providers;

use App\\Models\\User;
use Illuminate\\Support\\Facades\\Gate;
use Laravel\\Telescope\\IncomingEntry;
use Laravel\\Telescope\\Telescope;
use Laravel\\Telescope\\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function register(): void
    {
        Telescope::night();

        $this->hideSensitiveRequestDetails();

        Telescope::filter(function (IncomingEntry $entry): bool {
            if ($this->app->environment('local')) {
                return true;
            }

            return $entry->isReportableException()
                || $entry->isFailedRequest()
                || $entry->isFailedJob()
                || $entry->isScheduledTask()
                || $entry->hasMonitoredTag();
        });
    }

    protected function hideSensitiveRequestDetails(): void
    {
        if ($this->app->environment('local')) {
            return;
        }

        Telescope::hideRequestParameters([
            '_token',
            'password',
            'password_confirmation',
            'otp',
            'code',
        ]);

        Telescope::hideRequestHeaders([
            'cookie',
            'x-csrf-token',
            'x-xsrf-token',
            'authorization',
        ]);
    }

    protected function gate(): void
    {
        Gate::define('viewTelescope', static fn (User $user): bool => $user->hasRole('super_admin'));
    }
}
''',
)

write(
    'app/Http/Controllers/Api/SystemController.php',
    '''<?php

namespace App\\Http\\Controllers\\Api;

use App\\Http\\Controllers\\Controller;
use App\\Support\\ApiResponse;
use Illuminate\\Http\\JsonResponse;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\File;
use JsonException;
use Throwable;

class SystemController extends Controller
{
    public function health(): JsonResponse
    {
        return ApiResponse::success([
            'status' => 'ok',
            'service' => 'lbb-backend',
            'time' => now()->toIso8601String(),
        ]);
    }

    public function ready(): JsonResponse
    {
        try {
            DB::connection()->getPdo();

            return ApiResponse::success([
                'status' => 'ready',
                'checks' => [
                    'application' => 'ok',
                    'database' => 'ok',
                ],
                'time' => now()->toIso8601String(),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return ApiResponse::error(
                'سرویس هنوز آماده دریافت درخواست‌های وابسته به دیتابیس نیست.',
                503,
                [],
                ['checks' => ['application' => 'ok', 'database' => 'failed']],
            );
        }
    }

    public function meta(): JsonResponse
    {
        return ApiResponse::success([
            'service' => 'lbb-backend',
            'brand' => [
                'name' => config('lbb.brand.name'),
                'nameEn' => config('lbb.brand.name_en'),
            ],
            'apiVersion' => (string) config('lbb.api.version'),
            'contractVersion' => (string) config('lbb.api.contract_version'),
            'framework' => [
                'name' => 'Laravel',
                'version' => app()->version(),
            ],
            'backendComplete' => (bool) config('lbb.launch.backend_complete', false),
            'openApiUrl' => '/api/system/openapi',
        ]);
    }

    public function contracts(): JsonResponse
    {
        return ApiResponse::success([
            'contractVersion' => (string) config('lbb.api.contract_version'),
            'contracts' => config('lbb.contracts', []),
            'launch' => config('lbb.launch', []),
            'policies' => config('lbb.policies', []),
            'notes' => [
                'قرارداد کاتالوگ پوشاک هنوز در حال مهاجرت است.',
                'خرید، پرداخت و پیامک تا پایان ممیزی و انجماد قرارداد LBB غیرفعال می‌مانند.',
                'این پاسخ به معنی آمادگی انتشار production نیست.',
            ],
        ]);
    }

    /** @throws JsonException */
    public function openapi(): JsonResponse
    {
        $path = (string) config('lbb.api.openapi_path', base_path('docs/openapi.json'));
        $document = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
        $etag = '"'.hash_file('sha256', $path).'"';

        return response()
            ->json($document)
            ->header('Cache-Control', 'public, max-age=300')
            ->header('ETag', $etag);
    }
}
''',
)

write(
    'config/app.php',
    '''<?php

return [
    'name' => env('APP_NAME', 'LBB'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),
    'timezone' => env('APP_TIMEZONE', 'Asia/Tehran'),
    'locale' => env('APP_LOCALE', 'fa'),
    'fallback_locale' => env('APP_FALLBACK_LOCALE', 'fa'),
    'faker_locale' => 'fa_IR',
    'cipher' => 'AES-256-CBC',
    'key' => env('APP_KEY'),
    'previous_keys' => array_filter(explode(',', env('APP_PREVIOUS_KEYS', ''))),
    'maintenance' => [
        'driver' => env('APP_MAINTENANCE_DRIVER', 'file'),
        'store' => env('APP_MAINTENANCE_STORE', 'database'),
    ],
];
''',
)

write(
    'config/mail.php',
    '''<?php

return [
    'default' => env('MAIL_MAILER', 'log'),
    'mailers' => [
        'smtp' => [
            'transport' => 'smtp',
            'url' => env('MAIL_URL'),
            'host' => env('MAIL_HOST', '127.0.0.1'),
            'port' => env('MAIL_PORT', 2525),
            'encryption' => env('MAIL_ENCRYPTION'),
            'username' => env('MAIL_USERNAME'),
            'password' => env('MAIL_PASSWORD'),
            'timeout' => null,
            'local_domain' => env(
                'MAIL_EHLO_DOMAIN',
                parse_url(env('APP_URL', 'http://localhost'), PHP_URL_HOST),
            ),
        ],
        'log' => ['transport' => 'log', 'channel' => env('MAIL_LOG_CHANNEL')],
        'array' => ['transport' => 'array'],
        'failover' => ['transport' => 'failover', 'mailers' => ['smtp', 'log']],
    ],
    'from' => [
        'address' => env('MAIL_FROM_ADDRESS', 'noreply@localhost'),
        'name' => env('MAIL_FROM_NAME', env('APP_NAME', 'LBB')),
    ],
    'admin_address' => env('MAIL_ADMIN_ADDRESS'),
];
''',
)

write(
    'database/seeders/AdminUserSeeder.php',
    '''<?php

namespace Database\\Seeders;

use App\\Models\\User;
use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\Hash;
use RuntimeException;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $email = trim((string) env('LBB_DEV_ADMIN_EMAIL'));
        $password = (string) env('LBB_DEV_ADMIN_PASSWORD');

        if ($email === '' && $password === '') {
            $this->command?->warn('LBB development admin was not seeded; credentials are not configured.');

            return;
        }

        if ($email === '' || $password === '') {
            throw new RuntimeException(
                'Both LBB_DEV_ADMIN_EMAIL and LBB_DEV_ADMIN_PASSWORD are required to seed a development admin.',
            );
        }

        if (mb_strlen($password) < 16) {
            throw new RuntimeException('LBB development admin password must contain at least 16 characters.');
        }

        User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => env('LBB_DEV_ADMIN_NAME', 'LBB Admin'),
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ],
        );
    }
}
''',
)

panel_path = root / 'app/Providers/Filament/AdminPanelProvider.php'
if panel_path.exists():
    panel = panel_path.read_text()
    panel = panel.replace("->brandName(config('winimi.brand.name', 'وینیمی بیکری'))", "->brandName(config('lbb.brand.name', 'LBB'))")
    panel = panel.replace("->brandName(config('lbb.brand.name', 'وینیمی بیکری'))", "->brandName(config('lbb.brand.name', 'LBB'))")
    panel = panel.replace("'فروشگاه وینیمی',", "'فروشگاه LBB',")
    panel_path.write_text(panel)
