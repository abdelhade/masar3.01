<?php

namespace Modules\Maintenance\Enums;

enum MaintenanceStatus: int
{
    case PENDING = 1;
    case IN_PROGRESS = 2;
    case COMPLETED = 3;
    case CANCELLED = 4;

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'قيد الانتظار',
            self::IN_PROGRESS => 'جاري الصيانة',
            self::COMPLETED => 'تم الانتهاء',
            self::CANCELLED => 'تم الإلغاء',
        };
    }
    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'badge bg-warning',
            self::IN_PROGRESS => 'badge bg-info',
            self::COMPLETED => 'badge bg-success',
            self::CANCELLED => 'badge bg-danger',
        };
    }
}
