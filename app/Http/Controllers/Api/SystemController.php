<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use JsonException;
use Throwable;

class SystemController extends Controller
{
    public function health(): JsonResponse
    {
        return ApiResponse::success([
            'status' => 'ok',
            'service' => 'winimi-bakery-backend',
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
            'service' => 'winimi-bakery-backend',
            'brand' => [
                'name' => config('winimi.brand.name'),
                'nameEn' => config('winimi.brand.name_en'),
            ],
            'apiVersion' => (string) config('winimi.api.version'),
            'contractVersion' => (string) config('winimi.api.contract_version'),
            'roadmapVersion' => (string) config('winimi.launch.roadmap_version'),
            'framework' => [
                'name' => 'Laravel',
                'version' => app()->version(),
            ],
            'legacyApiEnabled' => (bool) config('winimi.legacy.enabled'),
            'openApiUrl' => '/api/system/openapi',
        ]);
    }

    public function contracts(): JsonResponse
    {
        return ApiResponse::success([
            'contractVersion' => (string) config('winimi.api.contract_version'),
            'contracts' => config('winimi.contracts', []),
            'launch' => config('winimi.launch', []),
            'policies' => config('winimi.policies', []),
            'notes' => [
                'مسیرهای /api/v1 متعلق به دامنه قدیمی ToolMaster هستند و در production به‌صورت پیش‌فرض غیرفعال‌اند.',
                'قرارداد بک‌اند در فاز ۱۶ منجمد شده و آماده اتصال فرانت است.',
                'پس از استقرار، فقط کد درگاه، کد اینماد و اطلاعات پنل پیامکی به‌عنوان ورودی خارجی باقی می‌مانند.',
            ],
        ]);
    }

    /** @throws JsonException */
    public function openapi(): JsonResponse
    {
        $path = base_path('docs/openapi.json');
        $document = json_decode(File::get($path), true, flags: JSON_THROW_ON_ERROR);
        $etag = '"'.hash_file('sha256', $path).'"';

        return response()
            ->json($document)
            ->header('Cache-Control', 'public, max-age=300')
            ->header('ETag', $etag);
    }
}
