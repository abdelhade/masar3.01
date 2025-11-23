<?php

namespace Modules\MyResources\Enums;

enum AssignmentType: string
{
    case CURRENT = 'current';
    case UPCOMING = 'upcoming';
    case PAST = 'past';

    public function label(): string
    {
        return match ($this) {
            self::CURRENT => 'حالي',
            self::UPCOMING => 'قادم',
            self::PAST => 'منتهي',
        };
    }

    public static function options(): array
    {
        return collect(self::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()])->toArray();
    }
}

