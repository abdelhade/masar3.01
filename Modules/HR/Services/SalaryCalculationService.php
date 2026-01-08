<?php

namespace Modules\HR\Services;

use Modules\HR\Models\Attendance;
use Modules\HR\Models\AttendanceProcessing;
use Modules\HR\Models\Employee;
use Modules\HR\Models\Errand;
use Modules\HR\Models\LeaveRequest;
use Modules\HR\Models\WorkPermission;
use Modules\HR\Services\SalaryCalculation\SalaryStrategyFactory;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class SalaryCalculationService
{
    /**
     * Calculate salary for an employee for a specific period
     *
     * This method processes attendance records, calculates working hours, overtime, and deductions,
     * then calculates the salary based on the employee's salary type. Results are cached to improve performance.
     *
     * @param  Employee  $employee  The employee to calculate salary for
     * @param  Carbon  $startDate  Start date of the calculation period (inclusive)
     * @param  Carbon  $endDate  End date of the calculation period (inclusive)
     * @return array{
     *     summary: array{
     *         total_days: int,
     *         working_days: int,
     *         overtime_days: int,
     *         present_days: float,
     *         absent_days: int,
     *         paid_leave_days: int,
     *         unpaid_leave_days: int,
     *         total_hours: float,
     *         actual_hours: float,
     *         overtime_minutes: int,
     *         late_minutes: int,
     *         holiday_days: int
     *     },
     *     salary_data: array{
     *         basic_salary: float,
     *         overtime_salary: float,
     *         overtime_days_salary?: float,
     *         late_hours_deduction?: float,
     *         unpaid_leave_deduction: float,
     *         absent_days_deduction: float,
     *         total_salary: float,
     *         calculation_type: string,
     *         hourly_rate: float,
     *         daily_rate: float
     *     },
     *     details: array<string, array{
     *         date: string,
     *         status: string,
     *         day_type: string,
     *         expected_hours: float,
     *         actual_hours: float,
     *         overtime_minutes: int,
     *         late_minutes: int,
     *         check_in_time: string|null,
     *         check_out_time: string|null,
     *         project_code: string|null,
     *         notes: string|null
     *     }>
     * }
     */
    public function calculateSalary(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        // Check if employee is active
        if ($employee->isInactive()) {
            // Return empty data for inactive employees
            return [
                'summary' => [
                    'total_days' => 0,
                    'working_days' => 0,
                    'overtime_days' => 0,
                    'present_days' => 0,
                    'absent_days' => 0,
                    'paid_leave_days' => 0,
                    'unpaid_leave_days' => 0,
                    'total_hours' => 0,
                    'actual_hours' => 0,
                    'overtime_minutes' => 0,
                    'late_minutes' => 0,
                    'holiday_days' => 0,
                ],
                'salary_data' => [
                    'basic_salary' => 0,
                    'overtime_salary' => 0,
                    'total_salary' => 0,
                    'daily_rate' => 0,
                    'hourly_rate' => 0,
                ],
                'details' => [],
            ];
        }

        // Generate cache key based on employee and period
        $cacheKey = $this->getCacheKey($employee, $startDate, $endDate);

        // Try to get from cache first (TTL: 1 hour)
        return Cache::remember($cacheKey, 3600, function () use ($employee, $startDate, $endDate) {
            // Get attendance records for the period
            $attendances = $this->getAttendanceRecords($employee, $startDate, $endDate);

            // Process attendance and calculate hours
            $processedData = $this->processAttendanceData($employee, $attendances, $startDate, $endDate);

            // Calculate salary based on type
            $salaryData = $this->calculateSalaryByType($employee, $processedData);

            return [
                'summary' => $processedData['summary'],
                'salary_data' => $salaryData,
                'details' => $processedData['daily_details'],
            ];
        });
    }

    /**
     * Generate cache key for salary calculation
     *
     * Cache key format: employee_salary_{employee_id}_{startDate}_{endDate}
     * This allows easy invalidation by period
     */
    private function getCacheKey(Employee $employee, Carbon $startDate, Carbon $endDate): string
    {
        return sprintf(
            'employee_salary_%d_%s_%s',
            $employee->id,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );
    }

    /**
     * Invalidate cache for employee salary calculations
     * Should be called when attendance records are created, updated, or deleted
     *
     * @param  Employee  $employee  The employee whose cache should be invalidated
     * @param  Carbon|null  $startDate  Optional start date for specific period invalidation
     * @param  Carbon|null  $endDate  Optional end date for specific period invalidation
     */
    public function invalidateCache(Employee $employee, ?Carbon $startDate = null, ?Carbon $endDate = null): void
    {
        if ($startDate && $endDate) {
            // Invalidate specific period
            $cacheKey = $this->getCacheKey($employee, $startDate, $endDate);
            Cache::forget($cacheKey);
        } else {
            // Invalidate all cached calculations for this employee
            // We need to invalidate for all possible date ranges, but that's impractical
            // Instead, we invalidate for the current month and previous month (most common cases)
            $now = Carbon::now();
            $currentMonthStart = $now->copy()->startOfMonth();
            $currentMonthEnd = $now->copy()->endOfMonth();
            $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
            $previousMonthEnd = $now->copy()->subMonth()->endOfMonth();

            Cache::forget($this->getCacheKey($employee, $currentMonthStart, $currentMonthEnd));
            Cache::forget($this->getCacheKey($employee, $previousMonthStart, $previousMonthEnd));

            // Also invalidate for the month containing the attendance date if provided
            // This is handled in the Attendance model's booted method
        }
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
            'present_days' => 0,
            'absent_days' => 0,
            'paid_leave_days' => 0,
            'unpaid_leave_days' => 0,
            'overtime_days' => 0,
            'holiday_days' => 0,
            'total_hours' => 0,
            'actual_hours' => 0,
            'overtime_minutes' => 0,
            'late_minutes' => 0,
            'early_hours' => 0,
            'leave_hours' => 0,
        ];
        // Group attendances by date (extract date part only)
        $attendanceByDate = $attendances->groupBy(function ($attendance) {
            return Carbon::parse($attendance->date)->format('Y-m-d');
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
            $dayData = $this->processDayAttendance($employee, $dayAttendances, $date, $dayType, $startDate, $endDate);

            $dailyDetails[$dateStr] = $dayData;

            // Update summary
            if ($dayData['status'] === 'present') {
                $summary['present_days']++;
                $summary['actual_hours'] += $dayData['actual_hours'];
                // Sum minutes directly
                $summary['overtime_minutes'] += $dayData['overtime_minutes'];
                $summary['late_minutes'] += $dayData['late_minutes'];
            } elseif ($dayData['status'] === 'half_day') {
                $summary['present_days'] += 0.5;
                $summary['actual_hours'] += $dayData['actual_hours'];
                $summary['late_minutes'] += $dayData['late_minutes'];
                // No overtime for half day (fixed half day)
            } elseif ($dayData['status'] === 'absent') {
                $summary['absent_days']++;
            } elseif ($dayData['status'] === 'paid_leave') {
                $summary['paid_leave_days']++;
                $summary['actual_hours'] += $dayData['actual_hours']; // Count hours for paid leave
                $summary['present_days']++; // Count as present day for salary calculation
            } elseif ($dayData['status'] === 'unpaid_leave') {
                $summary['unpaid_leave_days']++;
                // Unpaid leave: no hours, no salary, count as absent for salary calculation
                // Don't add to absent_days to differentiate from regular absence
            }
        }

        // Calculate total hours
        $summary['total_hours'] = $summary['actual_hours'] + ($summary['overtime_minutes'] / 60);

        return [
            'summary' => $summary,
            'daily_details' => $dailyDetails,
        ];
    }

    /**
     * Check if a day is a working day for the employee or not and we make it as working day or over time day or holiday
     */
    private function dayType(Employee $employee, int $dayOfWeek, Collection $attendanceByDate, string $dateStr): string
    {
        if (! $employee->shift) {
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
            6 => 'saturday',
        ];

        $currentDay = $dayMap[$dayOfWeek] ?? '';
        $attendanceCount = $attendanceByDate->get($dateStr, collect())->count();

        if (in_array($currentDay, $workingDays)) {
            return 'working_day';
        } elseif (! in_array($currentDay, $workingDays) && $attendanceCount == 0) {
            return 'holiday';
        } elseif (! in_array($currentDay, $workingDays) && $attendanceCount > 0) {
            return 'overtime_day';
        }

        return 'no_shift';
    }

    /**
     * Check if employee has an approved paid leave for a specific date
     */
    private function hasApprovedPaidLeave(Employee $employee, Carbon $date): ?LeaveRequest
    {
        $dateStr = $date->format('Y-m-d');

        $leaveRequest = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->with('leaveType')
            ->first();

        // Check if the leave type is paid
        if ($leaveRequest && $leaveRequest->leaveType && $leaveRequest->leaveType->is_paid) {
            return $leaveRequest;
        }

        return null;
    }

    /**
     * Check if employee has an approved unpaid leave for a specific date
     */
    private function hasApprovedUnpaidLeave(Employee $employee, Carbon $date): ?LeaveRequest
    {
        $dateStr = $date->format('Y-m-d');

        $leaveRequest = LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->with('leaveType')
            ->first();

        // Check if the leave type is unpaid
        if ($leaveRequest && $leaveRequest->leaveType && ! $leaveRequest->leaveType->is_paid) {
            return $leaveRequest;
        }

        return null;
    }

    /**
     * Check if employee has an approved work permission for a specific date
     */
    private function hasApprovedWorkPermission(Employee $employee, Carbon $date): ?WorkPermission
    {
        return WorkPermission::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereDate('date', $date->format('Y-m-d'))
            ->first();
    }

    /**
     * Check if employee has an approved errand for a specific date
     */
    private function hasApprovedErrand(Employee $employee, Carbon $date): ?Errand
    {
        $dateStr = $date->format('Y-m-d');

        return Errand::where('employee_id', $employee->id)
            ->whereNotNull('approved_at')
            ->whereDate('start_date', '<=', $dateStr)
            ->whereDate('end_date', '>=', $dateStr)
            ->first();
    }

    /**
     * Check if employee has exceeded allowed permission days in the period
     */
    private function hasExceededPermissionDays(Employee $employee, Carbon $startDate, Carbon $endDate): bool
    {
        $allowedPermissionDays = $employee->allowed_permission_days ?? 0;
        if ($allowedPermissionDays <= 0) {
            return false;
        }

        $permissionsCount = WorkPermission::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->count();

        return $permissionsCount > $allowedPermissionDays;
    }

    /**
     * Check if employee has exceeded allowed errand days in the period
     */
    private function hasExceededErrandDays(Employee $employee, Carbon $startDate, Carbon $endDate): bool
    {
        if (! $employee->is_errand_allowed) {
            return false;
        }

        $allowedErrandDays = $employee->allowed_errand_days ?? 0;
        if ($allowedErrandDays <= 0) {
            return false;
        }

        $errands = Errand::where('employee_id', $employee->id)
            ->whereNotNull('approved_at')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhereBetween('end_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_date', '<=', $startDate->format('Y-m-d'))
                            ->where('end_date', '>=', $endDate->format('Y-m-d'));
                    });
            })
            ->get();

        $errandDays = 0;
        foreach ($errands as $errand) {
            $errandStart = Carbon::parse($errand->start_date);
            $errandEnd = Carbon::parse($errand->end_date);
            $periodStart = $startDate->copy();
            $periodEnd = $endDate->copy();

            // Calculate overlapping days
            $overlapStart = $errandStart->gt($periodStart) ? $errandStart : $periodStart;
            $overlapEnd = $errandEnd->lt($periodEnd) ? $errandEnd : $periodEnd;

            if ($overlapStart->lte($overlapEnd)) {
                $errandDays += $overlapStart->diffInDays($overlapEnd) + 1;
            }
        }

        return $errandDays > $allowedErrandDays;
    }

    /**
     * Process attendance for a single day
     */
    private function processDayAttendance(Employee $employee, Collection $attendances, Carbon $date, string $dayType, Carbon $startDate, Carbon $endDate): array
    {
        $shift = $employee->shift;

        // Get project_code from first check-in of the day (if exists)
        $checkIns = $attendances->whereIn('type', ['check_in'])->sortBy('time');
        $projectCode = $checkIns->isNotEmpty() ? $checkIns->first()->project_code : null;

        // Check if employee has approved paid or unpaid leave for this day
        $paidLeave = $this->hasApprovedPaidLeave($employee, $date);
        $unpaidLeave = $this->hasApprovedUnpaidLeave($employee, $date);

        if (! $shift) {
            // If employee has paid leave, count as leave (not absent)
            if ($paidLeave) {
                return [
                    'date' => $date->format('Y-m-d'),
                    'status' => 'paid_leave',
                    'day_type' => $dayType,
                    'expected_hours' => 0,
                    'actual_hours' => 0,
                    'overtime_minutes' => 0,
                    'late_minutes' => 0,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'project_code' => $projectCode,
                    'notes' => 'إجازة مدفوعة الأجر: '.($paidLeave->leaveType->name ?? ''),
                ];
            }

            // If employee has unpaid leave, count as unpaid leave (not absent)
            if ($unpaidLeave) {
                return [
                    'date' => $date->format('Y-m-d'),
                    'status' => 'unpaid_leave',
                    'day_type' => $dayType,
                    'expected_hours' => 0,
                    'actual_hours' => 0,
                    'overtime_minutes' => 0,
                    'late_minutes' => 0,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'project_code' => $projectCode,
                    'notes' => 'إجازة غير مدفوعة الأجر: '.($unpaidLeave->leaveType->name ?? ''),
                ];
            }

            return [
                'date' => $date->format('Y-m-d'),
                'status' => 'no_shift',
                'day_type' => $dayType,
                'expected_hours' => 0,
                'actual_hours' => 0,
                'overtime_minutes' => 0,
                'late_minutes' => 0,
                'check_in_time' => null,
                'check_out_time' => null,
                'project_code' => $projectCode,
                'notes' => null,
            ];
        }

        // Separate check-outs (check-ins already separated above)
        $checkOuts = $attendances->whereIn('type', ['check_out'])->sortByDesc('time');

        // Get valid check-in (within range) - pass the date for proper comparison
        $validCheckIn = $this->getFirstValidCheckIn($shift, $checkIns, $date);

        // Get salary type to determine processing logic
        $salaryType = $employee->salary_type;
        $expectedHours = $this->getExpectedHours($employee);
        $actualHours = 0;
        $overtimeHours = 0;
        $lateHours = 0;
        $result = [];
        $notes = [];

        // Handle paid leave first (applies to all salary types)
        if ($paidLeave && ! $validCheckIn && $checkOuts->isEmpty()) {
            return [
                'date' => $date->format('Y-m-d'),
                'status' => 'paid_leave',
                'day_type' => $dayType,
                'expected_hours' => $expectedHours,
                'actual_hours' => $expectedHours,
                'overtime_minutes' => 0,
                'late_minutes' => 0,
                'check_in_time' => null,
                'check_out_time' => null,
                'project_code' => $projectCode,
                'notes' => 'إجازة مدفوعة الأجر: '.($paidLeave->leaveType->name ?? ''),
            ];
        }

        // Handle unpaid leave (applies to all salary types)
        if ($unpaidLeave && ! $validCheckIn && $checkOuts->isEmpty()) {
            return [
                'date' => $date->format('Y-m-d'),
                'status' => 'unpaid_leave',
                'day_type' => $dayType,
                'expected_hours' => $expectedHours,
                'actual_hours' => 0, // No hours for unpaid leave
                'overtime_minutes' => 0,
                'late_minutes' => 0,
                'check_in_time' => null,
                'check_out_time' => null,
                'project_code' => $projectCode,
                'notes' => 'إجازة غير مدفوعة الأجر: '.($unpaidLeave->leaveType->name ?? ''),
            ];
        }

        // Check for approved work permission (after checking leaves)
        $workPermission = $this->hasApprovedWorkPermission($employee, $date);
        if ($workPermission && ! $this->hasExceededPermissionDays($employee, $startDate, $endDate)) {
            // If employee has permission and hasn't exceeded limit, count as full day
            if (! $validCheckIn && $checkOuts->isEmpty()) {
                return [
                    'date' => $date->format('Y-m-d'),
                    'status' => 'permission',
                    'day_type' => $dayType,
                    'expected_hours' => $expectedHours,
                    'actual_hours' => $expectedHours,
                    'overtime_minutes' => 0,
                    'late_minutes' => 0,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'project_code' => $projectCode,
                    'notes' => 'إذن انصراف معتمد',
                ];
            }
        }

        // Check for approved errand (after checking leaves and permissions)
        $errand = $this->hasApprovedErrand($employee, $date);
        if ($errand && $employee->is_errand_allowed && ! $this->hasExceededErrandDays($employee, $startDate, $endDate)) {
            // If employee has errand and hasn't exceeded limit, count as full day
            if (! $validCheckIn && $checkOuts->isEmpty()) {
                return [
                    'date' => $date->format('Y-m-d'),
                    'status' => 'errand',
                    'day_type' => $dayType,
                    'expected_hours' => $expectedHours,
                    'actual_hours' => $expectedHours,
                    'overtime_minutes' => 0,
                    'late_minutes' => 0,
                    'check_in_time' => null,
                    'check_out_time' => null,
                    'project_code' => $projectCode,
                    'notes' => 'مأمورية معتمدة: '.($errand->title ?? ''),
                ];
            }
        }

        // Handle holiday
        if ($dayType == 'holiday' && ! $validCheckIn && $checkOuts->isEmpty()) {
            return [
                'date' => $date->format('Y-m-d'),
                'status' => 'holiday',
                'day_type' => $dayType,
                'expected_hours' => 0,
                'actual_hours' => 0,
                'overtime_minutes' => 0,
                'late_minutes' => 0,
                'check_in_time' => null,
                'check_out_time' => null,
                'project_code' => $projectCode,
                'notes' => null,
            ];
        }

        // Handle late check-in with check-out (Half Day)
        // If there's no valid check-in (rejected because it's late) but there ARE check-ins and check-outs
        // This means the employee checked in late (after ending_check_in) but still has a check-out
        if (! $validCheckIn && $checkIns->isNotEmpty() && $checkOuts->isNotEmpty() && $dayType == 'working_day') {
            $firstCheckIn = $checkIns->first();
            $lastCheckOut = $this->getAnyCheckOut($checkOuts);

            // Calculate late minutes (difference between actual check-in and shift start time)
            $lateMinutes = 0;
            if ($shift && $shift->start_time) {
                $checkInTime = Carbon::parse($date->format('Y-m-d').' '.$firstCheckIn->time);
                $shiftStart = Carbon::parse($date->format('Y-m-d').' '.$shift->start_time);

                if ($checkInTime->gt($shiftStart)) {
                    // Calculate late time in total minutes
                    $lateMinutes = $shiftStart->diffInMinutes($checkInTime);
                }
            }

            return [
                'date' => $date->format('Y-m-d'),
                'status' => 'half_day',
                'day_type' => $dayType,
                'expected_hours' => $expectedHours,
                'actual_hours' => $expectedHours / 2, // Half day hours
                'overtime_minutes' => 0,
                'late_minutes' => $lateMinutes,
                'check_in_time' => $firstCheckIn->time,
                'check_out_time' => $lastCheckOut->time,
                'project_code' => $projectCode,
                'notes' => 'نصف يوم (تأخير عن موعد البصمة)',
            ];
        }

        // Handle absence (no check-in and no check-out, and no leave)
        // Only count as absent if there's no paid or unpaid leave
        if (! $validCheckIn && $checkOuts->isEmpty() && $dayType == 'working_day' && ! $paidLeave && ! $unpaidLeave) {
            return [
                'date' => $date->format('Y-m-d'),
                'status' => 'absent',
                'day_type' => $dayType,
                'expected_hours' => $expectedHours,
                'actual_hours' => 0,
                'overtime_minutes' => 0,
                'late_minutes' => 0,
                'check_in_time' => null,
                'check_out_time' => null,
                'project_code' => $projectCode,
                'notes' => null,
            ];
        }

        // Process based on salary type
        switch ($salaryType) {
            case 'ساعات عمل فقط':
                // 1. بصمة دخول في النطاق + أي بصمة خروج → يحسب اليوم كاملاً بدون أي إضافي
                // التأخير لا يُحسب لهذا النوع
                if ($validCheckIn && $checkOuts->isNotEmpty()) {
                    $anyCheckOut = $this->getAnyCheckOut($checkOuts);
                    $checkInTime = Carbon::parse($validCheckIn->time);
                    $checkOutTime = Carbon::parse($anyCheckOut->time);

                    return [
                        'date' => $date->format('Y-m-d'),
                        'status' => 'present',
                        'day_type' => $dayType,
                        'expected_hours' => $expectedHours,
                        'actual_hours' => $expectedHours, // يوم كامل فقط بدون إضافي
                        'overtime_minutes' => 0, // لا يوجد إضافي
                        'late_minutes' => 0, // لا يُحسب التأخير لهذا النوع
                        'check_in_time' => $validCheckIn->time,
                        'check_out_time' => $anyCheckOut->time,
                        'project_code' => $projectCode,
                        'notes' => null,
                    ];
                }
                break;

            case 'ساعات عمل و إضافي يومى':
                // 2. يحسب اليوم (8 ساعات) + الإضافي إذا كانت بصمة الخروج بعد ending_check_out
                // الراتب الإضافي للساعات بعد end_time (ولكن فقط إذا كانت بصمة الخروج بعد ending_check_out)
                if ($validCheckIn && $checkOuts->isNotEmpty()) {
                    $anyCheckOut = $this->getAnyCheckOut($checkOuts);
                    // Use the date from $date parameter to create proper Carbon objects
                    $checkInTime = Carbon::parse($date->format('Y-m-d').' '.$validCheckIn->time);
                    $checkOutTime = Carbon::parse($date->format('Y-m-d').' '.$anyCheckOut->time);

                    // Clamp check-in time to shift start time if it's early
                    if ($shift && $shift->start_time) {
                        $shiftStart = Carbon::parse($date->format('Y-m-d').' '.$shift->start_time);
                        if ($checkInTime->lt($shiftStart)) {
                            $checkInTime = $shiftStart;
                        }
                    }

                    // Basic hours = expected hours
                    $basicHours = $expectedHours;

                    // Overtime = hours after end_time, but only if check-out is after ending_check_out
                    $isAfterEndingCheckOut = $this->isCheckOutAfterEndingCheckOut($shift, $checkOutTime);
                    $overtimeMinutes = 0;

                    if ($isAfterEndingCheckOut) {
                        // Calculate overtime after end_time (official shift end time)
                        $overtimeMinutes = $this->getOvertimeMinutesAfterEndTime($shift, $checkOutTime);
                    }

                    // Calculate late hours (pass original check-in time if needed, but here we use clamped/original for calculation?)
                    // Wait, calculateLateHours uses the passed checkInTime. If we clamped it, late hours would be 0.
                    // But late hours should be calculated based on ACTUAL check-in.
                    // However, if check-in is EARLY, late hours is 0 anyway.
                    // So passing clamped time (which is shift start) results in 0 late hours, which is correct.
                    // BUT, calculateLateHours might need the original time if we wanted to track "early arrival" (not needed here).
                    // Actually, let's pass the original time to calculateLateHours just in case, but re-parse it or use validCheckIn->time.
                    // Ah, I overwrote $checkInTime.
                    // Let's re-parse for late hours or just use the logic that early check-in = 0 late hours.

                    $lateMinutes = $this->calculateLateMinutes($shift, Carbon::parse($date->format('Y-m-d').' '.$validCheckIn->time));

                    return [
                        'date' => $date->format('Y-m-d'),
                        'status' => 'present',
                        'day_type' => $dayType,
                        'expected_hours' => $expectedHours,
                        'actual_hours' => $basicHours,
                        'overtime_minutes' => $overtimeMinutes > 0 ? $overtimeMinutes : 0,
                        'late_minutes' => $lateMinutes > 0 ? $lateMinutes : 0,
                        'check_in_time' => $validCheckIn->time,
                        'check_out_time' => $anyCheckOut->time,
                        'project_code' => $projectCode,
                        'notes' => null,
                    ];
                }
                break;

            case 'ساعات عمل و إضافي للمده':
                // 3. نفس "إضافي يومى" - يحسب اليوم (8 ساعات) + الإضافي إذا كانت بصمة الخروج بعد ending_check_out
                // الراتب الإضافي للساعات بعد end_time (ولكن فقط إذا كانت بصمة الخروج بعد ending_check_out)
                if ($validCheckIn && $checkOuts->isNotEmpty()) {
                    $anyCheckOut = $this->getAnyCheckOut($checkOuts);
                    // Use the date from $date parameter to create proper Carbon objects
                    $checkInTime = Carbon::parse($date->format('Y-m-d').' '.$validCheckIn->time);
                    $checkOutTime = Carbon::parse($date->format('Y-m-d').' '.$anyCheckOut->time);

                    // Clamp check-in time to shift start time if it's early
                    if ($shift && $shift->start_time) {
                        $shiftStart = Carbon::parse($date->format('Y-m-d').' '.$shift->start_time);
                        if ($checkInTime->lt($shiftStart)) {
                            $checkInTime = $shiftStart;
                        }
                    }

                    // Basic hours = expected hours
                    $basicHours = $expectedHours;

                    // Overtime = hours after end_time, but only if check-out is after ending_check_out
                    $isAfterEndingCheckOut = $this->isCheckOutAfterEndingCheckOut($shift, $checkOutTime);
                    $overtimeMinutes = 0;

                    if ($isAfterEndingCheckOut) {
                        // Calculate overtime after end_time (official shift end time)
                        $overtimeMinutes = $this->getOvertimeMinutesAfterEndTime($shift, $checkOutTime);
                    }

                    $lateMinutes = $this->calculateLateMinutes($shift, Carbon::parse($date->format('Y-m-d').' '.$validCheckIn->time));

                    return [
                        'date' => $date->format('Y-m-d'),
                        'status' => 'present',
                        'day_type' => $dayType,
                        'expected_hours' => $expectedHours,
                        'actual_hours' => $basicHours,
                        'overtime_minutes' => $overtimeMinutes > 0 ? $overtimeMinutes : 0,
                        'late_minutes' => $lateMinutes > 0 ? $lateMinutes : 0,
                        'check_in_time' => $validCheckIn->time,
                        'check_out_time' => $anyCheckOut->time,
                        'project_code' => $projectCode,
                        'notes' => null,
                    ];
                }
                break;

            case 'حضور فقط':
                // 4. في حالة وجود بصمة دخول داخل نطاق الدخول يحسب يوم كامل حتى لو ما فيش بصمة خروج
                // التأخير لا يُحسب لهذا النوع
                if ($validCheckIn) {
                    $checkInTime = Carbon::parse($validCheckIn->time);

                    // Get check-out if exists (for display purposes)
                    $anyCheckOut = $checkOuts->isNotEmpty() ? $this->getAnyCheckOut($checkOuts) : null;

                    return [
                        'date' => $date->format('Y-m-d'),
                        'status' => 'present',
                        'day_type' => $dayType,
                        'expected_hours' => $expectedHours,
                        'actual_hours' => $expectedHours, // Full day
                        'overtime_minutes' => 0,
                        'late_minutes' => 0, // لا يُحسب التأخير لهذا النوع
                        'check_in_time' => $validCheckIn->time,
                        'check_out_time' => $anyCheckOut ? $anyCheckOut->time : null,
                        'project_code' => $projectCode,
                        'notes' => $anyCheckOut ? null : 'يوم كامل بناءً على بصمة الدخول فقط',
                    ];
                }
                break;

            case 'إنتاج فقط':
                // 5. البصمة ملهاش لازمة في الراتب لأن الراتب بناء على الإنتاج وليس ساعات الحضور
                // Always count as full day if there's any check-in
                if ($checkIns->isNotEmpty()) {
                    $firstCheckIn = $checkIns->first();
                    $anyCheckOut = $checkOuts->isNotEmpty() ? $this->getAnyCheckOut($checkOuts) : null;

                    return [
                        'date' => $date->format('Y-m-d'),
                        'status' => 'present',
                        'day_type' => $dayType,
                        'expected_hours' => $expectedHours,
                        'actual_hours' => $expectedHours, // Full day for display
                        'overtime_minutes' => 0,
                        'late_minutes' => 0,
                        'check_in_time' => $firstCheckIn->time,
                        'check_out_time' => $anyCheckOut ? $anyCheckOut->time : null,
                        'project_code' => $projectCode,
                        'notes' => 'الراتب بناءً على الإنتاج وليس ساعات الحضور',
                    ];
                }
                break;

            default:
                // Default behavior (same as 'ساعات عمل فقط')
                // التأخير لا يُحسب لهذا النوع
                if ($validCheckIn && $checkOuts->isNotEmpty()) {
                    $anyCheckOut = $this->getAnyCheckOut($checkOuts);
                    $checkInTime = Carbon::parse($date->format('Y-m-d').' '.$validCheckIn->time);
                    $checkOutTime = Carbon::parse($date->format('Y-m-d').' '.$anyCheckOut->time);

                    // Clamp check-in time to shift start time if it's early
                    if ($shift && $shift->start_time) {
                        $shiftStart = Carbon::parse($date->format('Y-m-d').' '.$shift->start_time);
                        if ($checkInTime->lt($shiftStart)) {
                            $checkInTime = $shiftStart;
                        }
                    }

                    $actualHours = $checkInTime->diffInHours($checkOutTime, false);
                    $overtimeHours = 0; // Initialize to 0

                    if ($actualHours > $expectedHours) {
                        $overtimeHours = $actualHours - $expectedHours;
                    }

                    return [
                        'date' => $date->format('Y-m-d'),
                        'status' => 'present',
                        'day_type' => $dayType,
                        'expected_hours' => $expectedHours,
                        'actual_hours' => $actualHours - $overtimeHours,
                        'overtime_minutes' => $overtimeHours > 0 ? $overtimeHours * 60 : 0,
                        'late_minutes' => 0, // لا يُحسب التأخير لهذا النوع
                        'check_in_time' => $validCheckIn->time,
                        'check_out_time' => $anyCheckOut->time,
                        'project_code' => $projectCode,
                        'notes' => null,
                    ];
                }
                break;
        }

        // Default case: no valid check-in or no attendance
        return [
            'date' => $date->format('Y-m-d'),
            'status' => 'absent',
            'day_type' => $dayType,
            'expected_hours' => $expectedHours,
            'actual_hours' => 0,
            'overtime_minutes' => 0,
            'late_minutes' => 0,
            'check_in_time' => null,
            'check_out_time' => null,
            'project_code' => $projectCode,
            'notes' => null,
        ];
    }

    /**
     * Get expected working hours for employee
     */
    private function getExpectedHours(Employee $employee): float
    {
        if (! $employee->shift) {
            return 8.0; // Default 8 hours
        }

        $startTime = Carbon::parse($employee->shift->start_time);
        $endTime = Carbon::parse($employee->shift->end_time);

        return $startTime->diffInHours($endTime, false);
    }

    /**
     * Calculate late minutes based on actual start time
     * Returns total minutes of lateness (0 if not late)
     */
    private function calculateLateMinutes($shift, Carbon $checkInTime): int
    {
        if (! $shift || ! $shift->start_time) {
            return 0;
        }
        $dateStr = $checkInTime->format('Y-m-d');
        $shiftStart = Carbon::parse($dateStr.' '.$shift->start_time);
        if ($checkInTime->gt($shiftStart)) {
            return $shiftStart->diffInMinutes($checkInTime);
        }

        return 0;
    }

    /**
     * Calculate overtime minutes after official shift end time
     */
    private function getOvertimeMinutesAfterEndTime($shift, Carbon $checkOutTime): int
    {
        if (! $shift || ! $shift->end_time) {
            return 0;
        }
        $dateStr = $checkOutTime->format('Y-m-d');
        $shiftEnd = Carbon::parse($dateStr.' '.$shift->end_time);
        if ($checkOutTime->gt($shiftEnd)) {
            return $shiftEnd->diffInMinutes($checkOutTime);
        }

        return 0;
    }

    /**
     * Get first valid check-in within the allowed range
     */
    private function getFirstValidCheckIn($shift, Collection $checkIns, Carbon $date)
    {
        if (! $shift->beginning_check_in || ! $shift->ending_check_in) {
            // If no range is defined, return first check-in
            return $checkIns->first();
        }

        // Use the date from parameter for proper comparison
        $dateStr = $date->format('Y-m-d');
        $endingCheckIn = Carbon::parse($dateStr.' '.$shift->ending_check_in);

        foreach ($checkIns as $checkIn) {
            $checkInTime = Carbon::parse($dateStr.' '.$checkIn->time);

            // Check if check-in is before or equal to ending_check_in
            // We accept early check-ins (before beginning_check_in) as requested
            if ($checkInTime->lte($endingCheckIn)) {
                return $checkIn;
            }
        }

        return null;
    }

    /**
     * Get last valid check-out within the allowed range
     * If no check-out is within the range but there's a check-out after ending_check_out,
     * accept it as valid (likely overtime)
     */
    private function getLastValidCheckOut($shift, Collection $checkOuts, Carbon $date)
    {
        if (! $shift->beginning_check_out || ! $shift->ending_check_out) {
            // If no range is defined, return last check-out (first in sorted descending collection)
            return $checkOuts->first();
        }

        // Use the date from parameter for proper comparison
        $dateStr = $date->format('Y-m-d');
        $beginningCheckOut = Carbon::parse($dateStr.' '.$shift->beginning_check_out);
        $endingCheckOut = Carbon::parse($dateStr.' '.$shift->ending_check_out);

        // First, try to find a check-out within the allowed range
        foreach ($checkOuts as $checkOut) {
            $checkOutTime = Carbon::parse($dateStr.' '.$checkOut->time);

            // Check if check-out is within the allowed range
            if ($checkOutTime->between($beginningCheckOut, $endingCheckOut)) {
                return $checkOut;
            }
        }

        // If no check-out within range, check for check-outs after ending_check_out
        // These are likely overtime check-outs and should be accepted
        foreach ($checkOuts as $checkOut) {
            $checkOutTime = Carbon::parse($dateStr.' '.$checkOut->time);

            // Accept check-out if it's after ending_check_out (overtime)
            // But reject if it's before beginning_check_out (too early, likely wrong)
            if ($checkOutTime->gt($endingCheckOut)) {
                return $checkOut;
            }
        }

        // If no valid check-out found (including overtime), return null
        return null;
    }

    /**
     * Get any check-out (regardless of range) - used for certain salary types
     */
    private function getAnyCheckOut(Collection $checkOuts)
    {
        return $checkOuts->first(); // Last check-out (sorted descending, so first is last)
    }

    /**
     * Get overtime hours after ending_check_out
     */
    private function getOvertimeHoursAfterEndingCheckOut($shift, Carbon $checkOutTime): float
    {
        if (! $shift->ending_check_out) {
            return 0;
        }

        $endingCheckOut = Carbon::parse($shift->ending_check_out);

        if ($checkOutTime->gt($endingCheckOut)) {
            // Calculate overtime in hours.minutes format (e.g., 1.30 for 1 hour 30 minutes)
            $totalMinutes = $endingCheckOut->diffInMinutes($checkOutTime);
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;

            return $hours + ($minutes / 100);
        }

        return 0;
    }

    /**
     * Get overtime hours after end_time (official shift end time)
     * This is used for calculating overtime salary
     */
    private function getOvertimeHoursAfterEndTime($shift, Carbon $checkOutTime): float
    {
        if (! $shift->end_time) {
            return 0;
        }

        // Use the same date as checkOutTime for proper comparison
        $endTime = Carbon::parse($checkOutTime->format('Y-m-d').' '.$shift->end_time);

        if ($checkOutTime->gt($endTime)) {
            // Calculate overtime in hours.minutes format (e.g., 1.30 for 1 hour 30 minutes)
            $totalMinutes = $endTime->diffInMinutes($checkOutTime);
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;

            return $hours + ($minutes / 100);
        }

        return 0;
    }

    /**
     * Check if check-out is after ending_check_out
     */
    private function isCheckOutAfterEndingCheckOut($shift, Carbon $checkOutTime): bool
    {
        if (! $shift->ending_check_out) {
            return false;
        }

        // Use the same date as checkOutTime for proper comparison
        $endingCheckOut = Carbon::parse($checkOutTime->format('Y-m-d').' '.$shift->ending_check_out);

        return $checkOutTime->gt($endingCheckOut);
    }

    /**
     * Check if period is full month (30 days)
     */
    private function isFullMonthPeriod(Carbon $startDate, Carbon $endDate): bool
    {
        $daysDiff = $startDate->diffInDays($endDate) + 1;

        return $daysDiff >= 30;
    }

    /**
     * Calculate late hours based on actual start time
     * Only calculates late hours if:
     * 1. Check-in is within the check-in range (beginning_check_in to ending_check_in)
     * 2. Check-in is after the actual start time (start_time)
     * Note: Late hours are calculated from start_time, not from allowed time (start_time + allowed_late_minutes)
     */
    private function calculateLateHours($shift, Carbon $checkInTime): float
    {
        // Use the same date as checkInTime for proper comparison
        $dateStr = $checkInTime->format('Y-m-d');

        // Get the actual shift start time
        $shiftStart = Carbon::parse($dateStr.' '.$shift->start_time);
        $allowedLateMinutes = $shift->allowed_late_minutes ?? 0;
        $allowedCheckInTime = $shiftStart->copy()->addMinutes($allowedLateMinutes);

        // If no beginning_check_in is defined, check if check-in is after start_time
        if (! $shift->beginning_check_in) {
            // If check-in is after the start time, calculate late hours from start_time
            if ($checkInTime->gt($shiftStart)) {
                // Calculate late hours in hours.minutes format
                $totalMinutes = $shiftStart->diffInMinutes($checkInTime);
                $hours = floor($totalMinutes / 60);
                $minutes = $totalMinutes % 60;

                return $hours + ($minutes / 100);
            }

            return 0;
        }

        // The valid range is from beginning_check_in to ending_check_in (inclusive)
        $beginningCheckIn = Carbon::parse($dateStr.' '.$shift->beginning_check_in);
        $endingCheckIn = Carbon::parse($dateStr.' '.$shift->ending_check_in);

        // Check if check-in is within the valid range (beginning_check_in to ending_check_in inclusive)
        if (! $checkInTime->between($beginningCheckIn, $endingCheckIn)) {
            // Check-in is outside the valid range, no late hours calculation
            return 0;
        }

        // Check-in is within the valid range, now check if it's after the start time
        // Only calculate late hours if check-in is after the allowed time (start_time + allowed_late_minutes)
        // This ensures we only count lateness when it exceeds the grace period
        if ($checkInTime->gt($allowedCheckInTime)) {
            // Calculate late hours from actual start time (not from allowed time)
            // This gives the total late hours from the official start time
            // Calculate in hours.minutes format
            $totalMinutes = $shiftStart->diffInMinutes($checkInTime);
            $hours = floor($totalMinutes / 60);
            $minutes = $totalMinutes % 60;

            return $hours + ($minutes / 100);
        }

        // Check-in is within the range and before or equal to allowed time, no late hours
        return 0;
    }

    /**
     * Calculate salary based on employee's salary type using Strategy Pattern
     */
    private function calculateSalaryByType(Employee $employee, array $processedData): array
    {
        $summary = $processedData['summary'];
        $strategy = SalaryStrategyFactory::create($employee);

        return $strategy->calculate($employee, $summary);
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
        $overtimeSalary = ($summary['overtime_minutes'] / 60) * $hourlyRate * ($employee->additional_hour_calculation ?? 1.5);

        // Calculate Overtime Days Salary
        // Overtime days (e.g. holidays/weekends) should be paid based on daily rate * day multiplier
        $overtimeDaysSalary = ($summary['overtime_days'] ?? 0) * $dailyRate * ($employee->additional_day_calculation ?? 1.5);

        // Calculate deductions
        // Unpaid leave: full day deduction for each unpaid leave day
        $unpaidLeaveDeduction = ($summary['unpaid_leave_days'] ?? 0) * $dailyRate;

        // Absent days: deduction based on late_day_calculation (e.g., if late_day_calculation = 2, 1 absent day = 2 days deduction)
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
     * Calculate salary for hours with daily overtime
     */
    private function calculateHoursWithDailyOvertimeSalary(Employee $employee, array $summary): array
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
        // late_hour_calculation: how many hours each late hour is counted as (e.g., 0.5 means 1 late hour = 0.5 hours deduction)
        $lateHourCalculation = $employee->late_hour_calculation ?? 1.0;
        $lateHoursDeduction = ($summary['late_minutes'] / 60) * $lateHourCalculation;
        $lateDeductionAmount = $lateHoursDeduction * $hourlyRate;

        // Calculate deductions for unpaid leave and absent days
        // Unpaid leave: full day deduction for each unpaid leave day
        $unpaidLeaveDeduction = ($summary['unpaid_leave_days'] ?? 0) * $dailyRate;

        // Absent days: deduction based on late_day_calculation (e.g., if late_day_calculation = 2, 1 absent day = 2 days deduction)
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
     * Calculate salary for hours with period overtime
     */
    private function calculateHoursWithPeriodOvertimeSalary(Employee $employee, array $summary): array
    {

        $currentMonthDays = now()->daysInMonth;
        $shiftHours = $this->getExpectedHours($employee);
        $hourlyRate = $employee->salary / $currentMonthDays / $shiftHours;
        $dailyRate = $employee->salary / $currentMonthDays;
        $basicSalary = $summary['actual_hours'] * $hourlyRate;

        // Calculate period overtime (total overtime for the period)
        // FIX: Use additional_hour_calculation for HOURS, not day calculation
        $periodOvertimeRate = $employee->additional_hour_calculation ?? 1.5;
        $overtimeSalary = ($summary['overtime_minutes'] / 60) * $hourlyRate * $periodOvertimeRate;

        // Calculate Overtime Days Salary
        $overtimeDaysSalary = ($summary['overtime_days'] ?? 0) * $dailyRate * ($employee->additional_day_calculation ?? 1.5);

        // Calculate late hours deduction
        // late_hour_calculation: how many hours each late hour is counted as (e.g., 0.5 means 1 late hour = 0.5 hours deduction)
        $lateHourCalculation = $employee->late_hour_calculation ?? 1.0;
        $lateHoursDeduction = ($summary['late_minutes'] / 60) * $lateHourCalculation;
        $lateDeductionAmount = $lateHoursDeduction * $hourlyRate;

        // Calculate deductions for unpaid leave and absent days
        // Unpaid leave: full day deduction for each unpaid leave day
        $unpaidLeaveDeduction = ($summary['unpaid_leave_days'] ?? 0) * $dailyRate;

        // Absent days: deduction based on late_day_calculation (e.g., if late_day_calculation = 2, 1 absent day = 2 days deduction)
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
            'calculation_type' => 'hours_with_period_overtime',
            'hourly_rate' => $hourlyRate,
            'daily_rate' => $dailyRate,
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

        // Calculate deductions for unpaid leave and absent days
        // Unpaid leave: full day deduction for each unpaid leave day
        $unpaidLeaveDeduction = ($summary['unpaid_leave_days'] ?? 0) * $dailyRate;

        // Absent days: deduction based on late_day_calculation (e.g., if late_day_calculation = 2, 1 absent day = 2 days deduction)
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

    /**
     * Calculate salary for production-only type
     */
    private function calculateProductionOnlySalary(Employee $employee, array $summary): array
    {
        // الراتب لا يُحسب بناءً على الساعات، بل بناءً على الإنتاج
        // يتم حساب الراتب لاحقاً بناءً على معادلة خاصة بالإنتاج
        // هنا نعيد 0 لكل القيم المتعلقة بالراتب

        return [
            'basic_salary' => 0,
            'overtime_salary' => 0,
            'total_salary' => 0,
            'calculation_type' => 'production_only',
            'hourly_rate' => 0,
            'daily_rate' => 0,
        ];
    }

    /**
     * Get salary calculation history for employee
     */
    public function getSalaryHistory(Employee $employee, int $limit = 10): Collection
    {
        return AttendanceProcessing::with(['employee', 'department'])
            ->latest()
            ->limit($limit)
            ->get();
    }

    /**
     * Convert hours.minutes format to total minutes
     * Example: 1.35 (1h 35m) => 95 minutes
     */
    private function hoursMinutesToMinutes(float $value): int
    {
        if (! $value || $value == 0) {
            return 0;
        }

        $hours = floor($value);
        $minutes = round(($value - $hours) * 100);

        return ($hours * 60) + $minutes;
    }

    /**
     * Convert total minutes to hours.minutes format
     * Example: 95 minutes => 1.35 (1h 35m)
     */
    private function minutesToHoursMinutes(int $totalMinutes): float
    {
        if ($totalMinutes == 0) {
            return 0;
        }

        $hours = floor($totalMinutes / 60);
        $minutes = $totalMinutes % 60;

        return $hours + ($minutes / 100);
    }
}
