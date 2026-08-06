<?php

$frontendOrigins = array_values(array_filter(array_map(
    static fn (string $origin): string => rtrim(trim($origin), '/'),
    explode(',', (string) env('FRONTEND_URLS', env('FRONTEND_URL', 'http://localhost:5173'))),
)));

$boolean = static fn (string $key, bool $default = false): bool => filter_var(
    env($key, $default),
    FILTER_VALIDATE_BOOL,
);

$environment = (string) env('APP_ENV', 'production');
$legacyDefault = $environment !== 'production';
$zarinpalSandbox = $boolean('ZARINPAL_SANDBOX', true);

return [
    'brand' => [
        'name' => env('WINIMI_BRAND_NAME', 'وینیمی بیکری'),
        'name_en' => env('WINIMI_BRAND_NAME_EN', 'Winimi Bakery'),
    ],

    'api' => [
        'version' => '1',
        'contract_version' => '2026-07-20-phase-16',
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
        'packaging_fee_toman' => (int) env('CHECKOUT_PACKAGING_FEE_TOMAN', 0),
        'delivery_methods' => [
            'standard' => [
                'enabled' => $boolean('DELIVERY_STANDARD_ENABLED'),
                'fee_toman' => (int) env('DELIVERY_STANDARD_FEE_TOMAN', 0),
            ],
            'chilled' => [
                'enabled' => $boolean('DELIVERY_CHILLED_ENABLED'),
                'fee_toman' => (int) env('DELIVERY_CHILLED_FEE_TOMAN', 0),
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
        'callback_url' => env(
            'PAYMENT_CALLBACK_URL',
            env('ZARINPAL_CALLBACK_URL', 'http://localhost:5173/payment/result'),
        ),
        'currency' => env('PAYMENT_CURRENCY', 'IRR'),
        'amount_multiplier' => (int) env('PAYMENT_AMOUNT_MULTIPLIER', 10),
        'attempt_ttl_minutes' => (int) env('PAYMENT_ATTEMPT_TTL_MINUTES', 20),
        'timeout_seconds' => (int) env('PAYMENT_TIMEOUT_SECONDS', 10),
        'zarinpal' => [
            'merchant_id' => env('ZARINPAL_MERCHANT_ID'),
            'sandbox' => $zarinpalSandbox,
            'request_url' => env(
                'ZARINPAL_REQUEST_URL',
                $zarinpalSandbox
                    ? 'https://sandbox.zarinpal.com/pg/v4/payment/request.json'
                    : 'https://api.zarinpal.com/pg/v4/payment/request.json',
            ),
            'verify_url' => env(
                'ZARINPAL_VERIFY_URL',
                $zarinpalSandbox
                    ? 'https://sandbox.zarinpal.com/pg/v4/payment/verify.json'
                    : 'https://api.zarinpal.com/pg/v4/payment/verify.json',
            ),
            'start_pay_url' => env(
                'ZARINPAL_START_PAY_URL',
                $zarinpalSandbox
                    ? 'https://sandbox.zarinpal.com/pg/StartPay'
                    : 'https://www.zarinpal.com/pg/StartPay',
            ),
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
        'queue' => [
            'connection' => env('QUEUE_CONNECTION', 'database'),
            'notification_dispatch_command' => 'notifications:dispatch --limit=100',
            'inventory_release_command' => 'inventory:release-expired',
            'scheduler_frequency' => 'every-minute-without-overlap',
        ],
        'cache' => [
            'store' => env('CACHE_STORE', 'database'),
            'prefix' => env('CACHE_PREFIX', 'winimi'),
            'configuration_must_be_cached_in_production' => true,
        ],
        'storage' => [
            'application_disk' => env('FILESYSTEM_DISK', 'local'),
            'media_disk' => env('MEDIA_DISK', 'public'),
            'persistent_media_required_in_production' => true,
            'private_provider_payloads' => true,
        ],
        'backup' => [
            'disk' => env('BACKUP_DISK', 'local'),
            'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 14),
            'daily' => true,
            'restore_verification_required' => true,
        ],
        'legacy' => [
            'production_default' => false,
            'storefront_dependency_allowed' => false,
        ],
    ],

    'legacy' => [
        'enabled' => $boolean('LEGACY_TOOLMASTER_API_ENABLED', $legacyDefault),
        'contract_url' => '/api/system/contracts',
    ],

    'contracts' => [
        'system' => [
            'status' => 'implemented',
            'endpoints' => [
                'GET /api/system/health',
                'GET /api/system/ready',
                'GET /api/system/meta',
                'GET /api/system/contracts',
                'GET /api/system/openapi',
            ],
        ],
        'catalog' => [
            'status' => 'implemented',
            'target_phase' => 11,
            'source' => 'bakery-catalog',
            'endpoints' => [
                'GET /api/catalog/products',
                'GET /api/catalog/products/{slug}',
                'GET /api/catalog/products/{slug}/reviews',
                'GET /api/catalog/categories',
            ],
        ],
        'authentication' => [
            'status' => 'implemented',
            'target_phase' => 12,
            'source' => 'customer-session-otp',
            'endpoints' => [
                'POST /api/auth/otp/request',
                'POST /api/auth/otp/verify',
                'GET /api/auth/me',
                'POST /api/auth/logout',
                'PATCH /api/account/profile',
                'GET /api/account/addresses',
                'POST /api/account/addresses',
                'PUT /api/account/addresses/{addressId}',
                'DELETE /api/account/addresses/{addressId}',
            ],
        ],
        'orders' => [
            'status' => 'implemented',
            'target_phase' => 13,
            'source' => 'transactional-order-reservations',
            'endpoints' => [
                'POST /api/checkout',
                'GET /api/delivery/options',
                'GET /api/account/orders',
                'GET /api/account/orders/{orderId}',
                'POST /api/account/orders/{orderId}/cancel',
            ],
        ],
        'payments' => [
            'status' => 'implemented',
            'target_phase' => 14,
            'source' => 'provider-ready-payment-attempts',
            'activation' => 'disabled-until-external-credentials',
            'endpoints' => [
                'POST /api/orders/{orderId}/payments',
                'POST /api/payments/verify',
                'POST /api/payments/zarinpal/verify',
            ],
        ],
        'store_operations' => [
            'status' => 'implemented',
            'target_phase' => 15,
            'source' => 'delivery-content-reviews-inquiries-notification-outbox',
            'activation' => 'sms-disabled-until-external-credentials',
            'endpoints' => [
                'GET /api/store/settings',
                'GET /api/store/pages/{slug}',
                'GET /api/store/faqs',
                'GET /api/store/gallery',
                'GET /api/store/posts',
                'GET /api/store/posts/{slug}',
                'GET /api/store/cities/{slug}',
                'POST /api/inquiries',
                'POST /api/account/orders/{orderId}/reviews',
            ],
        ],
        'backend_freeze' => [
            'status' => 'ready',
            'target_phase' => 16,
            'source' => 'openapi-error-envelope-pagination-policies-indexes-staging-backup',
            'schema' => '/api/system/openapi',
            'legacy_production_default' => 'disabled',
        ],
    ],

    'launch' => [
        'strategy' => 'complete-internal-work-before-external-activation',
        'roadmap_version' => '2026-07-20-phase-16',
        'internal_gates' => [
            'backend_complete' => [
                'status' => 'ready',
                'target_phase' => 16,
                'contract_version' => '2026-07-20-phase-16',
            ],
            'frontend_integrated' => [
                'status' => 'not-started',
                'target_phase' => 17,
            ],
            'end_to_end_verified' => [
                'status' => 'not-started',
                'target_phase' => 18,
            ],
            'production_deployed' => [
                'status' => 'not-started',
                'target_phase' => 19,
            ],
        ],
        'external_only' => [
            'payment_gateway_credentials' => [
                'status' => 'pending-external',
                'target_phase' => 20,
                'server_keys' => ['ZARINPAL_MERCHANT_ID'],
            ],
            'enamad_badge_code' => [
                'status' => 'pending-external',
                'target_phase' => 20,
                'setting' => 'trust.enamad_badge_code',
            ],
            'sms_provider_credentials' => [
                'status' => 'pending-external',
                'target_phase' => 20,
                'server_keys' => ['KAVENEGAR_API_KEY', 'KAVENEGAR_TEMPLATE'],
            ],
        ],
    ],
];
