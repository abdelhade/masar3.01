<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\AttendanceProcessing;
use App\Models\AttendanceProcessingDetail;
use App\Models\Department;
use App\Models\Employee;
use App\Services\AttendanceProcessingService;
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

    /** @var Collection<int, \App\Models\EmployeeAdvance> */
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

            // Load deductions, rewards, and advances for the period
            $startDate = Carbon::parse($this->selectedProcessing->period_start);
            $endDate = Carbon::parse($this->selectedProcessing->period_end);

            $deductionService = app(\App\Services\EmployeeDeductionRewardService::class);

            // Load deductions and rewards summary
            $this->deductionsRewardsSummary = $deductionService->getMonthlySummary(
                $this->selectedProcessing->employee,
                $startDate,
                $endDate
            );

            // Load advances summary
            $this->advancesSummary = \App\Models\EmployeeAdvance::where('employee_id', $this->selectedProcessing->employee_id)
                ->where('status', 'approved')
                ->whereBetween('date', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                ->get();

            // Load final balance
            $this->finalBalance = $deductionService->getEmployeeAccountBalance(
                $this->selectedProcessing->employee,
                $startDate,
                $endDate
            );

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

            // If total salary is negative (employee owes the company), don't create journal entry
            // This means the employee was absent more than present, so no salary payment is due
            if ($processing->total_salary >= 0) {
                // If salary is positive or zero, create journal entry for payment using JournalService
                $debitAccount = AccHead::where('code', '5301')->first();
                if (! $debitAccount) {
                    throw new \Exception('حساب رواتب الموظفين (5301) غير موجود');
                }

                if (! $employee->account) {
                    throw new \Exception('حساب الموظف غير موجود');
                }

                $journalService = app(JournalService::class);

                $lines = [
                    [
                        'account_id' => $debitAccount->id,
                        'debit' => $processing->total_salary,
                        'credit' => 0,
                        'type' => 1,
                        'info' => "راتب الموظف: {$employee->name} - معالجة #{$processingId}",
                    ],
                    [
                        'account_id' => $employee->account->id,
                        'debit' => 0,
                        'credit' => $processing->total_salary,
                        'type' => 0,
                        'info' => "راتب الموظف: {$employee->name} - معالجة #{$processingId}",
                    ],
                ];

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

            // Apply deductions and rewards (create journal entries for them)
            $attendanceProcessingService = app(AttendanceProcessingService::class);
            $attendanceProcessingService->applyDeductionsAndRewards($processingId);

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
        return view('livewire.hr-management.attendances.processing.attendance-processing-manager', [
            'employees' => $this->employees,
            'departments' => $this->departments,
            'processings' => $this->processings,
        ]);
    }
}
