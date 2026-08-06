<?php

namespace App\Services\Orders;

use App\Enums\DeliveryMethod;
use App\Enums\InventoryReservationStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Exceptions\InventoryUnavailable;
use App\Models\BakeryProductVariant;
use App\Models\Customer;
use App\Models\InventoryReservation;
use App\Models\Order;
use App\Models\OrderInternalNote;
use App\Models\OrderStatusHistory;
use App\Services\Notifications\NotificationOutboxService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

final class OrderLifecycleService
{
    public function __construct(
        private readonly NotificationOutboxService $notifications,
    ) {}

    public function cancelByCustomer(Order $order, Customer $customer): Order
    {
        return DB::transaction(function () use ($order, $customer): Order {
            $locked = Order::query()
                ->whereKey($order->getKey())
                ->where('customer_id', $customer->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $locked->canBeCancelledByCustomer()) {
                throw ValidationException::withMessages([
                    'order' => ['این سفارش در وضعیت فعلی قابل لغو نیست.'],
                ]);
            }

            $this->releaseReservationsLocked(
                $locked,
                InventoryReservationStatus::Released,
                'customer_cancelled',
            );
            $this->transitionLocked(
                $locked,
                OrderStatus::Cancelled,
                'customer',
                $customer->getKey(),
                'سفارش پیش از پرداخت توسط مشتری لغو شد.',
            );
            $locked->forceFill(['cancelled_at' => now()])->save();
            $this->notifications->queueOrder($locked, 'order.cancelled');

            return $locked->fresh(['items', 'reservations', 'paymentAttempts', 'statusHistory']);
        }, 3);
    }

    public function transitionByAdmin(
        Order $order,
        OrderStatus $target,
        ?int $actorId,
        ?string $note = null,
        ?string $trackingCode = null,
    ): Order {
        return DB::transaction(function () use ($order, $target, $actorId, $note, $trackingCode): Order {
            $locked = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $allowed = $this->allowedTargets($locked->status);

            if (! in_array($target, $allowed, true)) {
                throw ValidationException::withMessages([
                    'status' => ['این انتقال وضعیت مجاز نیست.'],
                ]);
            }

            if ($target === OrderStatus::Cancelled) {
                $this->cancelByAdminLocked($locked, $actorId, $note);

                return $locked->fresh([
                    'items',
                    'reservations',
                    'paymentAttempts',
                    'statusHistory',
                    'internalNotes.user',
                ]);
            }

            $this->validateDeliveryTransition($locked, $target, $trackingCode);
            $attributes = match ($target) {
                OrderStatus::Confirmed => ['confirmed_at' => now()],
                OrderStatus::Preparing => ['preparing_at' => now()],
                OrderStatus::Ready => ['ready_at' => now()],
                OrderStatus::Dispatched => [
                    'dispatched_at' => now(),
                    'tracking_code' => trim((string) $trackingCode),
                ],
                OrderStatus::Delivered => ['delivered_at' => now()],
                default => [],
            };
            if ($attributes !== []) {
                $locked->forceFill($attributes)->save();
            }

            $this->transitionLocked(
                $locked,
                $target,
                'admin',
                $actorId,
                $this->nullableNote($note) ?? "وضعیت سفارش به {$target->label()} تغییر کرد.",
            );

            $templateKey = match ($target) {
                OrderStatus::Preparing => 'order.preparing',
                OrderStatus::Ready => 'order.ready',
                OrderStatus::Dispatched => 'order.dispatched',
                OrderStatus::Delivered => 'order.delivered',
                default => null,
            };
            if ($templateKey !== null) {
                $this->notifications->queueOrder($locked, $templateKey);
            }

            return $locked->fresh([
                'items',
                'reservations',
                'paymentAttempts',
                'statusHistory',
                'internalNotes.user',
            ]);
        }, 3);
    }

    public function addInternalNote(Order $order, ?int $actorId, string $note): OrderInternalNote
    {
        $note = trim($note);
        if ($note === '') {
            throw ValidationException::withMessages([
                'note' => ['یادداشت نمی‌تواند خالی باشد.'],
            ]);
        }

        return OrderInternalNote::query()->create([
            'order_id' => $order->getKey(),
            'user_id' => $actorId,
            'note' => $note,
        ]);
    }

