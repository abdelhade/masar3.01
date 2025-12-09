<?php

declare(strict_types=1);

namespace App\Services\SalaryCalculation\Strategies;

use App\Models\Employee;
use App\Services\SalaryCalculation\Contracts\SalaryCalculationStrategy;

class FixedWithFlexibleHoursStrategy implements SalaryCalculationStrategy
{
    /**
     * Calculate salary for fixed + flexible hours type
     * Formula: Fixed Salary + (Hours Worked × Hourly Wage)
     *
     * Note: This strategy doesn't rely on attendance records.
     * Hours should be entered manually via FlexibleSalaryProcessing.
     */
    public function calculate(Employee $employee, array $summary): array
    {
        // Fixed salary (monthly)
        $fixedSalary = (float) ($employee->salary ?? 0);

        // Flexible hours and hourly wage
        // These should come from FlexibleSalaryProcessing, not from attendance
        $hoursWorked = (float) ($summary['flexible_hours'] ?? 0);
        $hourlyWage = (float) ($employee->flexible_hourly_wage ?? 0);

        // Calculate flexible salary component
        $flexibleSalary = $hoursWorked * $hourlyWage;

        // Total salary
        $totalSalary = $fixedSalary + $flexibleSalary;

        return [
            'basic_salary' => round($fixedSalary, 2),
            'flexible_salary' => round($flexibleSalary, 2),
            'hours_worked' => round($hoursWorked, 2),
            'hourly_wage' => round($hourlyWage, 2),
            'total_salary' => round($totalSalary, 2),
            'calculation_type' => 'fixed_with_flexible_hours',
            'daily_rate' => 0, // Not applicable for this type
            'hourly_rate' => $hourlyWage,
        ];
    }
}
