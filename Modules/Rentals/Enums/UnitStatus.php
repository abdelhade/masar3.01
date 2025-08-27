<?php

namespace Modules\Rentals\Enums;

enum UnitStatus: int
{
    case AVAILABLE = 1;
    case RENTED = 2;
    case MAINTENANCE = 3;

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'متاحة',
            self::RENTED => 'مؤجرة',
            self::MAINTENANCE => 'صيانة',
        };
    }
}
