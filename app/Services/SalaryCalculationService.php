<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceProcessing;
use App\Models\AttendanceProcessingDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class SalaryCalculationService
{
    /**
     * Calculate salary for an employee for a specific period
     */
    public function calculateSalary(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        return DB::transaction(function () use ($employee, $startDate, $endDate) {
            
            // Get attendance records for the period
            $attendances = $this->getAttendanceRecords($employee, $startDate, $endDate);
            // dd($attendances);
            // Process attendance and calculate hours
            $processedData = $this->processAttendanceData($employee, $attendances, $startDate, $endDate);
            // dd($processedData);
            // Calculate salary based on type
            $salaryData = $this->calculateSalaryByType($employee, $processedData);
            
            return [
                'summary' => $processedData['summary'],
                'salary_data' => $salaryData,
                'details' => $processedData['daily_details']
            ];
        });
    }

    /**
     * Get attendance records for employee in date range
     */
    private function getAttendanceRecords(Employee $employee, Carbon $startDate, Carbon $endDate): Collection
    {
        // Get the actual records for the date range
        $records = Attendance::where('employee_id', $employee->id)
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('date')
            ->orderBy('time')
            ->get();
        return $records;
    }

    /**
     * Process attendance data and calculate working hours
     */
    private function processAttendanceData(Employee $employee, Collection $attendances, Carbon $startDate, Carbon $endDate): array
    {
        $dailyDetails = [];
        $summary = [
            'total_days' => 0,
            'working_days' => 0,
            'overtime_days' => 0,
            'present_days' => 0,
            'absent_days' => 0,
            'total_hours' => 0,
            'actual_hours' => 0,
            'overtime_hours' => 0,
            'late_hours' => 0,
            'holiday_days' => 0,
        ];

        // Group attendances by date (extract date part only)
        $attendanceByDate = $attendances->groupBy(function ($attendance) {
            return \Carbon\Carbon::parse($attendance->date)->format('Y-m-d');
        });
        // dd($attendanceByDate);
        // Process each day in the period
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dateStr = $date->format('Y-m-d');
            $dayOfWeek = $date->dayOfWeek; // 0=Sunday, 1=Monday, etc.
            
            $summary['total_days']++;

            
            // Check if it's a working day or overtime day or holiday
            $dayType = $this->dayType($employee, $dayOfWeek, $attendanceByDate, $dateStr);
            if ($dayType == 'holiday') {
                $summary['holiday_days']++;
            }
            if ($dayType == 'working_day') {
                $summary['working_days']++;
            }
            if ($dayType == 'overtime_day') {
                $summary['overtime_days']++;
            }

            // Get attendance for this day
            $dayAttendances = $attendanceByDate->get($dateStr, collect());
            // dd($dayAttendances);
            // Process day attendance
            $dayData = $this->processDayAttendance($employee, $dayAttendances, $date, $dayType);
            
            $dailyDetails[$dateStr] = $dayData;
            
            // Update summary
            if ($dayData['status'] === 'present') {
                $summary['present_days']++;
                $summary['actual_hours'] += $dayData['actual_hours'];
                $summary['overtime_hours'] += $dayData['overtime_hours'];
                $summary['late_hours'] += $dayData['late_hours'];
            } elseif ($dayData['status'] === 'absent') {
                $summary['absent_days']++;
            }
        }

        $summary['total_hours'] = $summary['working_days'] * $this->getExpectedHours($employee);


        return [
            'summary' => $summary,
            'daily_details' => $dailyDetails
        ];
    }

    /**
     * Check if a day is a working day for the employee or not and we make it as working day or over time day or holiday
     */
    private function dayType(Employee $employee, int $dayOfWeek, Collection $attendanceByDate, string $dateStr): string
    {
        if (!$employee->shift) {
            return 'no_shift';
        }

        $workingDays = json_decode($employee->shift->days, true) ?? [];
        
        // Convert day of week to match the stored format
        $dayMap = [ 
            0 => 'sunday',
            1 => 'monday', 
            2 => 'tuesday',
            3 => 'wednesday',
            4 => 'thursday',
            5 => 'friday',
            6 => 'saturday'
        ];

        $currentDay = $dayMap[$dayOfWeek] ?? '';
        $attendanceCount = $attendanceByDate->get($dateStr, collect())->count();
        

        
        if (in_array($currentDay, $workingDays)) {
            return 'working_day';
        } elseif (!in_array($currentDay, $workingDays) && $attendanceCount == 0) {
            return 'holiday';
        } elseif (!in_array($currentDay, $workingDays) && $attendanceCount > 0) {
            return 'overtime_day';
        }
        
        return 'no_shift';
    }

    /**
     * Process attendance for a single day
     */
    private function processDayAttendance(Employee $employee, Collection $attendances, Carbon $date, string $dayType): array
    {
        $shift = $employee->shift;
        
        
        if (!$shift) {
            return [
                'date' => $date->format('Y-m-d'),
                'status' => 'no_shift',
                'day_type' => $dayType,
                'expected_hours' => 0,
                'actual_hours' => 0,
                'overtime_hours' => 0,
                'late_hours' => 0,
                'check_in_time' => null,
                'check_out_time' => null,
                
            ];
        }

        // Separate check-ins and check-outs (handle both English and Arabic)
        $checkIns = $attendances->whereIn('type', 'check_in');
        $checkOuts = $attendances->whereIn('type', 'check_out');
        // dd($checkIns, $checkOuts);
        $firstCheckIn = $checkIns->sortBy('time')->first();
        $lastCheckOut = $checkOuts->sortByDesc('time')->first();

        $actualHours = 0;
        $overtimeHours = 0;
        $lateHours = 0;
        $result = [];

        if ($firstCheckIn && $lastCheckOut && ($dayType == 'working_day' || $dayType == 'overtime_day')) {
            $checkInTime = Carbon::parse($firstCheckIn->time);
            $checkOutTime = Carbon::parse($lastCheckOut->time);
            
            
            // Calculate actual working hours
            $actualHours = $checkInTime->diffInHours($checkOutTime, false);
            
            // Calculate expected hours for this day
            $expectedHours = $this->getExpectedHours($employee);
            
            
            // Calculate overtime
            if ($actualHours > $expectedHours) {
                $overtimeHours = $actualHours - $expectedHours;
            }
            
            // Calculate late hours (if check-in is after shift start)
            $shiftStart = Carbon::parse($shift->start_time);
            if ($checkInTime->gt($shiftStart)) {
                $lateHours = $checkInTime->diffInHours($shiftStart, false);
            }
            
            $result = [
                'date' => $date->format('Y-m-d'),
                'status' => 'present',
                'day_type' => $dayType,
                'expected_hours' => $expectedHours,
                'actual_hours' => $actualHours - $overtimeHours,
                'overtime_hours' => $overtimeHours > 0 ? $overtimeHours : 0,
                'late_hours' => $lateHours > 0 ? $lateHours : 0,
                'check_in_time' => $firstCheckIn ? $firstCheckIn->time : null,
                'check_out_time' => $lastCheckOut ? $lastCheckOut->time : null,
            ];
        } elseif (!$firstCheckIn && !$lastCheckOut && $dayType == 'working_day') {
            $result = [
                'date' => $date->format('Y-m-d'),
                'status' => 'absent',
                'day_type' => $dayType,
                'expected_hours' => $this->getExpectedHours($employee),
                'actual_hours' => 0,
                'overtime_hours' => 0,
                'late_hours' => 0,
                'check_in_time' => null,
                'check_out_time' => null,
            ];
        } elseif (!$firstCheckIn && !$lastCheckOut && $dayType == 'holiday') {
            $result = [
                'date' => $date->format('Y-m-d'),
                'status' => 'holiday',
                'day_type' => $dayType,
                'expected_hours' => 0,
                'actual_hours' => 0,
                'overtime_hours' => 0,
                'late_hours' => 0,
                'check_in_time' => null,
                'check_out_time' => null,
            ];
        }
        elseif((!$firstCheckIn || !$lastCheckOut) && $dayType == 'working_day'){
            $result = [
                'date' => $date->format('Y-m-d'),
                'status' => 'present',
                'day_type' => $dayType,
                'expected_hours' => $this->getExpectedHours($employee),
                'actual_hours' => $this->getExpectedHours($employee)/2, // نصف يوم حاله عدم وجود بصمه للدخول او الخروج
                'overtime_hours' => 0,
                'late_hours' => 0,
                'check_in_time' => $firstCheckIn ? $firstCheckIn->time : null,
                'check_out_time' => $lastCheckOut ? $lastCheckOut->time : null,
            ];
        } else {
            // Default case for any unhandled scenarios
            $result = [
                'date' => $date->format('Y-m-d'),
                'status' => 'unknown',
                'day_type' => $dayType,
                'expected_hours' => 0,
                'actual_hours' => 0,
                'overtime_hours' => 0,
                'late_hours' => 0,
                'check_in_time' => $firstCheckIn ? $firstCheckIn->time : null,
                'check_out_time' => $lastCheckOut ? $lastCheckOut->time : null,
            ];
        }
        
        
        return $result;
    }

    /**
     * Get expected working hours for employee
     */
    private function getExpectedHours(Employee $employee): float
    {
        if (!$employee->shift) {
            return 8.0; // Default 8 hours
        }

        $startTime = Carbon::parse($employee->shift->start_time);
        $endTime = Carbon::parse($employee->shift->end_time);
        
        return $startTime->diffInHours($endTime, false);
    }

    /**
     * Calculate salary based on employee's salary type
     */
    private function calculateSalaryByType(Employee $employee, array $processedData): array
    {
        $summary = $processedData['summary'];
        $salaryType = $employee->salary_type;
        
        
        switch ($salaryType) {
            case 'ساعات عمل فقط':
                return $this->calculateHoursOnlySalary($employee, $summary);
                
            case 'ساعات عمل و إضافي يومى':
                return $this->calculateHoursWithDailyOvertimeSalary($employee, $summary);
                
            case 'ساعات عمل و إضافي للمده':
                return $this->calculateHoursWithPeriodOvertimeSalary($employee, $summary);
                
            case 'حضور فقط':
                return $this->calculateAttendanceOnlySalary($employee, $summary);
                
            case 'إنتاج فقط':
                return $this->calculateProductionOnlySalary($employee, $summary);
                
            default:
                return $this->calculateHoursOnlySalary($employee, $summary);
        }
    }

    /**
     * Calculate salary for hours-only type
     */
    private function calculateHoursOnlySalary(Employee $employee, array $summary): array
    {
        $currentMonthDays = now()->daysInMonth;
        $shiftHours = $this->getExpectedHours($employee);
        $hourlyRate = $employee->salary / $currentMonthDays / $shiftHours;
        $dailyRate = $employee->salary / $currentMonthDays;
        $basicSalary = $summary['actual_hours'] * $hourlyRate;
        $overtimeSalary = $summary['overtime_hours'] * $hourlyRate * ($employee->additional_hour_calculation ?? 1.5);
        // dd($summary['actual_hours'],$summary['overtime_hours'],$hourlyRate,$dailyRate,$basicSalary,$overtimeSalary);
        
        
        return [
            'basic_salary' => round($basicSalary, 2),
            'overtime_salary' => round($overtimeSalary, 2),
            'total_salary' => round($basicSalary + $overtimeSalary, 2),
            'calculation_type' => 'hours_only',
            'hourly_rate' => $hourlyRate,
            'daily_rate' => $dailyRate
        ];
    }

    /**
     * Calculate salary for hours with daily overtime
     */
    private function calculateHoursWithDailyOvertimeSalary(Employee $employee, array $summary): array
    {
        $currentMonthDays = now()->daysInMonth;
        $shiftHours = $this->getExpectedHours($employee);
        $hourlyRate = $employee->salary / $currentMonthDays / $shiftHours;
        $basicSalary = $summary['actual_hours'] * $hourlyRate;
        $overtimeSalary = $summary['overtime_hours'] * $hourlyRate * ($employee->additional_hour_calculation ?? 1.5);
        
        return [
            'basic_salary' => round($basicSalary, 2),
            'overtime_salary' => round($overtimeSalary, 2),
            'total_salary' => round($basicSalary + $overtimeSalary, 2),
            'calculation_type' => 'hours_with_daily_overtime',
            'hourly_rate' => $hourlyRate
        ];
    }

    /**
     * Calculate salary for hours with period overtime
     */
    private function calculateHoursWithPeriodOvertimeSalary(Employee $employee, array $summary): array
    {
       
        $currentMonthDays = now()->daysInMonth;
        $shiftHours = $this->getExpectedHours($employee);
        $hourlyRate = $employee->salary / $currentMonthDays / $shiftHours;
        $basicSalary = $summary['actual_hours'] * $hourlyRate;
        
        // Calculate period overtime (total overtime for the period)
        $periodOvertimeRate = $employee->additional_day_calculation ?? 1.0;
        $overtimeSalary = $summary['overtime_hours'] * $hourlyRate * $periodOvertimeRate;
        
        return [
            'basic_salary' => round($basicSalary, 2),
            'overtime_salary' => round($overtimeSalary, 2),
            'total_salary' => round($basicSalary + $overtimeSalary, 2),
            'calculation_type' => 'hours_with_period_overtime',
            'hourly_rate' => $hourlyRate
        ];
    }

    /**
     * Calculate salary for attendance-only type
     */
    private function calculateAttendanceOnlySalary(Employee $employee, array $summary): array
    {
        $currentMonthDays = now()->daysInMonth;
        $dailyRate = $employee->salary / $currentMonthDays;
        $attendanceSalary = $summary['present_days'] * $dailyRate;
        
        
        return [
            'basic_salary' => round($attendanceSalary, 2),
            'overtime_salary' => 0,
            'total_salary' => round($attendanceSalary, 2),
            'calculation_type' => 'attendance_only',
            'daily_rate' => $dailyRate
        ];
    }

    /**
     * Calculate salary for production-only type
     */
    private function calculateProductionOnlySalary(Employee $employee, array $summary): array
    {
        // This would need to be integrated with production data
        // For now, return basic calculation
        $currentMonthDays = now()->daysInMonth;
        $shiftHours = $this->getExpectedHours($employee);
        $hourlyRate = $employee->salary / $currentMonthDays / $shiftHours;
        $basicSalary = $summary['actual_hours'] * $hourlyRate;
        
        return [
            'basic_salary' => round($basicSalary, 2),
            'overtime_salary' => 0,
            'total_salary' => round($basicSalary, 2),
            'calculation_type' => 'production_only',
            'hourly_rate' => $hourlyRate
        ];
    }

    /**
     * Get salary calculation history for employee
     */
    public function getSalaryHistory(Employee $employee, int $limit = 10): Collection
    {
        return AttendanceProcessing::where('employee_id', $employee->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
} 