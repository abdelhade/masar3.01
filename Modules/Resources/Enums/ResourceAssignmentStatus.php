<?php

namespace Modules\MyResources\Enums;

enum ResourceAssignmentStatus: string
{
    case SCHEDULED = 'scheduled';
    case ACTIVE = 'active';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'مجدول',
            self::ACTIVE => 'نشط',
            self::COMPLETED => 'مكتمل',
            self::CANCELLED => 'ملغي',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::SCHEDULED => 'info',
            self::ACTIVE => 'success',
            self::COMPLETED => 'secondary',
            self::CANCELLED => 'danger',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray();
    }
}

