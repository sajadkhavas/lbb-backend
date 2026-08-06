<?php

namespace App\Http\Middleware;

use App\Enums\ApiErrorCode;
use App\Support\ApiResponse;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MarkLegacyApi
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('winimi.legacy.enabled', false)) {
            return ApiResponse::error(
                'این مسیر قدیمی غیرفعال است.',
                404,
                code: ApiErrorCode::LegacyApiDisabled,
            );
        }

        $response = $next($request);
        $response->headers->set('Deprecation', 'true');
        $response->headers->set('X-Winimi-Legacy-Domain', 'toolmaster');
        $response->headers->set(
            'Link',
            sprintf('<%s>; rel="deprecation"', config('winimi.legacy.contract_url', '/api/system/contracts')),
        );

        return $response;
    }
}
