<?php

namespace App\Http\Resources;

use App\Models\OrderStatusHistory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->public_id,
            'number' => $this->order_number,
            'status' => $this->status->value,
            'statusLabel' => $this->status->label(),
            'paymentStatus' => $this->payment_status->value,
            'paymentStatusLabel' => $this->payment_status->label(),
            'delivery' => [
                'method' => $this->delivery_method->value,
                'methodLabel' => $this->delivery_method->label(),
                'requiresCooling' => $this->requires_cooling,
                'feeToman' => $this->delivery_fee_toman,
                'zone' => $this->resource->relationLoaded('deliveryZone') && $this->deliveryZone
                    ? [
                        'id' => $this->deliveryZone->public_id,
                        'name' => $this->deliveryZone->name,
                    ]
                    : null,
            ],
            'totals' => [
                'subtotalToman' => $this->subtotal_toman,
                'deliveryFeeToman' => $this->delivery_fee_toman,
                'packagingFeeToman' => $this->packaging_fee_toman,
                'discountToman' => $this->discount_total_toman,
                'grandTotalToman' => $this->grand_total_toman,
            ],
            'itemCount' => $this->item_count,
            'preparationTimeDays' => $this->preparation_time_days,
            'preparation' => [
                'minDays' => $this->preparation_time_days,
                'maxDays' => max($this->preparation_time_days, $this->preparation_max_days),
            ],
            'recipient' => [
                'fullName' => $this->customer_name,
                'mobile' => $this->customer_mobile,
                'province' => $this->province,
                'city' => $this->city,
                'address' => $this->address,
                'postalCode' => $this->postal_code,
                'notes' => $this->notes,
            ],
            'fulfillment' => [
                'trackingCode' => $this->tracking_code,
                'confirmedAt' => $this->confirmed_at?->toIso8601String(),
                'preparingAt' => $this->preparing_at?->toIso8601String(),
                'readyAt' => $this->ready_at?->toIso8601String(),
                'dispatchedAt' => $this->dispatched_at?->toIso8601String(),
                'deliveredAt' => $this->delivered_at?->toIso8601String(),
            ],
            'items' => OrderItemResource::collection($this->whenLoaded('items'))->resolve($request),
            'payments' => $this->resource->relationLoaded('paymentAttempts')
                ? PaymentAttemptResource::collection($this->paymentAttempts)->resolve($request)
                : [],
            'timeline' => $this->resource->relationLoaded('statusHistory')
                ? $this->statusHistory->map(fn (OrderStatusHistory $history): array => [
                    'from' => $history->from_status?->value,
                    'to' => $history->to_status->value,
                    'label' => $history->to_status->label(),
                    'createdAt' => $history->created_at?->toIso8601String(),
                ])->values()->all()
                : [],
            'reservationExpiresAt' => $this->reservation_expires_at?->toIso8601String(),
            'canCancel' => $this->canBeCancelledByCustomer(),
            'placedAt' => $this->placed_at?->toIso8601String(),
            'paidAt' => $this->paid_at?->toIso8601String(),
            'cancelledAt' => $this->cancelled_at?->toIso8601String(),
            'createdAt' => $this->created_at?->toIso8601String(),
        ];
    }
}
