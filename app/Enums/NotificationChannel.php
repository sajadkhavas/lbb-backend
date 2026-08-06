<?php

namespace App\Enums;

enum NotificationChannel: string
{
    case Sms = 'sms';

    public function label(): string
    {
        return match ($this) {
            self::Sms => 'پیامک',
        };
    }
}
