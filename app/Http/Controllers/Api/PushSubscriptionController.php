<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PushSubscription;
use App\Services\Notifications\WebPushService;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

final class PushSubscriptionController extends Controller
{
    public function configuration(WebPushService $webPush): JsonResponse
    {
        return ApiResponse::success([
            'enabled' => $webPush->ready(),
            'publicKey' => $webPush->ready() ? $webPush->publicKey() : null,
        ]);
    }

    public function index(): JsonResponse
    {
        $customer = $this->customer();

        return ApiResponse::success([
            'activeCount' => $customer->pushSubscriptions()->active()->count(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048', 'url', 'starts_with:https://'],
            'keys' => ['required', 'array'],
            'keys.p256dh' => ['required', 'string', 'max:512'],
            'keys.auth' => ['required', 'string', 'max:256'],
            'contentEncoding' => ['nullable', 'string', Rule::in(['aes128gcm', 'aesgcm'])],
        ]);

        $customer = $this->customer();
        $endpoint = trim($validated['endpoint']);
        $hash = PushSubscription::endpointHash($endpoint);

        $subscription = PushSubscription::query()->firstOrNew(['endpoint_hash' => $hash]);
        $subscription->forceFill([
            'customer_id' => $customer->getKey(),
            'endpoint' => $endpoint,
            'p256dh' => $validated['keys']['p256dh'],
            'auth_token' => $validated['keys']['auth'],
            'content_encoding' => $validated['contentEncoding'] ?? 'aes128gcm',
            'user_agent' => mb_substr((string) $request->userAgent(), 0, 512),
            'last_seen_at' => now(),
            'revoked_at' => null,
        ])->save();

        return ApiResponse::success([
            'subscriptionId' => $subscription->public_id,
            'activeCount' => $customer->pushSubscriptions()->active()->count(),
        ], 'اعلان‌های این دستگاه فعال شد.', $subscription->wasRecentlyCreated ? 201 : 200);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string', 'max:2048', 'url'],
        ]);
        $customer = $this->customer();

        PushSubscription::query()
            ->where('customer_id', $customer->getKey())
            ->where('endpoint_hash', PushSubscription::endpointHash($validated['endpoint']))
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now(), 'updated_at' => now()]);

        return ApiResponse::success([
            'activeCount' => $customer->pushSubscriptions()->active()->count(),
        ], 'اعلان‌های این دستگاه غیرفعال شد.');
    }

    public function test(WebPushService $webPush): JsonResponse
    {
        if (! $webPush->ready()) {
            return ApiResponse::error('سرویس Web Push هنوز در محیط سرور فعال نشده است.', 503);
        }

        $result = $webPush->sendToCustomer($this->customer(), [
            'title' => 'اعلان‌های LBB فعال است',
            'body' => 'این اعلان آزمایشی فقط برای تأیید اتصال همین دستگاه ارسال شد.',
            'icon' => '/icons/icon-192.png',
            'badge' => '/icons/icon-192.png',
            'url' => '/account',
            'tag' => 'lbb-web-push-test',
            'data' => ['kind' => 'test'],
        ]);

        if ($result['sent'] < 1) {
            return ApiResponse::error('اعلان آزمایشی به هیچ اشتراک فعالی تحویل نشد.', 503, [], [
                'revoked' => $result['revoked'],
                'failed' => $result['failed'],
            ]);
        }

        return ApiResponse::success($result, 'اعلان آزمایشی ارسال شد.');
    }

    private function customer(): Customer
    {
        /** @var Customer $customer */
        $customer = Auth::guard('customer')->user();

        return $customer;
    }
}
