from __future__ import annotations

import json
import re
import shutil
from pathlib import Path

root = Path(".")


def remove(path: str) -> None:
    target = root / path
    if target.is_dir():
        shutil.rmtree(target)
    elif target.exists() or target.is_symlink():
        target.unlink()


def move_to_reference(path: str, destination: str) -> None:
    source = root / path
    if not source.exists():
        return
    target = root / destination
    target.parent.mkdir(parents=True, exist_ok=True)
    if target.exists():
        remove(destination)
    shutil.move(str(source), str(target))


legacy_models = [
    "AbTest",
    "AbTestResult",
    "AbTestVariant",
    "ApiKey",
    "BlogPost",
    "Brand",
    "Category",
    "Contact",
    "Coupon",
    "EmailTemplate",
    "Faq",
    "FeatureFlag",
    "GoogleIndexingLog",
    "IpBlacklist",
    "MaintenanceSetting",
    "NavigationItem",
    "NewsletterSubscriber",
    "PerformanceMetric",
    "Product",
    "Redirect",
    "Review",
    "RfqItem",
    "RfqRequest",
    "SchemaMarkup",
    "SeoMeta",
    "SeoScan",
    "Setting",
    "ShortUrl",
    "SitePage",
    "SiteSetting",
    "Slider",
    "Subcategory",
    "Tag",
    "Translation",
    "Webhook",
]

for model in legacy_models:
    remove(f"app/Models/{model}.php")
    remove(f"app/Filament/Resources/{model}Resource.php")
    remove(f"app/Filament/Resources/{model}Resource")

for extra_resource in ["RfqResource", "ContactResource"]:
    remove(f"app/Filament/Resources/{extra_resource}.php")
    remove(f"app/Filament/Resources/{extra_resource}")

remove("app/Http/Controllers/Api/V1")
remove("app/Http/Controllers/Api/PerformanceMetricController.php")
remove("app/Http/Middleware/MarkLegacyApi.php")
remove("src")
remove("deploy")

for frontend_file in [
    "index.html",
    "components.json",
    "eslint.config.js",
    "package.json",
    "package-lock.json",
    "bun.lockb",
    "vite.config.ts",
    "tsconfig.json",
    "tsconfig.app.json",
    "tsconfig.node.json",
    "tailwind.config.ts",
    "tailwind.config.js",
    "postcss.config.js",
    "postcss.config.cjs",
]:
    remove(frontend_file)

legacy_migrations = [
    "2024_01_01_000001_create_categories_table.php",
    "2024_01_01_000002_create_subcategories_table.php",
    "2024_01_01_000003_create_brands_table.php",
    "2024_01_01_000004_create_products_table.php",
    "2024_01_01_000005_create_blog_posts_table.php",
    "2024_01_01_000006_create_rfq_tables.php",
    "2024_01_01_000007_create_site_settings_table.php",
    "2024_01_01_000008_create_sliders_table.php",
    "2024_01_01_000009_create_contacts_and_newsletter_tables.php",
    "2026_05_30_123323_create_site_pages_table.php",
    "2026_05_30_123349_create_navigation_items_table.php",
    "2026_05_31_192135_create_settings_table.php",
    "2026_06_04_024112_create_redirects_table.php",
    "2026_06_05_160854_create_seo_meta_table.php",
    "2026_06_07_092116_create_schema_markups_table.php",
    "2026_06_07_093020_create_email_templates_table.php",
    "2026_06_07_093709_create_webhooks_table.php",
    "2026_06_07_094257_create_api_keys_table.php",
    "2026_06_07_100631_create_feature_flags_table.php",
    "2026_06_07_101212_create_translations_table.php",
    "2026_06_07_110506_create_pages_table.php",
    "2026_06_07_110507_fix_slug_unique_constraint_on_pages_table.php",
    "2026_06_07_145702_create_performance_metrics_table.php",
    "2026_06_07_145709_create_ab_tests_table.php",
    "2026_06_07_145719_create_ab_test_variants_table.php",
    "2026_06_07_145725_create_ab_test_results_table.php",
    "2026_06_07_172416_create_coupons_table.php",
    "2026_06_07_172418_create_faqs_table.php",
    "2026_06_07_172419_create_reviews_table.php",
    "2026_06_07_172420_create_tags_table.php",
    "2026_06_07_172423_create_short_urls_table.php",
    "2026_06_07_174346_create_seo_scans_table.php",
    "2026_06_07_174349_create_google_indexing_logs_table.php",
    "2026_06_07_175936_create_ip_blacklists_table.php",
    "2026_06_07_175939_create_maintenance_mode_table.php",
    "2026_06_11_113550_fix_seo_meta_description_columns.php",
    "2026_06_11_114324_add_indexes_to_seo_scans.php",
    "2026_06_11_132802_add_ip_to_performance_metrics_table.php",
]
for migration in legacy_migrations:
    remove(f"database/migrations/{migration}")

