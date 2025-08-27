<?php

namespace Modules\Rentals\Enums;

enum LeaseStatus: int
{
    case ACTIVE = 1;
    case EXPIRED = 2;
    case TERMINATED = 3;
    case PENDING = 4;

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'نشط',
            self::EXPIRED => 'منتهي',
            self::TERMINATED => 'مفسوخ',
            self::PENDING => 'معلق',
        };
    }
}
