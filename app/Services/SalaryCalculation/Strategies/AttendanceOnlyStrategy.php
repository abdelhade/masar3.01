<?php

declare(strict_types=1);

namespace App\Services\SalaryCalculation\Strategies;

use App\Models\Employee;
use App\Services\SalaryCalculation\Contracts\SalaryCalculationStrategy;

class AttendanceOnlyStrategy implements SalaryCalculationStrategy
{
    /**
     * Calculate salary for attendance-only type
     */
    public function calculate(Employee $employee, array $summary): array
    {
        $currentMonthDays = now()->daysInMonth;
        $dailyRate = $employee->salary / $currentMonthDays;
        $attendanceSalary = $summary['present_days'] * $dailyRate;
        
        // Calculate deductions for unpaid leave and absent days
        $unpaidLeaveDeduction = ($summary['unpaid_leave_days'] ?? 0) * $dailyRate;
        $lateDayCalculation = $employee->late_day_calculation ?? 1.0;
        $absentDaysDeduction = ($summary['absent_days'] ?? 0) * $lateDayCalculation * $dailyRate;
        
        $totalDeductions = $unpaidLeaveDeduction + $absentDaysDeduction;

        return [
            'basic_salary' => round($attendanceSalary, 2),
            'overtime_salary' => 0,
            'unpaid_leave_deduction' => round($unpaidLeaveDeduction, 2),
            'absent_days_deduction' => round($absentDaysDeduction, 2),
            'total_salary' => round($attendanceSalary - $totalDeductions, 2),
            'calculation_type' => 'attendance_only',
            'daily_rate' => $dailyRate,
        ];
    }
}

