<?php

declare(strict_types=1);

namespace App\Livewire;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use App\Models\AttendanceProcessing;
use App\Models\AttendanceProcessingDetail;
use App\Services\AttendanceProcessingService;
use Modules\Accounts\Services\AccountService;
use Modules\Accounts\Models\AccHead;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class AttendanceProcessingManager extends Component
{
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
    /** @var Collection<int, AttendanceProcessing> */
    public ?Collection $processings = null;
    public ?AttendanceProcessing $selectedProcessing = null;
    public bool $showDetails = false;
    /** @var Collection<int, AttendanceProcessingDetail> */
    public ?Collection $processingDetails = null;

    protected AttendanceProcessingService $attendanceProcessingService;

    public function boot(): void
    {
        $this->attendanceProcessingService = app(AttendanceProcessingService::class);
    }

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
        $this->loadProcessings();
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
                        return;
                    }
                    break;
                    
                default:
                    throw new \Exception('نوع معالجة غير صحيح');
            }
            
            $this->processingResults = $results;
            $this->showResults = true;
            $this->resetSelection();
            $this->loadProcessings();
            
            session()->flash('success', 'تم معالجة الحضور بنجاح');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'لم يتم العثور على البيانات المطلوبة');
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء معالجة الحضور: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function loadProcessings(): void
    {
        
        try {
            $this->processings = AttendanceProcessing::with(['employee', 'department'])
                ->orderBy('created_at', 'desc')
                ->limit(20)
                ->get();
            
        } catch (\Exception $e) {
            $this->processings = collect(); // Empty collection as fallback
        }
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
    }

    public function approveProcessing(int $processingId): void
    {
        DB::beginTransaction();
        
        try {
            $processing = AttendanceProcessing::findOrFail($processingId);
            $employee = Employee::with('account')->findOrFail($processing->employee_id);
            $debitAccount = AccHead::where('code', 5301)->first()->id;
            $data = [
                'pro_type' => 74,
                'processing_id' => $processingId,
                'total' => $processing->total_salary,
                'debit_Account_id' => $debitAccount,
                'credit_Account_id' => $employee->account->id,
                'op_id' => $processing->id,
            ];
            if ($processing->total_salary < 0) {
                $accountService = app(AccountService::class);
                // create journal head and create journal detail
                $accountService->createJournalHead($data);
                // update processing status to approved
                $processing->status = 'approved';
                $processing->save();
            }
            
            DB::commit();
            $this->loadProcessings();
            session()->flash('success', __('hr.processing_approved_successfully'));
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving processing: ' . $e->getMessage(), [
                'processing_id' => $processingId,
                'exception' => $e
            ]);
            session()->flash('error', __('hr.error_approving_processing', ['error' => $e->getMessage()]));
        }
    }

    public function rejectProcessing(int $processingId): void
    {
        
        try {
            $processing = AttendanceProcessing::findOrFail($processingId);
            
            $processing->update(['status' => 'rejected']);
            
            $this->loadProcessings();
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
            
            $this->loadProcessings();
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
        ]);
    }
}