for seeder in [
    "SettingsSeeder.php",
    "SiteSettingsSeeder.php",
    "WinimiStagingSeeder.php",
]:
    remove(f"database/seeders/{seeder}")

reference_docs = [
    "DEPLOYMENT.md",
    "docs/API_CONTRACT.md",
    "docs/API_ERRORS_AND_PAGINATION.md",
    "docs/BACKEND_AUDIT.md",
    "docs/BACKUP_RESTORE.md",
    "docs/CATALOG_API.md",
    "docs/CUSTOMER_AUTH.md",
    "docs/FULL_LAUNCH_ROADMAP.md",
    "docs/LARAVEL_BACKEND_COMPLETE.md",
    "docs/LARAVEL_INTEGRATION.md",
    "docs/OPERATIONS_POLICIES.md",
    "docs/ORDERS_CHECKOUT.md",
    "docs/PAYMENTS.md",
    "docs/PHASE_19_PRODUCTION_DEPLOYMENT.md",
    "docs/QUERY_INDEX_REVIEW.md",
    "docs/SINGLE_SERVER_TOPOLOGY.md",
    "docs/STORE_OPERATIONS.md",
    "docs/openapi.json",
]
for path in reference_docs:
    move_to_reference(path, f"docs/reference/winimi-import/{Path(path).name}")

reference_scripts = [
    "audit-backend-foundation.php",
    "audit-backend-freeze.php",
    "audit-bakery-catalog.php",
    "audit-customer-auth.php",
    "audit-full-launch-roadmap.php",
    "audit-orders-checkout.php",
    "audit-payments.php",
    "audit-phase-18-acceptance.php",
    "audit-phase19-production-preparation.php",
    "audit-store-operations.php",
    "create-backend-release.php",
    "verify-backend-release.php",
]
for name in reference_scripts:
    move_to_reference(
        f"scripts/{name}", f"scripts/reference/winimi-import/{name}"
    )

routes_path = root / "routes/api.php"
routes = routes_path.read_text()
routes = re.sub(
    r"^use App\\Http\\Controllers\\Api\\V1\\.*;\n", "", routes, flags=re.M
)
routes = routes.replace(
    "use App\\Http\\Controllers\\Api\\PerformanceMetricController;\n", ""
)
marker = "/*\n|--------------------------------------------------------------------------\n| Legacy ToolMaster API"
if marker in routes:
    routes = routes.split(marker, 1)[0].rstrip() + "\n"
routes = re.sub(
    r"\n\s*Route::get\('cities/\{slug\}', \[StoreContentController::class, 'city'\]\);",
    "",
    routes,
)
routes_path.write_text(routes)

bootstrap_path = root / "bootstrap/app.php"
bootstrap = bootstrap_path.read_text()
bootstrap = bootstrap.replace("use App\\Http\\Middleware\\MarkLegacyApi;\n", "")
bootstrap = bootstrap.replace(
    "            'api.legacy' => MarkLegacyApi::class,\n", ""
)
bootstrap_path.write_text(bootstrap)

