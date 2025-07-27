<?php

namespace Modules\CRM\Enums;

enum TaskPriorityEnum: string
{
    case HIGH = 'عاجلة';
    case MEDIUM = 'متوسطة';
    case LOW = 'غير عاجلة';

    public function label(): string
    {
        return $this->value; // بيرجع التسمية بالعربي
    }

    public function color(): string
    {
        return match ($this) {
            self::HIGH => 'danger',     // أحمر
            self::MEDIUM => 'warning',  // أصفر
            self::LOW => 'success',     // أخضر
        };
    }
}
