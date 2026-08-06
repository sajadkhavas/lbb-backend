<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AttachApiContext
{
    public function handle(Request $request, Closure $next): Response
    {
        $configuredHeader = (string) config('winimi.api.request_id_header', 'X-Request-ID');
        $providedRequestId = trim((string) $request->header($configuredHeader));
        $requestId = $providedRequestId !== '' && strlen($providedRequestId) <= 100
            ? $providedRequestId
            : (string) Str::uuid();

        app()->instance('winimi.request_id', $requestId);

        $response = $next($request);
        $response->headers->set($configuredHeader, $requestId);
        $response->headers->set('X-API-Version', (string) config('winimi.api.version', '1'));
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
