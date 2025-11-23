<?php

namespace Modules\MyResources\Enums;

enum DocumentType: string
{
    case IMAGE = 'image';
    case CERTIFICATE = 'certificate';
    case MANUAL = 'manual';
    case WARRANTY = 'warranty';
    case INVOICE = 'invoice';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::IMAGE => 'صورة',
            self::CERTIFICATE => 'شهادة',
            self::MANUAL => 'دليل استخدام',
            self::WARRANTY => 'ضمان',
            self::INVOICE => 'فاتورة',
            self::OTHER => 'أخرى',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray();
    }
}

