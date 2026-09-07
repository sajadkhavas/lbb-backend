<?php

namespace App\Enums;

enum DeliveryMethod: string
{
    /** @deprecated Kept only for backward-compatible pre-P4 requests. */
    case Standard = 'standard';

    /** @deprecated Kept only for backward-compatible pre-P4 requests. */
    case Pickup = 'pickup';

    case ImmediateCourier = 'immediate_courier';
    case Tipax = 'tipax';
    case Decapost = 'decapost';
    case ExpressPost = 'express_post';

    public function label(): string
    {
        return match ($this) {
            self::Standard => 'ارسال معمولی (قدیمی)',
            self::Pickup => 'تحویل حضوری (قدیمی)',
            self::ImmediateCourier => 'پیک فوری کرج و تهران',
            self::Tipax => 'تیپاکس — پس‌کرایه',
            self::Decapost => 'دکاپست — پس‌کرایه',
            self::ExpressPost => 'پست پیشتاز',
        };
    }

    public function requiresAddress(): bool
    {
        return $this !== self::Pickup;
    }

    public function isOfficialP4Method(): bool
    {
        return match ($this) {
            self::ImmediateCourier, self::Tipax, self::Decapost, self::ExpressPost => true,
            self::Standard, self::Pickup => false,
        };
    }
}
