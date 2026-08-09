<?php

$frontendOrigins = array_values(array_filter(array_map(
    static fn (string $origin): string => rtrim(trim($origin), '/'),
    explode(',', (string) env('FRONTEND_URLS', env('FRONTEND_URL', 'http://localhost:3000'))),
)));
$boolean = static fn (string $key, bool $default = false): bool => filter_var(env($key, $default), FILTER_VALIDATE_BOOL);
$zarinpalSandbox = $boolean('ZARINPAL_SANDBOX', true);

return [
    'brand' => ['name' => env('LBB_BRAND_NAME', 'LBB'), 'name_en' => env('LBB_BRAND_NAME_EN', 'LBB')],
    'api' => [
        'version' => '1', 'contract_version' => '2026-08-08-f14-be-e', 'request_id_header' => 'X-Request-ID',
        'openapi_path' => base_path('docs/openapi.json'),
    ],
    'frontend_origins' => $frontendOrigins,
    'stateful_domains' => env('SANCTUM_STATEFUL_DOMAINS', 'lbb.ir,www.lbb.ir'),
    'otp' => [
        'provider' => env('SMS_PROVIDER', 'disabled'), 'length' => (int) env('OTP_LENGTH', 6),
        'expires_seconds' => (int) env('OTP_EXPIRES_SECONDS', 120), 'retry_after_seconds' => (int) env('OTP_RETRY_AFTER_SECONDS', 60),
        'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5), 'expose_test_code' => $boolean('OTP_EXPOSE_TEST_CODE'),
        'kavenegar' => ['api_key' => env('KAVENEGAR_API_KEY'), 'template' => env('KAVENEGAR_TEMPLATE'), 'base_url' => env('KAVENEGAR_BASE_URL', 'https://api.kavenegar.com/v1')],
    ],
    'checkout' => [
        'enabled' => $boolean('CHECKOUT_ENABLED'), 'reservation_minutes' => (int) env('INVENTORY_RESERVATION_MINUTES', 20),
        'max_quantity_per_line' => (int) env('CHECKOUT_MAX_QUANTITY_PER_LINE', 20), 'max_total_units' => (int) env('CHECKOUT_MAX_TOTAL_UNITS', 50),
        'packaging_fee_toman' => 0,
        'delivery_methods' => [
            'standard' => ['enabled' => $boolean('DELIVERY_STANDARD_ENABLED'), 'fee_toman' => (int) env('DELIVERY_STANDARD_FEE_TOMAN', 0)],
            'pickup' => ['enabled' => $boolean('DELIVERY_PICKUP_ENABLED'), 'fee_toman' => (int) env('DELIVERY_PICKUP_FEE_TOMAN', 0)],
        ],
    ],
    'commerce' => [
        'quote_minutes' => (int) env('COMMERCE_QUOTE_MINUTES', 5),
        'exchange_reservation_minutes' => (int) env('COMMERCE_EXCHANGE_RESERVATION_MINUTES', 60),
    ],
    'payment' => [
        'enabled' => $boolean('PAYMENT_ENABLED'), 'provider' => env('PAYMENT_PROVIDER', 'disabled'),
        'callback_url' => env('PAYMENT_CALLBACK_URL', 'http://localhost:3000/payment/result'),
        'currency' => env('PAYMENT_CURRENCY', 'IRR'), 'amount_multiplier' => (int) env('PAYMENT_AMOUNT_MULTIPLIER', 10),
        'attempt_ttl_minutes' => (int) env('PAYMENT_ATTEMPT_TTL_MINUTES', 20), 'timeout_seconds' => (int) env('PAYMENT_TIMEOUT_SECONDS', 10),
        'refunds_enabled' => $boolean('PAYMENT_REFUNDS_ENABLED'),
        'zarinpal' => [
            'merchant_id' => env('ZARINPAL_MERCHANT_ID'), 'sandbox' => $zarinpalSandbox,
            'request_url' => env('ZARINPAL_REQUEST_URL', $zarinpalSandbox ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json' : 'https://api.zarinpal.com/pg/v4/payment/request.json'),
            'verify_url' => env('ZARINPAL_VERIFY_URL', $zarinpalSandbox ? 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json' : 'https://api.zarinpal.com/pg/v4/payment/verify.json'),
            'start_pay_url' => env('ZARINPAL_START_PAY_URL', $zarinpalSandbox ? 'https://sandbox.zarinpal.com/pg/StartPay' : 'https://www.zarinpal.com/pg/StartPay'),
        ],
    ],
    'notifications' => [
        'sms_provider' => env('ORDER_SMS_PROVIDER', 'disabled'), 'max_attempts' => (int) env('NOTIFICATION_MAX_ATTEMPTS', 5),
        'retry_seconds' => (int) env('NOTIFICATION_RETRY_SECONDS', 60), 'timeout_seconds' => (int) env('NOTIFICATION_TIMEOUT_SECONDS', 8),
        'kavenegar' => ['api_key' => env('KAVENEGAR_API_KEY'), 'sender' => env('KAVENEGAR_ORDER_SENDER'), 'base_url' => env('KAVENEGAR_BASE_URL', 'https://api.kavenegar.com/v1')],
    ],
    'policies' => [
        'pagination' => ['shape' => ['page', 'perPage', 'total', 'totalPages', 'from', 'to', 'hasMore'], 'catalog_default' => 12, 'catalog_max' => 48, 'account_default' => 10, 'account_max' => 30],
        'cache' => ['store' => env('CACHE_STORE', 'database'), 'prefix' => env('CACHE_PREFIX', 'lbb')],
        'storage' => ['application_disk' => env('FILESYSTEM_DISK', 'local'), 'media_disk' => env('MEDIA_DISK', 'public')],
        'backup' => ['disk' => env('BACKUP_DISK', 'local'), 'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14)],
    ],
    'contracts' => [
        'system' => ['status' => 'implemented'], 'domain_cleanup' => ['status' => 'ready', 'source' => 'neutral-commerce-baseline'],
        'catalog' => ['status' => 'public-v1-ready', 'source' => 'f14-be-d'], 'apparel_domain' => ['status' => 'ready', 'source' => 'f14-be-c'],
        'authentication' => ['status' => 'ready-for-commerce'], 'orders' => ['status' => 'commerce-operations-ready'],
        'payments' => ['status' => 'provider-ready-fail-closed'], 'store_operations' => ['status' => 'commerce-operations-ready'],
        'backend_freeze' => ['status' => 'not-ready', 'target_phase' => 'F14-BE-F'],
    ],
    'launch' => ['strategy' => 'fail-closed-until-backend-freeze', 'backend_complete' => false, 'frontend_integrated' => false, 'production_deployed' => false],
];