    public function expireAwaitingPaymentOrders(): int
    {
        $orderIds = Order::query()
            ->where('status', OrderStatus::AwaitingPayment->value)
            ->whereNotNull('reservation_expires_at')
            ->where('reservation_expires_at', '<=', now())
            ->pluck('id');

        $expired = 0;

        foreach ($orderIds as $orderId) {
            $didExpire = DB::transaction(function () use ($orderId): bool {
                $order = Order::query()->whereKey($orderId)->lockForUpdate()->first();

                if (
                    ! $order
                    || $order->status !== OrderStatus::AwaitingPayment
                    || $order->reservation_expires_at?->isFuture()
                ) {
                    return false;
                }

                $this->releaseReservationsLocked(
                    $order,
                    InventoryReservationStatus::Expired,
                    'payment_timeout',
                );
                $this->transitionLocked(
                    $order,
                    OrderStatus::Expired,
                    'system',
                    null,
                    'مهلت پرداخت و رزرو موجودی پایان یافت.',
                );

                return true;
            }, 3);

            if ($didExpire) {
                $expired++;
            }
        }

        return $expired;
    }

    public function consumeReservations(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $lockedOrder = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $this->consumeReservationsLocked($lockedOrder);
        }, 3);
    }

    /**
     * The caller must already hold a row lock on the order and be inside the
     * same database transaction as the verified payment-attempt update.
     */
    public function markPaidFromVerifiedPaymentLocked(Order $order): void
    {
        if ($order->status === OrderStatus::Paid && $order->payment_status === PaymentStatus::Paid) {
            return;
        }

        if ($order->status !== OrderStatus::AwaitingPayment) {
            throw ValidationException::withMessages([
                'order' => ['این سفارش دیگر در وضعیت قابل پرداخت نیست.'],
            ]);
        }

        if (! $order->reservation_expires_at || $order->reservation_expires_at->isPast()) {
            throw ValidationException::withMessages([
                'order' => ['مهلت رزرو موجودی سفارش پایان یافته است.'],
            ]);
        }

        $this->consumeReservationsLocked($order);
        $order->forceFill([
            'payment_status' => PaymentStatus::Paid,
            'paid_at' => now(),
        ])->save();
        $this->transitionLocked(
            $order,
            OrderStatus::Paid,
            'payment',
            null,
            'پرداخت توسط درگاه تأیید شد و رزرو موجودی به‌صورت اتمیک مصرف شد.',
        );
        $this->notifications->queueOrder($order, 'order.paid');
    }

    private function consumeReservationsLocked(Order $lockedOrder): void
    {
        $reservations = InventoryReservation::query()
            ->where('order_id', $lockedOrder->getKey())
            ->where('status', InventoryReservationStatus::Active->value)
            ->orderBy('variant_id')
            ->lockForUpdate()
            ->get();

        if (
            $reservations->isEmpty()
            || $reservations->contains(
                fn (InventoryReservation $reservation): bool => ! $reservation->isActive(),
            )
        ) {
            throw ValidationException::withMessages([
                'order' => ['رزرو موجودی سفارش معتبر نیست یا منقضی شده است.'],
            ]);
        }

        $variants = BakeryProductVariant::query()
            ->whereIn('id', $reservations->pluck('variant_id'))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($reservations as $reservation) {
            /** @var BakeryProductVariant|null $variant */
            $variant = $variants->get($reservation->variant_id);

            if (! $variant || $variant->stock_quantity < $reservation->quantity) {
                throw new InventoryUnavailable(
                    $variant?->public_id ?? 'deleted',
                    $variant?->name ?? 'نامشخص',
                    $reservation->quantity,
                    $variant?->stock_quantity ?? 0,
                );
            }

            $variant->decrement('stock_quantity', $reservation->quantity);
            $reservation->forceFill([
                'status' => InventoryReservationStatus::Consumed,
                'consumed_at' => now(),
            ])->save();
        }
    }

    private function restockConsumedReservationsLocked(Order $order): void
    {
        $reservations = InventoryReservation::query()
            ->where('order_id', $order->getKey())
            ->where('status', InventoryReservationStatus::Consumed->value)
            ->orderBy('variant_id')
            ->lockForUpdate()
            ->get();

        $variants = BakeryProductVariant::query()
            ->whereIn('id', $reservations->pluck('variant_id'))
            ->orderBy('id')
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        foreach ($reservations as $reservation) {
            /** @var BakeryProductVariant|null $variant */
            $variant = $variants->get($reservation->variant_id);
            if ($variant) {
                $variant->increment('stock_quantity', $reservation->quantity);
            }

            $reservation->forceFill([
                'status' => InventoryReservationStatus::Restocked,
                'restocked_at' => now(),
                'released_at' => now(),
                'release_reason' => 'admin_cancelled_after_payment',
            ])->save();
        }
    }

    private function cancelByAdminLocked(Order $order, ?int $actorId, ?string $note): void
    {
        if ($order->status === OrderStatus::AwaitingPayment) {
            $this->releaseReservationsLocked(
                $order,
                InventoryReservationStatus::Released,
                'admin_cancelled_before_payment',
            );
        } else {
            $this->restockConsumedReservationsLocked($order);
        }

        $order->forceFill([
            'cancelled_at' => now(),
            'admin_cancelled_at' => now(),
        ])->save();
        $this->transitionLocked(
            $order,
            OrderStatus::Cancelled,
            'admin',
            $actorId,
            $this->nullableNote($note) ?? 'سفارش توسط مدیر لغو شد.',
        );
        $this->notifications->queueOrder($order, 'order.cancelled');
    }

    private function releaseReservationsLocked(
        Order $order,
        InventoryReservationStatus $status,
        string $reason,
    ): void {
        $reservations = InventoryReservation::query()
            ->where('order_id', $order->getKey())
            ->where('status', InventoryReservationStatus::Active->value)
            ->lockForUpdate()
            ->get();

        foreach ($reservations as $reservation) {
            $reservation->forceFill([
                'status' => $status,
                'released_at' => now(),
                'release_reason' => $reason,
            ])->save();
        }
    }

    private function transitionLocked(
        Order $order,
        OrderStatus $to,
        string $actorType,
        ?int $actorId,
        string $note,
    ): void {
        $from = $order->status;
        $order->forceFill(['status' => $to])->save();

        OrderStatusHistory::query()->create([
            'order_id' => $order->getKey(),
            'from_status' => $from,
            'to_status' => $to,
            'actor_type' => $actorType,
            'actor_id' => $actorId,
            'note' => $note,
            'created_at' => now(),
        ]);
    }

    /** @return array<int, OrderStatus> */
    private function allowedTargets(OrderStatus $status): array
    {
        return match ($status) {
            OrderStatus::AwaitingPayment => [OrderStatus::Cancelled],
            OrderStatus::Paid => [OrderStatus::Confirmed, OrderStatus::Cancelled],
            OrderStatus::Confirmed => [OrderStatus::Preparing, OrderStatus::Cancelled],
            OrderStatus::Preparing => [OrderStatus::Ready, OrderStatus::Cancelled],
            OrderStatus::Ready => [OrderStatus::Dispatched, OrderStatus::Delivered, OrderStatus::Cancelled],
            OrderStatus::Dispatched => [OrderStatus::Delivered],
            default => [],
        };
    }

    private function validateDeliveryTransition(Order $order, OrderStatus $target, ?string $trackingCode): void
    {
        if ($target === OrderStatus::Dispatched) {
            if ($order->delivery_method === DeliveryMethod::Pickup) {
                throw ValidationException::withMessages([
                    'status' => ['سفارش تحویل حضوری وارد وضعیت ارسال‌شده نمی‌شود.'],
                ]);
            }

            if (trim((string) $trackingCode) === '') {
                throw ValidationException::withMessages([
                    'trackingCode' => ['برای ثبت ارسال، کد پیگیری الزامی است.'],
                ]);
            }
        }

        if (
            $target === OrderStatus::Delivered
            && $order->status === OrderStatus::Ready
            && $order->delivery_method !== DeliveryMethod::Pickup
        ) {
            throw ValidationException::withMessages([
                'status' => ['سفارش ارسالی ابتدا باید وارد وضعیت ارسال‌شده شود.'],
            ]);
        }
    }

    private function nullableNote(?string $note): ?string
    {
        $note = trim((string) $note);

        return $note === '' ? null : $note;
    }
}
