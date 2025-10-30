<?php

namespace Modules\Inquiries\Enums;

enum StatusForKon: string
{
    case JOB_IN_HAND = 'Job in hand';
    case TENDER = 'Tender';
    case EXTENSION = 'Extension';
    case NEW_PROJECT = 'New Project';

    public function label(): string
    {
        return match ($this) {
            self::JOB_IN_HAND => 'عمل قيد التنفيذ',
            self::TENDER => 'مناقصة',
            self::EXTENSION => 'تمديد',
            self::NEW_PROJECT => 'مشروع جديد',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::JOB_IN_HAND => 'warning',
            self::TENDER => 'success',
            self::EXTENSION => 'info',
            self::NEW_PROJECT => 'primary',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
