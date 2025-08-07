<?php

namespace App\Services;

use App\Models\Employee;
use App\Models\Attendance;
use App\Models\AttendanceProcessing;
use App\Models\AttendanceProcessingDetail;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;


class AttendanceProcessingService
{
    /**
     * Process attendance for a single employee
     */
    public function processSingleEmployee(Employee $employee, Carbon $startDate, Carbon $endDate, ?string $notes = null): array
    {
        return DB::transaction(function () use ($employee, $startDate, $endDate, $notes) {
            try{
            // Check for existing processing record to prevent duplicates
            $existingProcessing = AttendanceProcessing::where('employee_id', $employee->id)
                ->where('type', 'single')
                ->where('period_start', $startDate->format('Y-m-d'))
                ->where('period_end', $endDate->format('Y-m-d'))
                ->first();
                
            if ($existingProcessing) {
                Log::warning('Duplicate processing attempt for employee: ' . $employee->id . ' for period: ' . $startDate->format('Y-m-d') . ' to ' . $endDate->format('Y-m-d'));
                return [
                    'error' => 'تم معالجة حضور هذا الموظف مسبقاً لهذه الفترة',
                    'existing_processing_id' => $existingProcessing->id
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
                'total_hours' => $processedData['summary']['total_hours'],
                'actual_work_hours' => $processedData['summary']['actual_hours'],
                'overtime_work_hours' => $processedData['summary']['overtime_hours'],
                'calculated_salary_for_day' => $processedData['salary_data']['daily_rate'],
                'calculated_salary_for_hour' => $processedData['salary_data']['hourly_rate'],
                'employee_productivity_salary' => $processedData['salary_data']['basic_salary'],
                'salary_due' => $processedData['salary_data']['overtime_salary'],
                'total_salary' => $processedData['salary_data']['total_salary'],
                'notes' => $notes
            ]);

            // Save processing details
            $this->saveProcessingDetails($processing, $processedData, $employee);

            return [
                'processing_id' => $processing->id,
                'employee' => $employee,
                'summary' => $processedData['summary'],
                'salary_data' => $processedData['salary_data'],
                'daily_details' => $processedData['details']
            ];
            }catch(\Exception $e){
                // print the error in log
                Log::error('Error processing attendance for employee: ' . $employee->id . ' - ' . $e->getMessage());
                return [
                    'error' => $e->getMessage()
                ];
            }
        });
    }

    /**
     * Process attendance for multiple employees
     */
    public function processMultipleEmployees(array $employeeIds, Carbon $startDate, Carbon $endDate, ?string $notes = null): array
    {
        return DB::transaction(function () use ($employeeIds, $startDate, $endDate, $notes) {
            $results = [];
            
            foreach ($employeeIds as $employeeId) {
                $employee = Employee::findOrFail($employeeId);
                $result = $this->processSingleEmployee($employee, $startDate, $endDate, $notes);
                $results[] = $result;
            }

            return $results;
        });
    }

    /**
     * Process attendance for entire department
     */
    public function processDepartment(Department $department, Carbon $startDate, Carbon $endDate, ?string $notes = null): array
    {
        return DB::transaction(function () use ($department, $startDate, $endDate, $notes) {
            $results = [];
            $employees = $department->employees;
            $departmentSummary = [
                'total_days' => 0,
                'working_days' => 0,
                'actual_work_days' => 0,
                'overtime_work_days' => 0,
                'absent_days' => 0,
                'total_hours' => 0,
                'actual_work_hours' => 0,
                'overtime_work_hours' => 0,
                'total_salary' => 0
            ];

            foreach ($employees as $employee) {
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
                    'total_hours' => $processedData['summary']['total_hours'],
                    'actual_work_hours' => $processedData['summary']['actual_hours'],
                    'overtime_work_hours' => $processedData['summary']['overtime_hours'],
                    'calculated_salary_for_day' => $salaryData['daily_rate'],
                    'calculated_salary_for_hour' => $salaryData['hourly_rate'],
                    'employee_productivity_salary' => $salaryData['basic_salary'],
                    'salary_due' => $salaryData['overtime_salary'],
                    'total_salary' => $salaryData['total_salary'],
                    'notes' => $notes
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
                $departmentSummary['overtime_work_hours'] += $processedData['summary']['overtime_hours'];
                $departmentSummary['total_salary'] += $salaryData['total_salary'];
                
                $results[] = [
                    'processing_id' => $processing->id,
                    'employee' => $employee,
                    'summary' => $processedData['summary'],
                    'salary_data' => $salaryData,
                    'daily_details' => $processedData['details']
                ];
            }

            return [
                'department' => $department,
                'department_summary' => $departmentSummary,
                'results' => $results
            ];
        });
    }

    /**
     * Process attendance data for an employee
     */
    private function processEmployeeAttendance(Employee $employee, Carbon $startDate, Carbon $endDate): array
    {
        // Use SalaryCalculationService to process attendance data
        $salaryCalculationService = new \App\Services\SalaryCalculationService();
        $result = $salaryCalculationService->calculateSalary($employee, $startDate, $endDate);
        
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
     * Save processing details to database
     */
    private function saveProcessingDetails(AttendanceProcessing $processing, array $processedData, Employee $employee): void
    {
        $summary = $processedData['summary'];
        $dailyDetails = $processedData['details'];
        $hourlyRate = $processedData['salary_data']['hourly_rate'];
        
        foreach ($dailyDetails as $date => $dayData) {
            // Check for existing detail record to prevent duplicates
            $existingDetail = AttendanceProcessingDetail::where('attendance_processing_id', $processing->id)
                ->where('employee_id', $employee->id)
                ->where('attendance_date', $date)
                ->first();
                
            if ($existingDetail) {
                Log::warning('Duplicate detail record found for processing: ' . $processing->id . ', employee: ' . $employee->id . ', date: ' . $date);
                continue; // Skip creating duplicate detail record
            }
            
            $dailySalary = ($dayData['actual_hours'] * $hourlyRate) + 
                          ($dayData['overtime_hours'] * $hourlyRate * ($employee->additional_hour_calculation ?? 1.5));
            
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
                'attendance_overtime_hours_count' => $dayData['overtime_hours'],
                'attendance_late_hours_count' => $dayData['late_hours'],
                'early_hours' => $earlyHours,
                'attendance_total_hours_count' => $dayData['actual_hours'] + $dayData['overtime_hours'],
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
            'details' => $details
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