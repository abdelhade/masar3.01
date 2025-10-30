<?php

namespace Modules\Inquiries\Enums;

enum InquiryStatus: string
{
    case JOB_IN_HAND = 'Job in hand';
    case TENDER = 'tender';

    public function label(): string
    {
        return match ($this) {
            self::JOB_IN_HAND => 'عمل قيد التنفيذ',
            self::TENDER => 'مناقصة',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::JOB_IN_HAND => 'primary',
            self::TENDER => 'info',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
