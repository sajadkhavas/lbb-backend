<?php

namespace App\Providers;

use App\Support\IranianMobile;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Throwable;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {

        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('otp-request', function (Request $request): array {
            try {
                $mobileKey = IranianMobile::hash((string) $request->input('mobile'));
            } catch (Throwable) {
                $mobileKey = hash('sha256', (string) $request->input('mobile'));
            }

            return [
                Limit::perMinute(5)->by('otp-request-ip:'.($request->ip() ?? 'unknown')),
                Limit::perMinute(2)->by('otp-request-mobile:'.$mobileKey),
            ];
        });

        RateLimiter::for('otp-verify', static function (Request $request): array {
            return [
                Limit::perMinute(15)->by('otp-verify-ip:'.($request->ip() ?? 'unknown')),
                Limit::perMinute(8)->by('otp-verify-challenge:'.(string) $request->input('challengeId')),
            ];
        });
    }
}
