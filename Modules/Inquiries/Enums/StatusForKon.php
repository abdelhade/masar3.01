<?php

namespace Modules\Inquiries\Enums;

enum StatusForKon: string
{
    case JOB_IN_HAND = 'Job in hand';
    case TENDER = 'Tender';
    case EXTENSION = 'Extension';

    public function label(): string
    {
        return match ($this) {
            self::JOB_IN_HAND => 'عمل قيد التنفيذ',
            self::TENDER => 'مناقصة',
            self::EXTENSION => 'تمديد',
        };
    }
}
