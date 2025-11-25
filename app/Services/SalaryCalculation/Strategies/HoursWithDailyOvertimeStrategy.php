<?php

declare(strict_types=1);

namespace App\Services\SalaryCalculation\Strategies;

use App\Models\Employee;
use App\Services\SalaryCalculation\Contracts\SalaryCalculationStrategy;

class HoursWithDailyOvertimeStrategy implements SalaryCalculationStrategy
{
    /**
     * Calculate salary for hours with daily overtime
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

        // Calculate late hours deduction
        $lateHourCalculation = $employee->late_hour_calculation ?? 1.0;
        $lateHoursDeduction = ($summary['late_minutes'] / 60) * $lateHourCalculation;
        $lateDeductionAmount = $lateHoursDeduction * $hourlyRate;
        
        // Calculate deductions for unpaid leave and absent days
        $unpaidLeaveDeduction = ($summary['unpaid_leave_days'] ?? 0) * $dailyRate;
        $lateDayCalculation = $employee->late_day_calculation ?? 1.0;
        $absentDaysDeduction = ($summary['absent_days'] ?? 0) * $lateDayCalculation * $dailyRate;
        
        $totalDeductions = $lateDeductionAmount + $unpaidLeaveDeduction + $absentDaysDeduction;

        return [
            'basic_salary' => round($basicSalary, 2),
            'overtime_salary' => round($overtimeSalary, 2),
            'overtime_days_salary' => round($overtimeDaysSalary, 2),
            'late_hours_deduction' => round($lateDeductionAmount, 2),
            'unpaid_leave_deduction' => round($unpaidLeaveDeduction, 2),
            'absent_days_deduction' => round($absentDaysDeduction, 2),
            'total_salary' => round($basicSalary + $overtimeSalary + $overtimeDaysSalary - $totalDeductions, 2),
            'calculation_type' => 'hours_with_daily_overtime',
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

