<?php

declare(strict_types=1);

namespace App\Services\SalaryCalculation\Strategies;

use App\Models\Employee;
use App\Services\SalaryCalculation\Contracts\SalaryCalculationStrategy;

class HoursOnlyStrategy implements SalaryCalculationStrategy
{
    /**
     * Calculate salary for hours-only type
     */
    public function calculate(Employee $employee, array $summary): array
    {
        $currentMonthDays = now()->daysInMonth;
        $shiftHours = $this->getExpectedHours($employee);
        $hourlyRate = $employee->salary / $currentMonthDays / $shiftHours;
        $dailyRate = $employee->salary / $currentMonthDays;
        $basicSalary = $summary['actual_hours'] * $hourlyRate;
        $overtimeSalary = ($summary['overtime_minutes'] / 60) * $hourlyRate * ($employee->additional_hour_calculation ?? 1.5);
        
        // Calculate Overtime Days Salary
        $overtimeDaysSalary = ($summary['overtime_days'] ?? 0) * $dailyRate * ($employee->additional_day_calculation ?? 1.5);
        
        // Calculate deductions
        $unpaidLeaveDeduction = ($summary['unpaid_leave_days'] ?? 0) * $dailyRate;
        $lateDayCalculation = $employee->late_day_calculation ?? 1.0;
        $absentDaysDeduction = ($summary['absent_days'] ?? 0) * $lateDayCalculation * $dailyRate;
        
        $totalDeductions = $unpaidLeaveDeduction + $absentDaysDeduction;

        return [
            'basic_salary' => round($basicSalary, 2),
            'overtime_salary' => round($overtimeSalary, 2),
            'overtime_days_salary' => round($overtimeDaysSalary, 2),
            'unpaid_leave_deduction' => round($unpaidLeaveDeduction, 2),
            'absent_days_deduction' => round($absentDaysDeduction, 2),
            'total_salary' => round($basicSalary + $overtimeSalary + $overtimeDaysSalary - $totalDeductions, 2),
            'calculation_type' => 'hours_only',
            'hourly_rate' => $hourlyRate,
            'daily_rate' => $dailyRate,
        ];
    }

    /**
     * Get expected working hours for employee
     */
    private function getExpectedHours(Employee $employee): float
    {
        if (!$employee->shift) {
            return 8.0; // Default 8 hours
        }

        $startTime = \Carbon\Carbon::parse($employee->shift->start_time);
        $endTime = \Carbon\Carbon::parse($employee->shift->end_time);

        return $startTime->diffInHours($endTime, false);
    }
}

