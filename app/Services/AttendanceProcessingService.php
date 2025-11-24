<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\AttendanceProcessing;
use App\Models\AttendanceProcessingDetail;
use App\Models\Department;
use App\Models\Employee;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AttendanceProcessingService
{
    /**
     * Check if two date ranges overlap
     */
    private function dateRangesOverlap(Carbon $start1, Carbon $end1, Carbon $start2, Carbon $end2): bool
    {
        // Two ranges overlap if start1 <= end2 && start2 <= end1
        return $start1->lte($end2) && $start2->lte($end1);
    }

    /**
     * Get overlapping days between two date ranges
     */
    private function getOverlappingDays(Carbon $start1, Carbon $end1, Carbon $start2, Carbon $end2): array
    {
        $overlappingDays = [];

        // Find the actual overlap period
        $overlapStart = $start1->gt($start2) ? $start1 : $start2;
        $overlapEnd = $end1->lt($end2) ? $end1 : $end2;

        // Generate all days in the overlap period
        for ($date = $overlapStart->copy(); $date->lte($overlapEnd); $date->addDay()) {
            $overlappingDays[] = $date->format('Y-m-d');
        }

        return $overlappingDays;
    }

    /**
     * Find all overlapping processing records for an employee
     */
    private function findOverlappingProcessings(Employee $employee, Carbon $startDate, Carbon $endDate, ?string $processingType = null): Collection
    {
        $query = AttendanceProcessing::where('employee_id', $employee->id);

        if ($processingType) {
            $query->where('type', $processingType);
        }

        // Get all processings that might overlap
        $allProcessings = $query->get();

        // Filter to find only overlapping ones
        return $allProcessings->filter(function ($processing) use ($startDate, $endDate) {
            $procStart = Carbon::parse($processing->period_start);
            $procEnd = Carbon::parse($processing->period_end);

            return $this->dateRangesOverlap($startDate, $endDate, $procStart, $procEnd);
        });
    }

    /**
     * Generate detailed error message for overlapping processing
     */
    private function generateOverlapErrorMessage(Employee $employee, Collection $overlappingProcessings, Carbon $requestedStart, Carbon $requestedEnd): string
    {
        $employeeName = $employee->name;
        $employeeId = $employee->id;
        $requestedPeriod = $requestedStart->format('Y-m-d').' إلى '.$requestedEnd->format('Y-m-d');

        $messages = [];
        $messages[] = '❌ تم رفض العملية - تكرار في المعالجة';
        $messages[] = '━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━';
        $messages[] = "👤 الموظف: {$employeeName} (كود: {$employeeId})";
        $messages[] = "📅 الفترة المطلوبة: {$requestedPeriod}";
        $messages[] = '';
        $messages[] = "⚠️ تم العثور على {$overlappingProcessings->count()} معالجة متداخلة:";
        $messages[] = '';

        foreach ($overlappingProcessings as $index => $processing) {
            $procStart = Carbon::parse($processing->period_start);
            $procEnd = Carbon::parse($processing->period_end);
            $overlappingDays = $this->getOverlappingDays($requestedStart, $requestedEnd, $procStart, $procEnd);
            $overlapCount = count($overlappingDays);
            $firstDay = reset($overlappingDays);
            $lastDay = end($overlappingDays);

            $messages[] = '  '.($index + 1).". معالجة #{$processing->id}:";
            $messages[] = "     • الفترة المعالجة: {$procStart->format('Y-m-d')} إلى {$procEnd->format('Y-m-d')}";
            $messages[] = "     • عدد الأيام المتداخلة: {$overlapCount} يوم";

            if ($overlapCount > 0) {
                if ($overlapCount <= 10) {
                    $messages[] = '     • الأيام المتداخلة: '.implode(', ', $overlappingDays);
                } else {
                    $messages[] = "     • الأيام المتداخلة: {$firstDay} إلى {$lastDay} (المجموع: {$overlapCount} يوم)";
                }
            }

            $messages[] = '     • تاريخ المعالجة: '.$processing->created_at->format('Y-m-d H:i:s');
            $messages[] = '';
        }

        $messages[] = '💡 الحل: قم بحذف أو تعديل المعالجة السابقة قبل إعادة المحاولة';

        return implode("\n", $messages);
    }

    /**
     * Process attendance for a single employee
     */
    public function processSingleEmployee(Employee $employee, Carbon $startDate, Carbon $endDate, ?string $notes = null): array
    {
        DB::beginTransaction();

        try {
            // Check if employee is active
            if ($employee->isInactive()) {
                DB::rollBack();
                return [
                    'error' => "❌ الموظف {$employee->name} (كود: {$employee->id}) معطل ولا يمكن معالجة بصماته أو رواتبه.",
                ];
            }

            // Check for overlapping processing records (including partial overlaps)
            $overlappingProcessings = $this->findOverlappingProcessings($employee, $startDate, $endDate, 'single');

            if ($overlappingProcessings->isNotEmpty()) {
                $errorMessage = $this->generateOverlapErrorMessage($employee, $overlappingProcessings, $startDate, $endDate);

                DB::rollBack();

                return [
                    'error' => $errorMessage,
                    'existing_processing_ids' => $overlappingProcessings->pluck('id')->toArray(),
                    'overlapping_processings' => $overlappingProcessings->map(function ($proc) {
                        return [
                            'id' => $proc->id,
                            'period_start' => $proc->period_start,
                            'period_end' => $proc->period_end,
                            'created_at' => $proc->created_at->format('Y-m-d H:i:s'),
                        ];
                    })->toArray(),
                ];
            }

            // Process attendance data first
            $processedData = $this->processEmployeeAttendance($employee, $startDate, $endDate);

            // Create attendance processing record
            $processing = AttendanceProcessing::create([
                'type' => 'single',
                'employee_id' => $employee->id,
                'department_id' => $employee->department_id,
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
                'total_days' => $processedData['summary']['total_days'],
                'working_days' => $processedData['summary']['working_days'],
                'actual_work_days' => $processedData['summary']['present_days'],
                'overtime_work_days' => $processedData['summary']['overtime_days'] ?? 0,
                'absent_days' => $processedData['summary']['absent_days'],
                'unpaid_leave_days' => $processedData['summary']['unpaid_leave_days'] ?? 0,
                'total_hours' => $processedData['summary']['total_hours'],
                'actual_work_hours' => $processedData['summary']['actual_hours'],
                'overtime_work_minutes' => $processedData['summary']['overtime_minutes'],
                'total_late_minutes' => $processedData['summary']['late_minutes'] ?? 0,
                'calculated_salary_for_day' => $processedData['salary_data']['daily_rate'] ?? 0,
                'calculated_salary_for_hour' => $processedData['salary_data']['hourly_rate'] ?? 0,
                'employee_productivity_salary' => $processedData['salary_data']['basic_salary'] ?? 0,
                'salary_due' => $processedData['salary_data']['overtime_salary'] ?? 0,
                'total_salary' => $processedData['salary_data']['total_salary'] ?? 0,
                'notes' => $notes,
            ]);

            // Save processing details
            $this->saveProcessingDetails($processing, $processedData, $employee);

            DB::commit();

            return [
                'processing_id' => $processing->id,
                'employee' => $employee,
                'summary' => $processedData['summary'],
                'salary_data' => $processedData['salary_data'],
                'daily_details' => $processedData['details'],
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process attendance for multiple employees
     */
    public function processMultipleEmployees(array $employeeIds, Carbon $startDate, Carbon $endDate, ?string $notes = null): array
    {
        DB::beginTransaction();

        try {
            $results = [];

            foreach ($employeeIds as $employeeId) {
                $employee = Employee::findOrFail($employeeId);

                // Check if employee is active
                if ($employee->isInactive()) {
                    $results[] = [
                        'error' => "❌ الموظف {$employee->name} (كود: {$employee->id}) معطل ولا يمكن معالجة بصماته أو رواتبه.",
                        'employee' => $employee,
                    ];
                    continue; // Skip inactive employee
                }

                // Check for overlapping processing records
                $overlappingProcessings = $this->findOverlappingProcessings($employee, $startDate, $endDate, 'single');

                if ($overlappingProcessings->isNotEmpty()) {
                    $errorMessage = $this->generateOverlapErrorMessage($employee, $overlappingProcessings, $startDate, $endDate);

                    $results[] = [
                        'error' => $errorMessage,
                        'existing_processing_ids' => $overlappingProcessings->pluck('id')->toArray(),
                        'employee' => $employee,
                        'overlapping_processings' => $overlappingProcessings->map(function ($proc) {
                            return [
                                'id' => $proc->id,
                                'period_start' => $proc->period_start,
                                'period_end' => $proc->period_end,
                                'created_at' => $proc->created_at->format('Y-m-d H:i:s'),
                            ];
                        })->toArray(),
                    ];

                    continue; // Skip this employee
                }

                $result = $this->processSingleEmployee($employee, $startDate, $endDate, $notes);

                // If processSingleEmployee returns an error, add it to results
                if (isset($result['error'])) {
                    $results[] = $result;
                } else {
                    $results[] = $result;
                }
            }

            DB::commit();

            return $results;
        } catch (\Exception $e) {
            DB::rollBack();

            return [
                [
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Process attendance for entire department
     */
    public function processDepartment(Department $department, Carbon $startDate, Carbon $endDate, ?string $notes = null): array
    {
        DB::beginTransaction();

        try {
            $results = [];
            // Only get active employees
            $employees = $department->employees()->where('status', 'مفعل')->get();
            
            // Check if department has any active employees
            if ($employees->isEmpty()) {
                DB::rollBack();
                return [
                    'error' => "❌ القسم '{$department->title}' لا يحتوي على موظفين مفعلين.",
                    'department' => $department,
                    'department_summary' => [],
                    'results' => [],
                ];
            }
            
            // Check if there are any attendance records for the period
            $hasAnyAttendance = false;
            foreach ($employees as $employee) {
                $attendanceCount = Attendance::where('employee_id', $employee->id)
                    ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                    ->count();
                if ($attendanceCount > 0) {
                    $hasAnyAttendance = true;
                    break;
                }
            }
            
            if (!$hasAnyAttendance) {
                DB::rollBack();
                return [
                    'error' => "❌ لا توجد بصمات للموظفين في القسم '{$department->title}' للفترة من {$startDate->format('Y-m-d')} إلى {$endDate->format('Y-m-d')}.",
                    'department' => $department,
                    'department_summary' => [],
                    'results' => [],
                ];
            }
            
            $departmentSummary = [
                'total_days' => 0,
                'working_days' => 0,
                'actual_work_days' => 0,
                'overtime_work_days' => 0,
                'absent_days' => 0,
                'total_hours' => 0,
                'actual_work_hours' => 0,
                'overtime_work_minutes' => 0,
                'total_salary' => 0,
            ];
            
            $successfulProcessings = 0;

            foreach ($employees as $employee) {
                // Check if employee is active
                if ($employee->isInactive()) {
                    $results[] = [
                        'error' => "❌ الموظف {$employee->name} (كود: {$employee->id}) معطل ولا يمكن معالجة بصماته أو رواتبه.",
                        'employee' => $employee,
                    ];
                    continue; // Skip inactive employee
                }

                // Check for overlapping processing records (including partial overlaps)
                $overlappingProcessings = $this->findOverlappingProcessings($employee, $startDate, $endDate);

                // Also check specifically for department type processings
                $departmentOverlaps = $overlappingProcessings->filter(function ($proc) use ($department) {
                    return $proc->type == 'department' && $proc->department_id == $department->id;
                });

                // If there are any overlaps, reject
                if ($overlappingProcessings->isNotEmpty()) {
                    $errorMessage = $this->generateOverlapErrorMessage($employee, $overlappingProcessings, $startDate, $endDate);

                    $results[] = [
                        'error' => $errorMessage,
                        'existing_processing_ids' => $overlappingProcessings->pluck('id')->toArray(),
                        'employee' => $employee,
                        'overlapping_processings' => $overlappingProcessings->map(function ($proc) {
                            return [
                                'id' => $proc->id,
                                'type' => $proc->type,
                                'period_start' => $proc->period_start,
                                'period_end' => $proc->period_end,
                                'created_at' => $proc->created_at->format('Y-m-d H:i:s'),
                            ];
                        })->toArray(),
                    ];

                    continue; // Skip this employee
                }

                $processedData = $this->processEmployeeAttendance($employee, $startDate, $endDate);
                $salaryData = $processedData['salary_data'];

                // Create individual processing record for each employee
                $processing = AttendanceProcessing::create([
                    'type' => 'department',
                    'employee_id' => $employee->id,
                    'department_id' => $department->id,
                    'period_start' => $startDate->format('Y-m-d'),
                    'period_end' => $endDate->format('Y-m-d'),
                    'total_days' => $processedData['summary']['total_days'],
                    'working_days' => $processedData['summary']['working_days'],
                    'actual_work_days' => $processedData['summary']['present_days'],
                    'overtime_work_days' => $processedData['summary']['overtime_days'] ?? 0,
                    'absent_days' => $processedData['summary']['absent_days'],
                    'unpaid_leave_days' => $processedData['summary']['unpaid_leave_days'] ?? 0,
                    'total_hours' => $processedData['summary']['total_hours'],
                    'actual_work_hours' => $processedData['summary']['actual_hours'],
                    'overtime_work_minutes' => $processedData['summary']['overtime_minutes'],
                    'total_late_minutes' => $processedData['summary']['late_minutes'] ?? 0,
                    'calculated_salary_for_day' => $salaryData['daily_rate'] ?? 0,
                    'calculated_salary_for_hour' => $salaryData['hourly_rate'] ?? 0,
                    'employee_productivity_salary' => $salaryData['basic_salary'] ?? 0,
                    'salary_due' => $salaryData['overtime_salary'] ?? 0,
                    'total_salary' => $salaryData['total_salary'] ?? 0,
                    'notes' => $notes,
                ]);

                $this->saveProcessingDetails($processing, $processedData, $employee);

                // Accumulate department summary
                $departmentSummary['total_days'] = $processedData['summary']['total_days'];
                $departmentSummary['working_days'] += $processedData['summary']['working_days'];
                $departmentSummary['actual_work_days'] += $processedData['summary']['present_days'];
                $departmentSummary['overtime_work_days'] += $processedData['summary']['overtime_days'] ?? 0;
                $departmentSummary['absent_days'] += $processedData['summary']['absent_days'];
                $departmentSummary['total_hours'] += $processedData['summary']['total_hours'];
                $departmentSummary['actual_work_hours'] += $processedData['summary']['actual_hours'];
                $departmentSummary['overtime_work_minutes'] += $processedData['summary']['overtime_minutes'];
                $departmentSummary['total_salary'] += $salaryData['total_salary'];

                $results[] = [
                    'processing_id' => $processing->id,
                    'employee' => $employee,
                    'summary' => $processedData['summary'],
                    'salary_data' => $salaryData,
                    'daily_details' => $processedData['details'],
                ];
                
                $successfulProcessings++;
            }

            // Check if no employees were successfully processed
            if ($successfulProcessings === 0) {
                DB::rollBack();
                $errorMessages = array_filter(array_column($results, 'error'));
                $errorMessage = !empty($errorMessages) 
                    ? implode("\n\n", $errorMessages)
                    : "❌ لم يتم معالجة أي موظف في القسم '{$department->title}' للفترة المحددة.";
                
                return [
                    'error' => $errorMessage,
                    'department' => $department,
                    'department_summary' => [],
                    'results' => $results,
                ];
            }

            DB::commit();

            return [
                'department' => $department,
                'department_summary' => $departmentSummary,
                'results' => $results,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'department' => $department,
                'department_summary' => [],
                'results' => [
                    [
                        'error' => $e->getMessage(),
                    ],
                ],
            ];
        }
    }

    /**
     * Process attendance data for an employee
     */
    private function processEmployeeAttendance(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        // Use SalaryCalculationService to process attendance data
        $salaryCalculationService = new \App\Services\SalaryCalculationService;
        $result = $salaryCalculationService->calculateSalary($employee, $startDate, $endDate);

        return $result;
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
     * Save processing details to database
     * Note: This method is called within a transaction, so it doesn't start its own transaction
     */
    private function saveProcessingDetails(AttendanceProcessing $processing, array $processedData, Employee $employee): void
    {
        $summary = $processedData['summary'];
        $dailyDetails = $processedData['details'];
        $hourlyRate = $processedData['salary_data']['hourly_rate'] ?? 0;

        foreach ($dailyDetails as $date => $dayData) {
            // Check for existing detail record to prevent duplicates
            $existingDetail = AttendanceProcessingDetail::where('attendance_processing_id', $processing->id)
                ->where('employee_id', $employee->id)
                ->where('attendance_date', $date)
                ->first();

            if ($existingDetail) {

                continue; // Skip creating duplicate detail record
            }

            // For production-only type, salary is not calculated based on hours
            // Salary will be calculated later based on production formula
            $dailySalary = 0;
            if ($employee->salary_type !== 'إنتاج فقط') {
                if ($dayData['day_type'] === 'overtime_day') {
                    // If it's an overtime day, the whole day is calculated with the day multiplier
                    // We calculate the daily rate * multiplier
                    $dailyRate = $processedData['salary_data']['daily_rate'] ?? 0;
                    $dailySalary = $dailyRate * ($employee->additional_day_calculation ?? 1.5);
                    
                    // If there are specific overtime hours ON TOP of the day (unlikely for "overtime day" which is usually a holiday),
                    // we might need to decide if we add them. 
                    // Usually "Overtime Day" means the whole day is overtime.
                    // But if the system separates "actual hours" and "overtime hours" for that day:
                    // Let's assume the "Overtime Day" pay covers the "Actual Hours".
                    // Any "Overtime Hours" (hours beyond the shift) should probably be paid extra?
                    // For now, let's stick to the user request: "Calculate the overtime day based on the employee's table".
                    // So we use the Day Multiplier on the Daily Rate.
                    
                    // However, we should also check if we need to add overtime hours pay if they exist separately.
                    // But typically, if I work on Friday, I get paid 1.5 days. 
                    // So $dailySalary = DailyRate * 1.5.
                    
                    // What if I work MORE than 8 hours on Friday? 
                    // The current logic in SalaryCalculationService separates hours.
                    // But for "Overtime Day", the `actual_hours` might be 0 in some logic? No, it should be > 0.
                    
                    // Let's look at how we calculated total salary in the plan.
                    // We said: $overtimeDaysSalary = $summary['overtime_days'] * $dailyRate * $multiplier.
                    // So here, for this specific day, we should set $dailySalary to that amount.
                    
                } else {
                    // Normal working day
                    $dailySalary = ($dayData['actual_hours'] * $hourlyRate) +
                                  (($dayData['overtime_minutes'] / 60) * $hourlyRate * ($employee->additional_hour_calculation ?? 1.5));
                }
            }

            // Get shift times if employee has a shift
            $shiftStartTime = null;
            $shiftEndTime = null;
            $basicHours = 0;
            $earlyHours = 0;

            if ($employee->shift) {
                $shiftStartTime = $employee->shift->start_time;
                $shiftEndTime = $employee->shift->end_time;
                $basicHours = $this->getExpectedHours($employee);

                // Calculate early hours if check-in is before shift start
                if ($dayData['check_in_time'] && $shiftStartTime) {
                    $checkInTime = Carbon::parse($dayData['check_in_time']);
                    $shiftStart = Carbon::parse($shiftStartTime);
                    if ($checkInTime->lt($shiftStart)) {
                        $earlyHours = $shiftStart->diffInHours($checkInTime, false);
                    }
                }
            }

            // For paid leave days, use expected hours instead of actual hours for salary calculation
            // But skip salary calculation for production-only type
            if ($dayData['status'] === 'paid_leave' && $employee->salary_type !== 'إنتاج فقط') {
                $dailySalary = ($dayData['actual_hours'] * $hourlyRate); // Paid leave gets full expected hours salary
                $basicHours = $this->getExpectedHours($employee); // Use expected hours for paid leave
            }

            AttendanceProcessingDetail::create([
                'attendance_processing_id' => $processing->id,
                'employee_id' => $employee->id,
                'department_id' => $employee->department_id,
                'attendance_date' => $date,
                'attendance_status' => $dayData['status'],
                'shift_start_time' => $shiftStartTime,
                'shift_end_time' => $shiftEndTime,
                'working_hours_in_shift' => $basicHours,
                'check_in_time' => $dayData['check_in_time'],
                'check_out_time' => $dayData['check_out_time'],
                'attendance_basic_hours_count' => $basicHours,
                'attendance_actual_hours_count' => $dayData['actual_hours'],
                'attendance_overtime_minutes_count' => $dayData['overtime_minutes'],
                'attendance_late_minutes_count' => $dayData['late_minutes'],
                'early_hours' => $earlyHours,
                'attendance_total_hours_count' => $dayData['actual_hours'] + ($dayData['overtime_minutes'] / 60),
                'total_due_hourly_salary' => $dailySalary,
                'day_type' => $dayData['day_type'],
                'notes' => $dayData['notes'] ?? null,
            ]);
        }
    }

    /**
     * Get processing history
     */
    public function getProcessingHistory(int $limit = 10): Collection
    {
        return AttendanceProcessing::with(['employee', 'department'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get processing details by ID
     */
    public function getProcessingDetails(int $processingId): array
    {
        $processing = AttendanceProcessing::with(['employee', 'department'])->findOrFail($processingId);
        $details = AttendanceProcessingDetail::where('attendance_processing_id', $processingId)
            ->with(['employee', 'department'])
            ->get();

        return [
            'processing' => $processing,
            'details' => $details,
        ];
    }

    /**
     * Generate attendance report
     */
    public function generateAttendanceReport(Carbon $startDate, Carbon $endDate, ?int $departmentId = null): array
    {
        $query = AttendanceProcessingDetail::with(['employee', 'department'])
            ->whereBetween('attendance_date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);

        if ($departmentId) {
            $query->where('department_id', $departmentId);
        }

        $details = $query->get();

        $summary = [
            'total_employees' => $details->unique('employee_id')->count(),
            'total_days' => $details->count(),
            'total_hours' => $details->sum('attendance_total_hours_count'),
            'overtime_hours' => $details->sum('attendance_overtime_hours_count'),
            'late_hours' => $details->sum('attendance_late_hours_count'),
        ];

        return [
            'summary' => $summary,
            'details' => $details,
            'by_employee' => $details->groupBy('employee_id'),
            'by_department' => $details->groupBy('department_id'),
            'by_date' => $details->groupBy('attendance_date'),
        ];
    }

    /**
     * Calculate hourly rate for employee
     */
    private function calculateHourlyRate(Employee $employee): float
    {
        $monthlySalary = $employee->salary ?? 0;
        $workingDaysPerMonth = now()->daysInMonth;
        $hoursPerDay = $this->getExpectedHours($employee);

        return $monthlySalary / $workingDaysPerMonth / $hoursPerDay;
    }

    /**
     * Calculate daily rate for employee
     */
    private function calculateDailyRate(Employee $employee): float
    {
        $monthlySalary = $employee->salary ?? 0;
        $workingDaysPerMonth = now()->daysInMonth;

        return $monthlySalary / $workingDaysPerMonth;
    }
}
