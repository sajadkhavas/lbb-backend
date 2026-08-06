<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Unpaid = 'unpaid';
    case Pending = 'pending';
    case Paid = 'paid';
    case Failed = 'failed';
    case Refunded = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::Unpaid => 'پرداخت‌نشده',
            self::Pending => 'در حال بررسی',
            self::Paid => 'پرداخت‌شده',
            self::Failed => 'ناموفق',
            self::Refunded => 'بازگشت‌داده‌شده',
        };
    }
}
