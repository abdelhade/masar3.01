<?php

declare(strict_types=1);

namespace App\Services\SalaryCalculation;

use App\Models\Employee;
use App\Services\SalaryCalculation\Contracts\SalaryCalculationStrategy;
use App\Services\SalaryCalculation\Strategies\AttendanceOnlyStrategy;
use App\Services\SalaryCalculation\Strategies\FixedWithFlexibleHoursStrategy;
use App\Services\SalaryCalculation\Strategies\HoursOnlyStrategy;
use App\Services\SalaryCalculation\Strategies\HoursWithDailyOvertimeStrategy;
use App\Services\SalaryCalculation\Strategies\HoursWithPeriodOvertimeStrategy;
use App\Services\SalaryCalculation\Strategies\ProductionOnlyStrategy;

class SalaryStrategyFactory
{
    /**
     * Create appropriate strategy based on employee's salary type
     */
    public static function create(Employee $employee): SalaryCalculationStrategy
    {
        return match ($employee->salary_type) {
            'ساعات عمل فقط' => new HoursOnlyStrategy,
            'ساعات عمل و إضافي يومى' => new HoursWithDailyOvertimeStrategy,
            'ساعات عمل و إضافي للمده' => new HoursWithPeriodOvertimeStrategy,
            'حضور فقط' => new AttendanceOnlyStrategy,
            'إنتاج فقط' => new ProductionOnlyStrategy,
            'ثابت + ساعات عمل مرن' => new FixedWithFlexibleHoursStrategy,
            default => new HoursOnlyStrategy, // Default fallback
        };
    }
}
