<?php

namespace App\Enums;

enum InvoiceStatus: int
{
    case DELIVERED = 0;
    case RETURNED = 1;
    case SHIPPING = 2;
    case PACKING = 3;
    case CANCELLED = 4;
    case VIEWED = 5;

    public function translate(): string
    {
        return match ($this) {
            self::DELIVERED => 'تم التسليم',
            self::RETURNED => 'مرتجع شحن',
            self::SHIPPING => 'في الشحن',
            self::PACKING => 'في التعبئة',
            self::CANCELLED => 'ملغي',
            self::VIEWED => 'تم الاطلاع',
        };
    }
}
