<?php

declare(strict_types=1);

namespace Modules\HR\Livewire;

use Modules\HR\Models\AttendanceProcessing;
use Modules\HR\Models\AttendanceProcessingDetail;
use Modules\HR\Models\Department;
use Modules\HR\Models\Employee;
use Modules\HR\Services\AttendanceProcessingService;
use App\Services\JournalService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Accounts\Models\AccHead;

class AttendanceProcessingManager extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $processingType = 'single';

    public ?int $selectedEmployee = null;

    public ?int $selectedDepartment = null;

    /** @var array<int> */
    public array $selectedEmployees = [];

    public string $startDate = '';

    public string $endDate = '';

    public string $notes = '';

    public bool $isProcessing = false;

    public bool $showResults = false;

    /** @var array<string, mixed> */
    public array $processingResults = [];

    public ?AttendanceProcessing $selectedProcessing = null;

    public bool $showDetails = false;

    /** @var Collection<int, AttendanceProcessingDetail> */
    public ?Collection $processingDetails = null;

    /** @var array<string, mixed> */
    public array $deductionsRewardsSummary = [];

    /** @var Collection<int, \Modules\HR\Models\EmployeeAdvance> */
    public ?Collection $advancesSummary = null;

    /** @var array<string, mixed> */
    public array $finalBalance = [];

    protected AttendanceProcessingService $attendanceProcessingService;

    public function boot(): void
    {
        $this->attendanceProcessingService = app(AttendanceProcessingService::class);
    }

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    protected $rules = [
        'processingType' => 'required|in:single,multiple,department',
        'startDate' => 'required|date',
        'endDate' => 'required|date|after_or_equal:startDate',
        'selectedEmployee' => 'required_if:processingType,single',
        'selectedDepartment' => 'required_if:processingType,department',
        'selectedEmployees' => 'required_if:processingType,multiple',
    ];

    protected $messages = [
        'selectedEmployee.required_if' => 'يرجى اختيار موظف',
        'selectedDepartment.required_if' => 'يرجى اختيار قسم',
        'selectedEmployees.required_if' => 'يرجى اختيار موظف واحد على الأقل',
        'startDate.required' => 'تاريخ البداية مطلوب',
        'endDate.required' => 'تاريخ النهاية مطلوب',
        'endDate.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية',
    ];

    public function updatedProcessingType()
    {
        $this->resetSelection();
        $this->resetErrorBag(); // Clear all validation errors
        $this->dispatch('processing-type-changed'); // Trigger UI update
        $this->dispatch('reinitialize-tom-select'); // Force Tom Select reinitialization
    }

    public function resetSelection()
    {
        $this->selectedEmployee = null;
        $this->selectedDepartment = null;
        $this->selectedEmployees = [];
        $this->showResults = false;
        $this->processingResults = [];
    }

    public function processAttendance(): void
    {
        try {
            // Validate the form
            $this->validate();

            // Additional validation for selectedEmployees
            if ($this->processingType === 'multiple' && empty($this->selectedEmployees)) {
                session()->flash('error', __('hr.please_select_at_least_one_employee'));

                return;
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e; // Re-throw to show validation errors
        }

        $this->isProcessing = true;

        try {
            $startDate = Carbon::parse($this->startDate);
            $endDate = Carbon::parse($this->endDate);

            switch ($this->processingType) {
                case 'single':
                    $employee = Employee::findOrFail($this->selectedEmployee);

                    $results = $this->attendanceProcessingService->processSingleEmployee(
                        $employee,
                        $startDate,
                        $endDate,
                        $this->notes
                    );

                    // Check if there was an error (like duplicate processing or overlap)
                    if (isset($results['error'])) {
                        // Store detailed error information
                        session()->flash('error', $results['error']);
                        session()->flash('error_type', 'overlap');

                        if (isset($results['existing_processing_ids'])) {
                            session()->flash('existing_processing_ids', $results['existing_processing_ids']);
                        }
                        if (isset($results['overlapping_processings'])) {
                            session()->flash('overlapping_processings', $results['overlapping_processings']);
                        }
                        $this->isProcessing = false;

                        return;
                    }
                    break;

                case 'multiple':
                    $results = $this->attendanceProcessingService->processMultipleEmployees(
                        $this->selectedEmployees,
                        $startDate,
                        $endDate,
                        $this->notes
                    );

                    // Check for errors in multiple employees processing
                    $hasErrors = false;
                    $errorMessages = [];

                    foreach ($results as $result) {
                        if (isset($result['error'])) {
                            $hasErrors = true;
                            $errorMessages[] = $result['error'];
                        }
                    }

                    if ($hasErrors) {
                        session()->flash('error', implode("\n\n", $errorMessages));
                        session()->flash('error_type', 'overlap');
                        $this->isProcessing = false;

                        return;
                    }
                    break;

                case 'department':
                    $department = Department::findOrFail($this->selectedDepartment);

                    $results = $this->attendanceProcessingService->processDepartment(
                        $department,
                        $startDate,
                        $endDate,
                        $this->notes
                    );

                    // Check for top-level error (no employees or no attendance)
                    if (isset($results['error'])) {
                        session()->flash('error', $results['error']);
                        session()->flash('error_type', 'validation');
                        $this->isProcessing = false;

                        return;
                    }

                    // Check for errors in department processing
                    $hasErrors = false;
                    $errorMessages = [];

                    if (isset($results['results'])) {
                        foreach ($results['results'] as $result) {
                            if (isset($result['error'])) {
                                $hasErrors = true;
                                $errorMessages[] = $result['error'];
                            }
                        }
                    }

                    if ($hasErrors) {
                        session()->flash('error', implode("\n\n", $errorMessages));
                        session()->flash('error_type', 'overlap');
                        $this->isProcessing = false;

                        return;
                    }
                    break;

                default:
                    throw new \Exception('نوع معالجة غير صحيح');
            }

            $this->processingResults = $results;
            $this->showResults = true;
            $this->resetSelection();
            $this->resetPage(); // Reset pagination to show new processing

            session()->flash('success', 'تم معالجة الحضور بنجاح');

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'لم يتم العثور على البيانات المطلوبة');
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء معالجة الحضور: '.$e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    /**
     * Get paginated processing records
     * This is now a computed property that uses pagination
     */
    public function getProcessingsProperty()
    {
        return AttendanceProcessing::with(['employee', 'department'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function viewProcessingDetails(int $processingId): void
    {

        try {
            $this->selectedProcessing = AttendanceProcessing::with(['employee', 'department'])
                ->findOrFail($processingId);

            $this->processingDetails = AttendanceProcessingDetail::with(['employee'])
                ->where('attendance_processing_id', $processingId)
                ->orderBy('attendance_date')
                ->get();

            $employee = $this->selectedProcessing->employee;
            $processing = $this->selectedProcessing;

            // Calculate salary components from processing data
            $basicSalary = $employee->salary ?? 0;
            
            // Calculate overtime from processing
            $overtimeAmount = 0;
            if ($processing->overtime_work_minutes > 0 && $processing->calculated_salary_for_hour > 0) {
                $overtimeHours = $processing->overtime_work_minutes / 60;
                $overtimeMultiplier = $employee->additional_hour_calculation ?? 1.5;
                $overtimeAmount = $overtimeHours * $processing->calculated_salary_for_hour * $overtimeMultiplier;
            }
            
            // Calculate attendance deductions from processing
            $attendanceDeductions = 0;
            if ($processing->total_late_minutes > 0 && $processing->calculated_salary_for_hour > 0) {
                $lateHourCalculation = $employee->late_hour_calculation ?? 1.0;
                $attendanceDeductions += ($processing->total_late_minutes / 60) * $lateHourCalculation * $processing->calculated_salary_for_hour;
            }
            if ($processing->absent_days > 0 && $processing->calculated_salary_for_day > 0) {
                $lateDayCalculation = $employee->late_day_calculation ?? 1.0;
                $attendanceDeductions += $processing->absent_days * $lateDayCalculation * $processing->calculated_salary_for_day;
            }
            if ($processing->unpaid_leave_days > 0 && $processing->calculated_salary_for_day > 0) {
                $attendanceDeductions += $processing->unpaid_leave_days * $processing->calculated_salary_for_day;
            }

            // Fetch balances from AccHead sub-accounts using service
            /** @var \Modules\HR\Services\EmployeeDeductionRewardService $deductionService */
            $deductionService = app(\Modules\HR\Services\EmployeeDeductionRewardService::class);
            
            $startDate = Carbon::parse($processing->period_start);
            $endDate = Carbon::parse($processing->period_end);

            $rewardsPayable = $deductionService->getRewardsPayableBalance($employee);
            $deductionsReceivable = $deductionService->getDeductionsReceivableBalance($employee);
            $advancesBalance = $deductionService->getAdvancesBalance($employee);

            // Calculations based on User Request
            // 1. Period Attendance Salary (Base calculated from days/attendance)
            $salaryDueFromAttendance = round((float) ($processing->salary_due ?? 0), 2);
            
            // 2. Net Period Salary (Attendance + Overtime - Deductions)
            $netPeriodSalary = $salaryDueFromAttendance + $overtimeAmount - $attendanceDeductions;

            // Fetch settled amounts for the period
            $rewardsSettled = $deductionService->getSettledRewards($employee, $startDate, $endDate);
            $deductionsSettled = $deductionService->getSettledDeductions($employee, $startDate, $endDate);
            $advancesSettled = $deductionService->getSettledAdvances($employee, $startDate, $endDate);

            // 3. Final Net Calculation (Adding/Subtracting settled amounts from period net)
            $finalNet = $netPeriodSalary + $rewardsSettled - $deductionsSettled - $advancesSettled;

            // Build finalBalance array
            $this->finalBalance = [
                // Employee info
                'basic_salary' => $basicSalary,
                
                // Period salary components
                'salary_due' => $salaryDueFromAttendance,
                'overtime_salary' => round((float) $overtimeAmount, 2),
                'attendance_deductions' => round((float) $attendanceDeductions, 2),
                'net_period_salary' => round((float) $netPeriodSalary, 2),
                
                // Settled amounts (Applied to this salary)
                'rewards_settled' => round((float) $rewardsSettled, 2),
                'deductions_settled' => round((float) $deductionsSettled, 2),
                'advances_settled' => round((float) $advancesSettled, 2),
                
                // Remaining/Outstanding Balances (For display)
                'rewards_remaining' => round((float) abs($rewardsPayable), 2),
                'deductions_remaining' => round((float) abs($deductionsReceivable), 2),
                'advances_remaining' => round((float) abs($advancesBalance), 2),
                
                // Final Net Salary to be paid
                'final_net' => round((float) $finalNet, 2),
            ];

            // Load deductions, rewards, and advances for the period (for detail display)
            $startDate = Carbon::parse($this->selectedProcessing->period_start);
            $endDate = Carbon::parse($this->selectedProcessing->period_end);

            $deductionService = app(\Modules\HR\Services\EmployeeDeductionRewardService::class);

            // Load deductions and rewards summary (for history display)
            $this->deductionsRewardsSummary = $deductionService->getMonthlySummary(
                $employee,
                $startDate,
                $endDate
            );

            // Load advances summary (for history display)
            $this->advancesSummary = \Modules\HR\Models\EmployeeAdvance::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get();

            $this->showDetails = true;

        } catch (\Exception $e) {
            session()->flash('error', __('hr.error_loading_processing_details'));
        }
    }

    public function closeDetails(): void
    {
        $this->showDetails = false;
        $this->selectedProcessing = null;
        $this->processingDetails = null;
        $this->deductionsRewardsSummary = [];
        $this->advancesSummary = null;
        $this->finalBalance = [];
    }

    public function approveProcessing(int $processingId): void
    {
        DB::beginTransaction();

        try {
            $processing = AttendanceProcessing::findOrFail($processingId);

            // Check if already approved to prevent duplicate journals
            if ($processing->status === 'approved') {
                session()->flash('error', __('hr.processing_already_approved'));
                DB::rollBack();

                return;
            }

            $employee = Employee::with('account')->findOrFail($processing->employee_id);

            if (! $employee->account) {
                throw new \Exception('حساب الموظف الرئيسي غير موجود');
            }

            // Get salary expense account (5301)
            $salaryExpenseAccount = AccHead::where('code', '5301')->first();
            if (! $salaryExpenseAccount) {
                throw new \Exception('حساب رواتب الموظفين (5301) غير موجود');
            }

            // Get employee's overtime account (sub-account under 5304)
            $overtimeAccount = AccHead::where('accountable_type', Employee::class)
                ->where('accountable_id', $employee->id)
                ->where('aname', 'like', 'إضافى حضور%')
                ->first();

            // Get employee's deduction account (sub-account under 4203)
            $deductionAccount = AccHead::where('accountable_type', Employee::class)
                ->where('accountable_id', $employee->id)
                ->where('aname', 'like', 'خصم حضور%')
                ->first();

            // Calculate salary components from processing data
            // Basic salary = actual work hours * hourly rate
            $basicSalary = $processing->salary_due ?? 0;
            
            // Overtime = overtime work hours * hourly rate * overtime multiplier
            $overtimeAmount = 0;
            if ($processing->overtime_work_minutes > 0 && $processing->calculated_salary_for_hour > 0) {
                $overtimeHours = $processing->overtime_work_minutes / 60;
                $overtimeMultiplier = $employee->additional_hour_calculation ?? 1.5;
                $overtimeAmount = $overtimeHours * $processing->calculated_salary_for_hour * $overtimeMultiplier;
            }
            
            // Deductions = late minutes deduction + absent days deduction
            $deductionAmount = 0;
            if ($processing->total_late_minutes > 0 && $processing->calculated_salary_for_hour > 0) {
                $lateHourCalculation = $employee->late_hour_calculation ?? 1.0;
                $deductionAmount += ($processing->total_late_minutes / 60) * $lateHourCalculation * $processing->calculated_salary_for_hour;
            }
            if ($processing->absent_days > 0 && $processing->calculated_salary_for_day > 0) {
                $lateDayCalculation = $employee->late_day_calculation ?? 1.0;
                $deductionAmount += $processing->absent_days * $lateDayCalculation * $processing->calculated_salary_for_day;
            }
            // Add unpaid leave deduction
            if ($processing->unpaid_leave_days > 0 && $processing->calculated_salary_for_day > 0) {
                $deductionAmount += $processing->unpaid_leave_days * $processing->calculated_salary_for_day;
            }

            // Net salary to employee = basic + overtime - deductions
            $netSalary = $basicSalary + $overtimeAmount - $deductionAmount;

            // Only create journal if there's something to record
            if ($basicSalary > 0 || $overtimeAmount > 0 || $deductionAmount > 0) {
                $journalService = app(JournalService::class);
                $lines = [];

                // Debit: Salary Expense (5301) - Basic salary
                if ($basicSalary > 0) {
                    $lines[] = [
                        'account_id' => $salaryExpenseAccount->id,
                        'debit' => $basicSalary,
                        'credit' => 0,
                        'type' => 1,
                        'info' => "راتب الموظف: {$employee->name} - معالجة #{$processingId}",
                    ];
                }

                // Debit: Overtime Account (5304/xxx) - Overtime salary
                if ($overtimeAmount > 0 && $overtimeAccount) {
                    $lines[] = [
                        'account_id' => $overtimeAccount->id,
                        'debit' => round($overtimeAmount, 2),
                        'credit' => 0,
                        'type' => 1,
                        'info' => "إضافي حضور: {$employee->name} - معالجة #{$processingId}",
                    ];
                }

                // Credit: Attendance Deduction Account (4203/xxx) - Deductions
                if ($deductionAmount > 0 && $deductionAccount) {
                    $lines[] = [
                        'account_id' => $deductionAccount->id,
                        'debit' => 0,
                        'credit' => round($deductionAmount, 2),
                        'type' => 0,
                        'info' => "خصم حضور: {$employee->name} - معالجة #{$processingId}",
                    ];
                }

                // Credit: Employee Main Account (2102/xxx) - Net salary payable
                if ($netSalary != 0) {
                    $lines[] = [
                        'account_id' => $employee->account->id,
                        'debit' => $netSalary < 0 ? abs($netSalary) : 0,
                        'credit' => $netSalary > 0 ? $netSalary : 0,
                        'type' => $netSalary > 0 ? 0 : 1,
                        'info' => "راتب الموظف: {$employee->name} - معالجة #{$processingId}",
                    ];
                }

                $meta = [
                    'pro_type' => 74,
                    'date' => now(),
                    'info' => "راتب الموظف: {$employee->name}",
                    'details' => "معالجة البصمات #{$processingId} - الفترة من {$processing->period_start->format('Y-m-d')} إلى {$processing->period_end->format('Y-m-d')}",
                    'emp_id' => $employee->id,
                ];

                $journalId = $journalService->createJournal($lines, $meta);
            }

            // Update processing status to approved
            $processing->status = 'approved';
            $processing->save();

            // Note: Deductions, rewards, and advances are now handled separately
            // They are NOT applied automatically during salary approval

            DB::commit();
            $this->resetPage(); // Refresh pagination
            session()->flash('success', __('hr.processing_approved_successfully'));

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving processing: '.$e->getMessage(), [
                'processing_id' => $processingId,
                'exception' => $e,
            ]);
            session()->flash('error', __('hr.error_approving_processing', ['error' => $e->getMessage()]));
        }
    }

    public function rejectProcessing(int $processingId): void
    {

        try {
            $processing = AttendanceProcessing::findOrFail($processingId);

            $processing->update(['status' => 'rejected']);

            $this->resetPage(); // Refresh pagination
            session()->flash('success', __('hr.processing_rejected_successfully'));

        } catch (\Exception $e) {
            session()->flash('error', __('hr.error_rejecting_processing', ['error' => $e->getMessage()]));
        }
    }

    public function deleteProcessing(int $processingId): void
    {
        try {
            $processing = AttendanceProcessing::findOrFail($processingId);

            // منع حذف المعالجات المعتمدة فقط
            if ($processing->status === 'approved') {
                session()->flash('error', __('hr.cannot_delete_approved_processing'));

                return;
            }

            // حذف المعالجة (سيتم حذف التفاصيل تلقائياً بسبب cascade)
            $processing->delete();

            // إغلاق نافذة التفاصيل إذا كانت مفتوحة
            if ($this->selectedProcessing && $this->selectedProcessing->id == $processingId) {
                $this->closeDetails();
            }

            $this->resetPage(); // Refresh pagination
            session()->flash('success', __('hr.processing_deleted_successfully'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', __('hr.processing_not_found'));
        } catch (\Exception $e) {
            session()->flash('error', __('hr.error_deleting_processing', ['error' => $e->getMessage()]));
        }
    }

    /**
     * Settle advance from employee's salary (deduct from salary modal)
     * Journal: Debit Employee Main, Credit Employee Advance
     */
    public function settleAdvance(float $amount): void
    {
        if (!$this->selectedProcessing) {
            session()->flash('error', 'لا توجد معالجة محددة');
            return;
        }

        if ($amount <= 0) {
            session()->flash('error', 'المبلغ يجب أن يكون أكبر من صفر');
            return;
        }

        DB::beginTransaction();
        try {
            $employee = $this->selectedProcessing->employee;

            // Get employee main account
            $employeeMainAccount = $employee->account;
            if (!$employeeMainAccount) {
                throw new \Exception('حساب الموظف الرئيسي غير موجود');
            }

            // Get employee advance account
            $advanceAccount = AccHead::where('accountable_type', Employee::class)
                ->where('accountable_id', $employee->id)
                ->where('aname', 'like', 'سلف%')
                ->first();

            if (!$advanceAccount) {
                throw new \Exception('حساب سلف الموظف غير موجود');
            }

            $journalService = app(JournalService::class);

            $lines = [
                [
                    'account_id' => $employeeMainAccount->id,
                    'debit' => $amount,
                    'credit' => 0,
                    'type' => 1,
                    'info' => "خصم سلفة من راتب: {$employee->name}",
                ],
                [
                    'account_id' => $advanceAccount->id,
                    'debit' => 0,
                    'credit' => $amount,
                    'type' => 0,
                    'info' => "سداد سلفة من راتب: {$employee->name}",
                ],
            ];

            $journalDate = $this->selectedProcessing->period_end instanceof Carbon 
                ? $this->selectedProcessing->period_end->format('Y-m-d') 
                : Carbon::parse($this->selectedProcessing->period_end)->format('Y-m-d');

            $meta = [
                'pro_type' => 79,
                'date' => $journalDate,
                'info' => "خصم سلفة من راتب الموظف: {$employee->name}",
                'emp_id' => $employee->id,
            ];

            Log::info("Settling Advance for emp {$employee->id} using date {$journalDate}");
            $journalService->createJournal($lines, $meta);

            DB::commit();
            
            // Refresh finalBalance
            $this->viewProcessingDetails($this->selectedProcessing->id);
            
            session()->flash('success', 'تم خصم السلفة من الراتب بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error settling advance: ' . $e->getMessage());
            session()->flash('error', 'خطأ في خصم السلفة: ' . $e->getMessage());
        }
    }

    /**
     * Apply deductions to employee's salary
     * Journal: Debit Employee Main, Credit Deductions Receivable (110602)
     */
    public function applyDeductions(float $amount): void
    {
        if (!$this->selectedProcessing) {
            session()->flash('error', 'لا توجد معالجة محددة');
            return;
        }

        if ($amount <= 0) {
            session()->flash('error', 'المبلغ يجب أن يكون أكبر من صفر');
            return;
        }

        DB::beginTransaction();
        try {
            $employee = $this->selectedProcessing->employee;

            // Get employee main account
            $employeeMainAccount = $employee->account;
            if (!$employeeMainAccount) {
                throw new \Exception('حساب الموظف الرئيسي غير موجود');
            }

            // Get employee deductions receivable account (110602)
            $deductionsReceivableAccount = AccHead::where('accountable_type', Employee::class)
                ->where('accountable_id', $employee->id)
                ->where('aname', 'like', 'جزاءات وخصومات مستحقه%')
                ->first();

            if (!$deductionsReceivableAccount) {
                throw new \Exception('حساب جزاءات وخصومات مستحقه الموظف غير موجود');
            }

            $journalService = app(JournalService::class);

            $lines = [
                [
                    'account_id' => $employeeMainAccount->id,
                    'debit' => $amount,
                    'credit' => 0,
                    'type' => 1,
                    'info' => "تطبيق خصومات على راتب: {$employee->name}",
                ],
                [
                    'account_id' => $deductionsReceivableAccount->id,
                    'debit' => 0,
                    'credit' => $amount,
                    'type' => 0,
                    'info' => "تسوية جزاءات مستحقة: {$employee->name}",
                ],
            ];

            $journalDate = $this->selectedProcessing->period_end instanceof Carbon 
                ? $this->selectedProcessing->period_end->format('Y-m-d') 
                : Carbon::parse($this->selectedProcessing->period_end)->format('Y-m-d');

            $meta = [
                'pro_type' => 75,
                'date' => $journalDate,
                'info' => "تطبيق خصومات على راتب الموظف: {$employee->name}",
                'emp_id' => $employee->id,
            ];

            Log::info("Applying Deductions for emp {$employee->id} using date {$journalDate}");
            $journalService->createJournal($lines, $meta);

            DB::commit();
            
            // Refresh finalBalance
            $this->viewProcessingDetails($this->selectedProcessing->id);
            
            session()->flash('success', 'تم تطبيق الخصومات على الراتب بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error applying deductions: ' . $e->getMessage());
            session()->flash('error', 'خطأ في تطبيق الخصومات: ' . $e->getMessage());
        }
    }

    /**
     * Pay rewards to employee (add to salary)
     * Journal: Debit Rewards Payable (2109), Credit Employee Main
     */
    public function payRewards(float $amount): void
    {
        if (!$this->selectedProcessing) {
            session()->flash('error', 'لا توجد معالجة محددة');
            return;
        }

        if ($amount <= 0) {
            session()->flash('error', 'المبلغ يجب أن يكون أكبر من صفر');
            return;
        }

        DB::beginTransaction();
        try {
            $employee = $this->selectedProcessing->employee;

            // Get employee main account
            $employeeMainAccount = $employee->account;
            if (!$employeeMainAccount) {
                throw new \Exception('حساب الموظف الرئيسي غير موجود');
            }

            // Get employee rewards payable account (2109)
            $rewardsPayableAccount = AccHead::where('accountable_type', Employee::class)
                ->where('accountable_id', $employee->id)
                ->where('aname', 'like', 'مكافآت وحوافز مستحقه%')
                ->first();

            if (!$rewardsPayableAccount) {
                throw new \Exception('حساب مكافآت وحوافز مستحقه الموظف غير موجود');
            }

            $journalService = app(JournalService::class);

            $lines = [
                [
                    'account_id' => $rewardsPayableAccount->id,
                    'debit' => $amount,
                    'credit' => 0,
                    'type' => 1,
                    'info' => "صرف مكافآت مستحقة: {$employee->name}",
                ],
                [
                    'account_id' => $employeeMainAccount->id,
                    'debit' => 0,
                    'credit' => $amount,
                    'type' => 0,
                    'info' => "إضافة مكافآت للراتب: {$employee->name}",
                ],
            ];

            $journalDate = $this->selectedProcessing->period_end instanceof Carbon 
                ? $this->selectedProcessing->period_end->format('Y-m-d') 
                : Carbon::parse($this->selectedProcessing->period_end)->format('Y-m-d');

            $meta = [
                'pro_type' => 76,
                'date' => $journalDate,
                'info' => "صرف مكافآت للموظف: {$employee->name}",
                'emp_id' => $employee->id,
            ];

            Log::info("Paying Rewards for emp {$employee->id} using date {$journalDate}");
            $journalService->createJournal($lines, $meta);

            DB::commit();
            
            // Refresh finalBalance
            $this->viewProcessingDetails($this->selectedProcessing->id);
            
            session()->flash('success', 'تم صرف المكافآت للراتب بنجاح');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error paying rewards: ' . $e->getMessage());
            session()->flash('error', 'خطأ في صرف المكافآت: ' . $e->getMessage());
        }
    }

    public function getEmployeesProperty(): Collection
    {
        return Employee::with('department')
            ->where('status', 'مفعل') // Only active employees
            ->orderBy('name')
            ->get();
    }

    public function getDepartmentsProperty(): Collection
    {
        return Department::orderBy('title')->get();
    }

    /**
     * Called when any property is updated
     */
    public function updated($propertyName)
    {
        // Clear validation errors for the updated field
        $this->resetErrorBag($propertyName);
    }

    public function render()
    {
        return view('hr::livewire.hr-management.attendances.processing.attendance-processing-manager', [
            'employees' => $this->employees,
            'departments' => $this->departments,
            'processings' => $this->processings,
        ]);
    }
}
