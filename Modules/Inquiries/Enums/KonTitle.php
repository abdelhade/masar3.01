<?php

namespace Modules\Inquiries\Enums;

enum KonTitle: string
{
    case MAIN_PILING_CONTRACTOR = 'Main Piling Contractor';
    case SUB_PILING_CONTRACTOR = 'Sub-Piling Contractor';

    public function label(): string
    {
        return match ($this) {
            self::MAIN_PILING_CONTRACTOR => 'مقاول رئيسي للـ Piling',
            self::SUB_PILING_CONTRACTOR => 'مقاول فرعي للـ Piling',
        };
    }
}
