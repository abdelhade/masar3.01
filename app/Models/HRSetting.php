<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HRSetting extends Model
{
    use HasFactory;

    protected $table = 'hr_settings';

    protected $fillable = [
        'company_max_leave_percentage',
    ];

    protected $casts = [
        'company_max_leave_percentage' => 'decimal:2',
    ];

    /**
     * الحصول على الإعدادات الافتراضية للشركة
     */
    public static function getCompanyDefault(): ?self
    {
        return self::first();
    }

    /**
     * الحصول على النسبة المئوية القصوى للشركة
     */
    public static function getCompanyMaxLeavePercentage(): ?float
    {
        $setting = self::getCompanyDefault();

        return $setting ? (float) $setting->company_max_leave_percentage : null;
    }
}
