<?php

declare(strict_types=1);

namespace Modules\Fleet\Enums;

enum FuelType: string
{
    case GASOLINE = 'gasoline';
    case DIESEL = 'diesel';
    case ELECTRIC = 'electric';

    public function label(): string
    {
        return match ($this) {
            self::GASOLINE => 'بنزين',
            self::DIESEL => 'ديزل',
            self::ELECTRIC => 'كهربائي',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
