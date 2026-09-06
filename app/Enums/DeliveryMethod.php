<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    case Standard = 'standard';
    case Pickup = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'ارسال معمولی',
            self::Pickup => 'تحویل حضوری',
        };
    }

    public function requiresAddress(): bool
    {
        return $this !== self::Pickup;
    }
}
