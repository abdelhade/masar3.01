<?php

namespace Modules\HR\Services;

use Modules\HR\Exceptions\AttendanceProcessingException;
use Modules\HR\Jobs\ProcessAttendanceJob;
use Modules\HR\Jobs\ProcessSingleEmployeeJob;
use Modules\HR\Models\Attendance;
use Modules\HR\Models\AttendanceProcessing;
use Modules\HR\Models\AttendanceProcessingDetail;
use Modules\HR\Models\Department;
use Modules\HR\Models\Employee;
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
     * Optimized to use database query instead of memory filtering
     */
    private function findOverlappingProcessings(Employee $employee, Carbon $startDate, Carbon $endDate, ?string $processingType = null): Collection
    {
        $query = AttendanceProcessing::where('employee_id', $employee->id)
            ->where(function ($q) use ($startDate, $endDate) {
                // Overlap condition: start1 <= end2 && start2 <= end1
                // Converted to: period_start <= endDate AND period_end >= startDate
                $q->where('period_start', '<=', $endDate->format('Y-m-d'))
                    ->where('period_end', '>=', $startDate->format('Y-m-d'));
            });

        if ($processingType) {
            $query->where('type', $processingType);
        }

        return $query->get();
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
     * Process employee attendance data without transaction management
     * This method contains the core processing logic and can be called from within a transaction
     *
     * @param  Employee  $employee  The employee to process attendance for
     * @param  Carbon  $startDate  Start date of the processing period
     * @param  Carbon  $endDate  End date of the processing period
     * @param  string  $processingType  Type of processing: 'single', 'multiple', or 'department'
     * @param  int|null  $departmentId  Department ID (required for 'department' type)
     * @param  string|null  $notes  Optional notes for the processing
     * @return array{
     *     processing_id?: int,
     *     employee?: Employee,
     *     summary?: array<string, mixed>,
     *     salary_data?: array<string, mixed>,
     *     daily_details?: array<string, array>,
     *     error?: string,
     *     existing_processing_ids?: array<int>,
     *     overlapping_processings?: array<int, array>
     * }
     */
    private function processEmployeeData(Employee $employee, Carbon $startDate, Carbon $endDate, string $processingType, ?int $departmentId = null, ?string $notes = null): array
    {
        // Check if employee is active
        if ($employee->isInactive()) {
            throw AttendanceProcessingException::inactiveEmployee($employee->name, $employee->id);
        }

        // Check for overlapping processing records (including partial overlaps)
        $overlappingProcessings = $this->findOverlappingProcessings($employee, $startDate, $endDate, $processingType === 'department' ? null : 'single');

        if ($overlappingProcessings->isNotEmpty()) {
            $errorMessage = $this->generateOverlapErrorMessage($employee, $overlappingProcessings, $startDate, $endDate);

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
            'type' => $processingType,
            'employee_id' => $employee->id,
            'department_id' => $departmentId ?? $employee->department_id,
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
            'employee_productivity_salary' => 0,
            'salary_due' => $processedData['salary_data']['basic_salary'] ?? 0,
            'total_salary' => $processedData['salary_data']['total_salary'] ?? 0,
            'notes' => $notes,
        ]);

        // Save processing details
        $this->saveProcessingDetails($processing, $processedData, $employee);

        // Calculate monthly deductions (but don't create journal entries yet)
        $deductionService = app(\Modules\HR\Services\EmployeeDeductionRewardService::class);
        $deductionService->calculateMonthlyDeductions($employee, $startDate, $endDate, $processing->id);

        return [
            'processing_id' => $processing->id,
            'employee' => $employee,
            'summary' => $processedData['summary'],
            'salary_data' => $processedData['salary_data'],
            'daily_details' => $processedData['details'],
        ];
    }



    /**
     * Process attendance for a single employee
     *
     * This method processes attendance records for a single employee within a specified date range,
     * calculates working hours, overtime, and salary, then saves the results to the database.
     *
     * @param  Employee  $employee  The employee to process attendance for
     * @param  Carbon  $startDate  Start date of the processing period (inclusive)
     * @param  Carbon  $endDate  End date of the processing period (inclusive)
     * @param  string|null  $notes  Optional notes to attach to the processing record
     * @return array{
     *     processing_id?: int,
     *     employee?: Employee,
     *     summary?: array{
     *         total_days: int,
     *         working_days: int,
     *         present_days: float,
     *         absent_days: int,
     *         overtime_days: int,
     *         total_hours: float,
     *         actual_hours: float,
     *         overtime_minutes: int,
     *         late_minutes: int
     *     },
     *     salary_data?: array{
     *         basic_salary: float,
     *         overtime_salary: float,
     *         total_salary: float,
     *         hourly_rate: float,
     *         daily_rate: float
     *     },
     *     daily_details?: array<string, array>,
     *     error?: string,
     *     existing_processing_ids?: array<int>,
     *     overlapping_processings?: array<int, array>
     * }
     *
     * @throws \Exception If database transaction fails
     */
    public function processSingleEmployee(Employee $employee, Carbon $startDate, Carbon $endDate, ?string $notes = null): array
    {
        DB::beginTransaction();

        try {
            $result = $this->processEmployeeData($employee, $startDate, $endDate, 'single', null, $notes);

            if (isset($result['error'])) {
                DB::rollBack();

                return $result;
            }

            DB::commit();

            return $result;
        } catch (AttendanceProcessingException $e) {
            DB::rollBack();

            return [
                'error' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error processing single employee attendance', [
                'employee_id' => $employee->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Process attendance for multiple employees
     *
     * Processes attendance for multiple employees in a single transaction.
     * Each employee is processed independently, and errors for one employee
     * do not prevent processing of other employees.
     *
     * @param  array<int>  $employeeIds  Array of employee IDs to process
     * @param  Carbon  $startDate  Start date of the processing period (inclusive)
     * @param  Carbon  $endDate  End date of the processing period (inclusive)
     * @param  string|null  $notes  Optional notes to attach to processing records
     * @return array<int, array> Array of results, one for each employee
     *
     * @throws \Exception If database transaction fails
     */
    public function processMultipleEmployees(array $employeeIds, Carbon $startDate, Carbon $endDate, ?string $notes = null): array
    {
        DB::beginTransaction();

        try {
            $results = [];

            foreach ($employeeIds as $employeeId) {
                try {
                    $employee = Employee::findOrFail($employeeId);
                    $result = $this->processEmployeeData($employee, $startDate, $endDate, 'single', null, $notes);
                    $results[] = $result;
                } catch (AttendanceProcessingException $e) {
                    $results[] = [
                        'error' => $e->getMessage(),
                    ];
                    // Continue processing other employees
                } catch (\Exception $e) {
                    Log::error('Error processing employee in multiple processing', [
                        'employee_id' => $employeeId,
                        'error' => $e->getMessage(),
                    ]);
                    $results[] = [
                        'error' => "❌ خطأ في معالجة الموظف (كود: {$employeeId}): ".$e->getMessage(),
                    ];
                    // Continue processing other employees
                }
            }

            DB::commit();

            return $results;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error in processMultipleEmployees', [
                'employee_ids' => $employeeIds,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return [
                [
                    'error' => $e->getMessage(),
                ],
            ];
        }
    }

    /**
     * Process attendance for entire department
     *
     * Processes attendance for all active employees in a department within a specified date range.
     * Returns a summary of all employees' processing results along with department-level totals.
     *
     * @param  Department  $department  The department to process
     * @param  Carbon  $startDate  Start date of the processing period (inclusive)
     * @param  Carbon  $endDate  End date of the processing period (inclusive)
     * @param  string|null  $notes  Optional notes to attach to processing records
     * @return array{
     *     department?: Department,
     *     department_summary?: array{
     *         total_days: int,
     *         working_days: int,
     *         actual_work_days: int,
     *         overtime_work_days: int,
     *         absent_days: int,
     *         total_hours: float,
     *         actual_work_hours: float,
     *         overtime_work_minutes: int,
     *         total_salary: float
     *     },
     *     results?: array<int, array>,
     *     error?: string
     * }
     *
     * @throws \Exception If database transaction fails
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

            if (! $hasAnyAttendance) {
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
                try {
                    $result = $this->processEmployeeData($employee, $startDate, $endDate, 'department', $department->id, $notes);

                    if (isset($result['error'])) {
                        $results[] = $result;

                        continue;
                    }

                    // Accumulate department summary from successful processing
                    $summary = $result['summary'];
                    $salaryData = $result['salary_data'];

                    $departmentSummary['total_days'] = $summary['total_days'];
                    $departmentSummary['working_days'] += $summary['working_days'];
                    $departmentSummary['actual_work_days'] += $summary['present_days'];
                    $departmentSummary['overtime_work_days'] += $summary['overtime_days'] ?? 0;
                    $departmentSummary['absent_days'] += $summary['absent_days'];
                    $departmentSummary['total_hours'] += $summary['total_hours'];
                    $departmentSummary['actual_work_hours'] += $summary['actual_hours'];
                    $departmentSummary['overtime_work_minutes'] += $summary['overtime_minutes'];
                    $departmentSummary['total_salary'] += $salaryData['total_salary'];

                    $results[] = $result;
                    $successfulProcessings++;
                } catch (AttendanceProcessingException $e) {
                    $results[] = [
                        'error' => $e->getMessage(),
                        'employee' => $employee,
                    ];
                    // Continue processing other employees
                } catch (\Exception $e) {
                    Log::error('Error processing employee in department processing', [
                        'employee_id' => $employee->id,
                        'department_id' => $department->id,
                        'error' => $e->getMessage(),
                    ]);
                    $results[] = [
                        'error' => "❌ خطأ في معالجة الموظف {$employee->name} (كود: {$employee->id}): ".$e->getMessage(),
                        'employee' => $employee,
                    ];
                    // Continue processing other employees
                }
            }

            // Check if no employees were successfully processed
            if ($successfulProcessings === 0) {
                DB::rollBack();
                $errorMessages = array_filter(array_column($results, 'error'));
                $errorMessage = ! empty($errorMessages)
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
            Log::error('Error in processDepartment', [
                'department_id' => $department->id,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

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
        $salaryCalculationService = new \Modules\HR\Services\SalaryCalculationService;
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
                'project_code' => $dayData['project_code'] ?? null,
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

    /**
     * Process department attendance asynchronously using Queue
     * This is useful for large departments to avoid timeout
     *
     * @param  Department  $department  The department to process
     * @param  Carbon  $startDate  Start date of the processing period
     * @param  Carbon  $endDate  End date of the processing period
     * @param  string|null  $notes  Optional notes
     * @return string Job ID for tracking
     */
    /**
     * Process department attendance asynchronously using Queue
     * This is useful for large departments to avoid timeout
     *
     * @param  Department  $department  The department to process
     * @param  Carbon  $startDate  Start date of the processing period
     * @param  Carbon  $endDate  End date of the processing period
     * @param  string|null  $notes  Optional notes
     */
    public function processDepartmentAsync(Department $department, Carbon $startDate, Carbon $endDate, ?string $notes = null): void
    {
        ProcessAttendanceJob::dispatch(
            $department->id,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            $notes
        );
    }

    /**
     * Process single employee attendance asynchronously using Queue
     * This is useful when processing might take long time
     *
     * @param  Employee  $employee  The employee to process
     * @param  Carbon  $startDate  Start date of the processing period
     * @param  Carbon  $endDate  End date of the processing period
     * @param  string|null  $notes  Optional notes
     */
    public function processSingleEmployeeAsync(Employee $employee, Carbon $startDate, Carbon $endDate, ?string $notes = null): void
    {
        ProcessSingleEmployeeJob::dispatch(
            $employee->id,
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d'),
            $notes
        );
    }
}
