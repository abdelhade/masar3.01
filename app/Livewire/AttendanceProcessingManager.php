<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Employee;
use App\Models\Department;
use App\Models\AttendanceProcessing;
use App\Models\AttendanceProcessingDetail;
use App\Services\AttendanceProcessingService;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class AttendanceProcessingManager extends Component
{
    public $processingType = 'single';
    public $selectedEmployee = null;
    public $selectedDepartment = null;
    public $selectedEmployees = [];
    public $startDate;
    public $endDate;
    public $notes = '';
    public $isProcessing = false;
    public $showResults = false;
    public $processingResults = [];
    public $processings;
    public $selectedProcessing = null;
    public $showDetails = false;
    public $processingDetails = [];

    protected $attendanceProcessingService;

    public function boot()
    {
        $this->attendanceProcessingService = app(AttendanceProcessingService::class);
    }

    public function mount()
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
    }

    public function resetSelection()
    {
        $this->selectedEmployee = null;
        $this->selectedDepartment = null;
        $this->selectedEmployees = [];
        $this->showResults = false;
        $this->processingResults = [];
    }

    public function processAttendance()
    {
        try {
            // Validate the form
            $this->validate();
            
            // Additional validation for selectedEmployees
            if ($this->processingType === 'multiple' && empty($this->selectedEmployees)) {
                session()->flash('error', 'يرجى اختيار موظف واحد على الأقل');
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
                    
                    // Check if there was an error (like duplicate processing)
                    if (isset($results['error'])) {
                        session()->flash('error', $results['error']);
                        if (isset($results['existing_processing_id'])) {
                            session()->flash('info', 'يمكنك عرض المعالجة الموجودة من الجدول أدناه');
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
                    break;
                    
                case 'department':
                    $department = Department::findOrFail($this->selectedDepartment);
                    
                    $results = $this->attendanceProcessingService->processDepartment(
                        $department,
                        $startDate,
                        $endDate,
                        $this->notes
                    );
                    break;
                    
                default:
                    throw new \Exception('نوع معالجة غير صحيح');
            }
            
            $this->processingResults = $results;
            $this->showResults = true;
            $this->loadProcessings();
            
            session()->flash('success', 'تم معالجة الحضور بنجاح');
            
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            session()->flash('error', 'لم يتم العثور على البيانات المطلوبة');
        } catch (\Exception $e) {
            Log::error('Error processing attendance: ' . $e->getMessage());
            session()->flash('error', 'حدث خطأ أثناء معالجة الحضور: ' . $e->getMessage());
        } finally {
            $this->isProcessing = false;
        }
    }

    public function loadProcessings()
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

    public function viewProcessingDetails($processingId)
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
            session()->flash('error', 'حدث خطأ أثناء تحميل تفاصيل المعالجة');
        }
    }

    public function closeDetails()
    {
        $this->showDetails = false;
        $this->selectedProcessing = null;
        $this->processingDetails = [];
    }

    public function approveProcessing($processingId)
    {
        
        try {
            $processing = AttendanceProcessing::findOrFail($processingId);

            $processing->update(['status' => 'approved']);
            
            $this->loadProcessings();
            session()->flash('success', 'تم اعتماد المعالجة بنجاح');
            
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء اعتماد المعالجة: ' . $e->getMessage());
        }
    }

    public function rejectProcessing($processingId)
    {
        
        try {
            $processing = AttendanceProcessing::findOrFail($processingId);
            
            $processing->update(['status' => 'rejected']);
            
            $this->loadProcessings();
            session()->flash('success', 'تم رفض المعالجة');
            
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء رفض المعالجة: ' . $e->getMessage());
        }
    }

    public function getEmployeesProperty(): Collection
    {
        return Employee::with('department')->orderBy('name')->get();
    }

    public function getDepartmentsProperty(): Collection
    {
        return Department::orderBy('title')->get();
    }

    /**
     * Debug method to log form state
     */
    public function debugFormState()
    {
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