for path in root.rglob("*"):
    if not path.is_file() or ".git" in path.parts:
        continue
    if path.parts[:2] == ("docs", "reference"):
        continue
    if path.parts[:2] == ("scripts", "reference"):
        continue
    if path == Path("scripts/f14_be_b_cleanup.py"):
        continue
    if (
        path.suffix.lower()
        not in {
            ".php",
            ".json",
            ".md",
            ".txt",
            ".yml",
            ".yaml",
            ".xml",
            ".env",
            ".example",
        }
        and path.name != ".gitignore"
    ):
        continue
    try:
        text = path.read_text()
    except UnicodeDecodeError:
        continue
    updated = text.replace("config('winimi.", "config('lbb.")
    updated = updated.replace('config("winimi.', 'config("lbb.')
    updated = updated.replace("Winimi Bakery", "LBB")
    updated = updated.replace("WINIMI_", "LBB_")
    updated = updated.replace("Winimi", "LBB")
    updated = updated.replace("winimi", "lbb")
    if updated != text:
        path.write_text(updated)

remove("config/winimi.php")
(root / "config/lbb.php").write_text(
    """<?php

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
        'contract_version' => '2026-08-06-f14-be-b1',
        'request_id_header' => 'X-Request-ID',
        'openapi_path' => base_path('docs/openapi.json'),
    ],
    'frontend_origins' => $frontendOrigins,
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
        'catalog' => ['status' => 'migration-in-progress'],
        'authentication' => ['status' => 'imported-pending-lbb-verification'],
        'orders' => ['status' => 'imported-pending-lbb-verification'],
        'payments' => ['status' => 'disabled-pending-lbb-verification'],
        'store_operations' => ['status' => 'migration-in-progress'],
        'backend_freeze' => ['status' => 'not-ready'],
    ],
    'launch' => [
        'strategy' => 'fail-closed-until-lbb-contract-freeze',
        'backend_complete' => false,
        'frontend_integrated' => false,
        'production_deployed' => false,
    ],
];
"""
)

env_path = root / ".env.example"
env = env_path.read_text()
env = "\n".join(
    line
    for line in env.splitlines()
    if not line.startswith("LEGACY_TOOLMASTER_API_ENABLED=")
    and not line.startswith("SEED_LBB_STAGING=")
) + "\n"
if "LBB_BRAND_NAME=" not in env:
    env += '\nLBB_BRAND_NAME="LBB"\nLBB_BRAND_NAME_EN="LBB"\n'
env_path.write_text(env)

(root / "database/seeders/DatabaseSeeder.php").write_text(
    """<?php

namespace Database\\Seeders;

use Illuminate\\Database\\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->environment(['local', 'testing'])) {
            $this->call(AdminUserSeeder::class);
        }
    }
}
"""
)

composer_path = root / "composer.json"
composer = json.loads(composer_path.read_text())
composer["name"] = "lbb/apparel-backend"
composer["description"] = (
    "LBB Laravel 12 headless apparel commerce backend with Filament administration"
)
composer["scripts"]["audit:foundation"] = [
    "@php scripts/audit-lbb-foundation.php"
]
for key in [
    "audit:catalog",
    "audit:auth",
    "audit:orders",
    "audit:payments",
    "audit:operations",
    "audit:launch",
]:
    composer["scripts"].pop(key, None)
composer["scripts"]["format:check"] = ["@php vendor/bin/pint --test"]
composer["scripts"]["check"] = [
    "@audit:foundation",
    "@format:check",
    "@php artisan route:list --except-vendor",
]
composer_path.write_text(json.dumps(composer, ensure_ascii=False, indent=4) + "\n")

