<?php

namespace Modules\Inquiries\Enums;

enum InquiryStatus: string
{
    case JOB_IN_HAND = 'Job in hand';

    public function label(): string
    {
        return match ($this) {
            self::JOB_IN_HAND => 'عمل قيد التنفيذ',
        };
    }
}
