<?php

namespace App\Services\Notifications;

use App\Enums\NotificationChannel;
use App\Enums\NotificationStatus;
use App\Models\NotificationOutbox;
use App\Models\NotificationTemplate;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

final class NotificationOutboxService
{
    public function __construct(
        private readonly SmsProviderManager $providers,
    ) {}

    public function queueOrder(Order $order, string $templateKey, array $payload = []): NotificationOutbox
    {
        return NotificationOutbox::query()->create([
            'customer_id' => $order->customer_id,
            'order_id' => $order->getKey(),
            'channel' => NotificationChannel::Sms,
            'destination' => $order->customer_mobile,
            'template_key' => $templateKey,
            'payload' => [
                'order_number' => $order->order_number,
                'tracking_code' => $order->tracking_code,
                ...$payload,
            ],
            'status' => NotificationStatus::Pending,
            'provider' => strtolower(trim((string) config('winimi.notifications.sms_provider', 'disabled'))),
            'available_at' => now(),
        ]);
    }

    public function dispatchPending(int $limit = 50): int
    {
        if (! $this->providers->ready()) {
            return 0;
        }

        NotificationOutbox::query()
            ->where('status', NotificationStatus::Processing->value)
            ->where('updated_at', '<=', now()->subMinutes(10))
            ->update([
                'status' => NotificationStatus::Pending->value,
                'available_at' => now(),
                'last_error' => 'stale_processing_recovered',
                'updated_at' => now(),
            ]);

        $ids = NotificationOutbox::query()
            ->ready()
            ->orderBy('id')
            ->limit(max(1, min(500, $limit)))
            ->pluck('id');
        $sent = 0;

        foreach ($ids as $id) {
            if ($this->dispatchOne((int) $id)) {
                $sent++;
            }
        }

        return $sent;
    }

    public function dispatchOne(int $id): bool
    {
        if (! $this->providers->ready()) {
            return false;
        }

        $notification = DB::transaction(function () use ($id): ?NotificationOutbox {
            $locked = NotificationOutbox::query()->whereKey($id)->lockForUpdate()->first();
            if (! $locked || $locked->status !== NotificationStatus::Pending) {
                return null;
            }

            $locked->forceFill([
                'status' => NotificationStatus::Processing,
                'attempts' => $locked->attempts + 1,
                'provider' => strtolower(trim((string) config('winimi.notifications.sms_provider', 'disabled'))),
            ])->save();

            return $locked->fresh();
        }, 3);

        if (! $notification) {
            return false;
        }

        try {
            $template = NotificationTemplate::query()
                ->where('key', $notification->template_key)
                ->where('channel', NotificationChannel::Sms->value)
                ->where('is_active', true)
                ->first();
            if (! $template) {
                throw new RuntimeException('قالب اعلان فعال پیدا نشد.');
            }

            $provider = $this->providers->current();
            $providerMessageId = $provider->send(
                $notification->destination,
                $template->render($notification->payload ?? []),
                $template->provider_template,
            );

            DB::transaction(function () use ($notification, $provider, $providerMessageId): void {
                $locked = NotificationOutbox::query()
                    ->whereKey($notification->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();
                $locked->forceFill([
                    'status' => NotificationStatus::Sent,
                    'provider' => $provider->name(),
                    'provider_message_id' => $providerMessageId,
                    'last_error' => null,
                    'sent_at' => now(),
                    'failed_at' => null,
                ])->save();
            }, 3);

            return true;
        } catch (Throwable $exception) {
            $this->recordFailure($notification, $exception);

            return false;
        }
    }

    private function recordFailure(NotificationOutbox $notification, Throwable $exception): void
    {
        DB::transaction(function () use ($notification, $exception): void {
            $locked = NotificationOutbox::query()
                ->whereKey($notification->getKey())
                ->lockForUpdate()
                ->first();
            if (! $locked || $locked->status === NotificationStatus::Sent) {
                return;
            }

            $maximum = max(1, (int) config('winimi.notifications.max_attempts', 5));
            $terminal = $locked->attempts >= $maximum;
            $locked->forceFill([
                'status' => $terminal ? NotificationStatus::Failed : NotificationStatus::Pending,
                'last_error' => mb_substr($exception->getMessage(), 0, 1000),
                'available_at' => $terminal
                    ? null
                    : now()->addSeconds(
                        max(30, (int) config('winimi.notifications.retry_seconds', 60)) * $locked->attempts,
                    ),
                'failed_at' => $terminal ? now() : null,
            ])->save();
        }, 3);
    }
}