scripts_dir = root / "scripts"
scripts_dir.mkdir(exist_ok=True)
(scripts_dir / "audit-lbb-foundation.php").write_text(
    """<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$errors = [];

$required = [
    'config/lbb.php',
    'routes/api.php',
    'app/Models/Customer.php',
    'app/Models/Order.php',
    'app/Models/PaymentAttempt.php',
    'app/Services/Orders/CheckoutService.php',
];
foreach ($required as $path) {
    if (! is_file($root.'/'.$path)) {
        $errors[] = 'Missing required foundation file: '.$path;
    }
}

$forbidden = [
    'config/winimi.php',
    'app/Http/Controllers/Api/V1',
    'app/Http/Middleware/MarkLegacyApi.php',
    'deploy',
    'src',
];
foreach ($forbidden as $path) {
    if (file_exists($root.'/'.$path)) {
        $errors[] = 'Forbidden inherited path remains: '.$path;
    }
}

$activeRoots = ['app', 'bootstrap', 'config', 'database', 'routes'];
$needles = [
    'Tool'.'Master',
    'TOOL'.'MASTER',
    'tool'.'master',
    'Win'.'imi',
    'WIN'.'IMI',
    'win'.'imi',
];
foreach ($activeRoots as $activeRoot) {
    $base = $root.'/'.$activeRoot;
    if (! is_dir($base)) {
        continue;
    }
    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base));
    foreach ($iterator as $file) {
        if (! $file->isFile()) {
            continue;
        }
        $contents = @file_get_contents($file->getPathname());
        if ($contents === false) {
            continue;
        }
        foreach ($needles as $needle) {
            if (str_contains($contents, $needle)) {
                $relative = str_replace($root.'/', '', str_replace('\\\\', '/', $file->getPathname()));
                $errors[] = 'Forbidden identity reference '.$needle.' in '.$relative;
                break;
            }
        }
    }
}

if ($errors !== []) {
    fwrite(STDERR, implode(PHP_EOL, array_values(array_unique($errors))).PHP_EOL);
    exit(1);
}

echo "lbb_backend_foundation=clean\\n";
"""
)

(root / "docs/openapi.json").write_text(
    json.dumps(
        {
            "openapi": "3.1.0",
            "info": {
                "title": "LBB Backend API",
                "version": "2026-08-06-f14-be-b1",
                "description": (
                    "Transitional system-only contract. Commerce endpoints are not frozen."
                ),
            },
            "paths": {
                "/api/system/health": {
                    "get": {"responses": {"200": {"description": "Healthy"}}}
                },
                "/api/system/ready": {
                    "get": {
                        "responses": {"200": {"description": "Readiness status"}}
                    }
                },
                "/api/system/meta": {
                    "get": {
                        "responses": {"200": {"description": "API metadata"}}
                    }
                },
            },
        },
        ensure_ascii=False,
        indent=2,
    )
    + "\n"
)

(root / "README.md").write_text(
    """# LBB Backend

Independent Laravel 12 + Filament backend for the LBB apparel commerce platform.

## Current state

- F14-BE-A imported the audited backend baseline without changing Cooci.
- F14-BE-B is removing inherited legacy and food-specific behavior.
- Checkout, payment, SMS and production launch remain disabled until the LBB API contract is frozen.

## Repository boundary

All LBB backend development happens here. The Cooci repositories are read-only references and are not modified by this migration.
"""
)

(root / "docs/F14_BE_B_CLEANUP_STATUS.md").write_text(
    """# F14-BE-B Cleanup Status

## Completed in B1

- Removed the complete `/api/v1` inherited route surface and controllers.
- Removed the legacy API middleware and configuration flag.
- Removed inherited legacy models, Filament resources and schema migrations.
- Removed the unrelated React/Vite storefront source from the backend repository.
- Quarantined imported contracts, audits and deployment documentation under reference folders.
- Replaced active imported configuration with fail-closed `config/lbb.php`.
- Reset Composer checks to an LBB foundation audit.

## Remaining in B2

- Rename Bakery catalog/content classes and tables to neutral LBB names.
- Remove food-only fields and chilled delivery behavior.
- Remove city SEO pages.
- Rebuild catalog and Filament tests around apparel requirements.
"""
)
