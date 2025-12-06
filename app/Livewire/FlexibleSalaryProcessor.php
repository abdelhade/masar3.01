<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Department;
use App\Models\Employee;
use App\Models\FlexibleSalaryProcessing;
use App\Services\JournalService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Accounts\Models\AccHead;

class FlexibleSalaryProcessor extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public string $processingType = 'single';

    public ?int $selectedEmployee = null;

    public ?int $selectedDepartment = null;

    /** @var array<int, float> */
    public array $employeeHours = [];

    public string $startDate = '';

    public string $endDate = '';

    public string $notes = '';

    public bool $isProcessing = false;

    public bool $showResults = false;

    /** @var array<string, mixed> */
    public array $processingResults = [];

    public bool $showEditModal = false;

    public bool $showCreateModal = false;

    public ?int $editingProcessingId = null;

    public float $editingHoursWorked = 0;

    public string $editingNotes = '';

    protected $rules = [
        'processingType' => 'required|in:single,department',
        'startDate' => 'required|date',
        'endDate' => 'required|date|after_or_equal:startDate',
        'selectedEmployee' => 'required_if:processingType,single',
        'selectedDepartment' => 'required_if:processingType,department',
        'employeeHours.*' => 'nullable|numeric|min:0.01',
    ];

    protected $messages = [
        'selectedEmployee.required_if' => 'يرجى اختيار موظف',
        'selectedDepartment.required_if' => 'يرجى اختيار قسم',
        'startDate.required' => 'تاريخ البداية مطلوب',
        'endDate.required' => 'تاريخ النهاية مطلوب',
        'endDate.after_or_equal' => 'تاريخ النهاية يجب أن يكون بعد أو يساوي تاريخ البداية',
    ];

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function updatedProcessingType(): void
    {
        $this->resetSelection();
        $this->resetErrorBag();
    }

    public function resetSelection(): void
    {
        $this->selectedEmployee = null;
        $this->selectedDepartment = null;
        $this->employeeHours = [];
        $this->showResults = false;
        $this->processingResults = [];
    }

    public function processSalary(): void
    {
        $this->validate();

        $this->isProcessing = true;

        try {
            $startDate = Carbon::parse($this->startDate);
            $endDate = Carbon::parse($this->endDate);

            $results = [];
            $errors = []; // مصفوفة لجمع جميع الأخطاء

            if ($this->processingType === 'single') {
                $employee = Employee::findOrFail($this->selectedEmployee);

                if ($employee->salary_type !== 'ثابت + ساعات عمل مرن') {
                    session()->flash('error', 'نوع راتب الموظف ليس "ثابت + ساعات عمل مرن"');
                    $this->isProcessing = false;

                    return;
                }

                $hoursWorked = (float) ($this->employeeHours[$employee->id] ?? 0);
                if ($hoursWorked <= 0) {
                    session()->flash('error', 'يرجى إدخال عدد الساعات');
                    $this->isProcessing = false;

                    return;
                }

                try {
                    $result = $this->processEmployeeSalary($employee, $startDate, $endDate, $hoursWorked);
                    $results[] = $result;
                } catch (\Exception $e) {
                    $errors[] = "الموظف {$employee->name}: {$e->getMessage()}";
                    Log::warning('Failed to process salary for employee: '.$employee->name, [
                        'employee_id' => $employee->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } else {
                $department = Department::findOrFail($this->selectedDepartment);
                $employees = Employee::where('department_id', $department->id)
                    ->where('salary_type', 'ثابت + ساعات عمل مرن')
                    ->where('status', 'مفعل')
                    ->get();

                if ($employees->isEmpty()) {
                    session()->flash('error', 'لا يوجد موظفين من نوع "ثابت + ساعات عمل مرن" في هذا القسم');
                    $this->isProcessing = false;

                    return;
                }

                foreach ($employees as $employee) {
                    $hoursWorked = (float) ($this->employeeHours[$employee->id] ?? 0);
                    if ($hoursWorked > 0) {
                        try {
                            $result = $this->processEmployeeSalary($employee, $startDate, $endDate, $hoursWorked);
                            $results[] = $result;
                        } catch (\Exception $e) {
                            // جمع الخطأ في مصفوفة الأخطاء مع تنسيق أفضل
                            $errorMsg = $e->getMessage();
                            // إزالة التكرار في الرسالة (إذا كانت الرسالة تحتوي على اسم الموظف مرتين)
                            if (str_contains($errorMsg, $employee->name)) {
                                $errors[] = $errorMsg;
                            } else {
                                $errors[] = "الموظف {$employee->name}: {$errorMsg}";
                            }
                            Log::warning('Failed to process salary for employee: '.$employee->name, [
                                'employee_id' => $employee->id,
                                'error' => $e->getMessage(),
                            ]);
                        }
                    }
                }
            }

            // عرض النتائج والأخطاء
            if (! empty($results)) {
                $this->processingResults = $results;
                $this->showResults = true;
            }

            // عرض رسائل الخطأ إذا كانت موجودة
            if (! empty($errors)) {
                if (! empty($results)) {
                    // إذا نجحت بعض المعالجات وفشلت أخرى
                    $errorMessage = 'تم معالجة '.count($results).' موظف بنجاح، ولكن فشلت معالجة '.count($errors).' موظف:';
                    session()->flash('warning', $errorMessage);
                    // حفظ الأخطاء في session منفصلة لعرضها بشكل أفضل
                    session()->flash('error_details', $errors);
                } else {
                    // إذا فشلت جميع المعالجات
                    $errorMessage = 'فشلت معالجة جميع الموظفين ('.count($errors).' موظف):';
                    session()->flash('error', $errorMessage);
                    // حفظ الأخطاء في session منفصلة لعرضها بشكل أفضل
                    session()->flash('error_details', $errors);
                }
            } elseif (! empty($results)) {
                // إذا نجحت جميع المعالجات
                session()->flash('success', 'تم معالجة الرواتب بنجاح لـ '.count($results).' موظف');
                // إغلاق الـ modal بعد النجاح
                $this->closeCreateModal();
            } else {
                // إذا لم يتم معالجة أي موظف
                session()->flash('error', 'لم يتم معالجة أي موظف. يرجى التحقق من البيانات المدخلة.');
            }

            $this->resetSelection();
            $this->resetPage();
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء معالجة الرواتب: '.$e->getMessage());
            Log::error('Error processing flexible salary: '.$e->getMessage(), [
                'exception' => $e,
            ]);
        } finally {
            $this->isProcessing = false;
        }
    }

    private function processEmployeeSalary(Employee $employee, Carbon $startDate, Carbon $endDate, float $hoursWorked): array
    {
        DB::beginTransaction();

        try {
            // التحقق من وجود معالجة سابقة لنفس الموظف في نفس الفترة بالضبط
            // استخدام lockForUpdate لمنع race conditions
            $existingProcessing = FlexibleSalaryProcessing::where('employee_id', $employee->id)
                ->where('period_start', $startDate->format('Y-m-d'))
                ->where('period_end', $endDate->format('Y-m-d'))
                ->lockForUpdate()
                ->first();

            if ($existingProcessing) {
                // السماح بإنشاء معالجة جديدة فقط إذا كانت المعالجة السابقة مرفوضة
                if ($existingProcessing->status === 'rejected') {
                    // حذف المعالجة المرفوضة قبل إنشاء معالجة جديدة
                    $existingProcessing->delete();
                    // يمكن المتابعة لإنشاء معالجة جديدة
                } elseif ($existingProcessing->status === 'approved') {
                    // منع إنشاء معالجة جديدة إذا كانت المعالجة معتمدة
                    throw new \Exception("تمت معالجة الراتب بالفعل في الفترة من {$startDate->format('Y-m-d')} إلى {$endDate->format('Y-m-d')} (الحالة: معتمدة). لا يمكن إنشاء معالجة جديدة لنفس الفترة.");
                } elseif ($existingProcessing->status === 'pending') {
                    // منع إنشاء معالجة جديدة إذا كانت المعالجة قيد المراجعة
                    throw new \Exception("تمت معالجة الراتب بالفعل في الفترة من {$startDate->format('Y-m-d')} إلى {$endDate->format('Y-m-d')} (الحالة: قيد المراجعة). لا يمكن إنشاء معالجة جديدة لنفس الفترة. يمكنك تعديل المعالجة الموجودة.");
                } else {
                    throw new \Exception("تمت معالجة الراتب بالفعل في الفترة من {$startDate->format('Y-m-d')} إلى {$endDate->format('Y-m-d')}. لا يمكن إنشاء معالجة جديدة لنفس الفترة.");
                }
            }

            // التحقق من وجود معالجة معتمدة في أي فترة متداخلة (فترة تتداخل مع الفترة المحددة)
            // هذا يمنع إنشاء معالجة جديدة إذا كانت هناك معالجة معتمدة في فترة متداخلة
            $overlappingApproved = FlexibleSalaryProcessing::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where(function ($q) use ($startDate, $endDate) {
                        // الفترة الجديدة تبدأ داخل فترة موجودة
                        $q->whereBetween('period_start', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->orWhereBetween('period_end', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                    })
                        ->orWhere(function ($q) use ($startDate, $endDate) {
                            // الفترة الموجودة تبدأ داخل الفترة الجديدة
                            $q->where('period_start', '<=', $startDate->format('Y-m-d'))
                                ->where('period_end', '>=', $endDate->format('Y-m-d'));
                        });
                })
                ->first();

            if ($overlappingApproved) {
                throw new \Exception("يوجد معالجة معتمدة للموظف {$employee->name} في فترة متداخلة مع الفترة المحددة (من {$overlappingApproved->period_start->format('Y-m-d')} إلى {$overlappingApproved->period_end->format('Y-m-d')}). لا يمكن إنشاء معالجة جديدة.");
            }

            $fixedSalary = $employee->salary;
            $hourlyWage = $employee->flexible_hourly_wage ?? 0;
            $flexibleSalary = $hoursWorked * $hourlyWage;
            $totalSalary = $fixedSalary + $flexibleSalary;

            $processing = FlexibleSalaryProcessing::create([
                'employee_id' => $employee->id,
                'department_id' => $employee->department_id,
                'period_start' => $startDate->format('Y-m-d'),
                'period_end' => $endDate->format('Y-m-d'),
                'fixed_salary' => $fixedSalary,
                'hours_worked' => $hoursWorked,
                'hourly_wage' => $hourlyWage,
                'total_salary' => $totalSalary,
                'status' => 'pending',
                'notes' => $this->notes,
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return [
                'processing_id' => $processing->id,
                'employee_id' => $employee->id,
                'employee_name' => $employee->name,
                'fixed_salary' => $fixedSalary,
                'hours_worked' => $hoursWorked,
                'hourly_wage' => $hourlyWage,
                'flexible_salary' => $flexibleSalary,
                'total_salary' => $totalSalary,
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function approveProcessing(int $processingId): void
    {
        DB::beginTransaction();

        try {
            $processing = FlexibleSalaryProcessing::with('employee')->findOrFail($processingId);

            // Check if already approved
            if ($processing->status === 'approved') {
                session()->flash('error', 'المعالجة معتمدة بالفعل');
                DB::rollBack();

                return;
            }

            $employee = $processing->employee;

            if (! $employee->account) {
                throw new \Exception('حساب الموظف غير موجود');
            }

            // Create journal entry using JournalService
            $debitAccount = AccHead::where('code', '5301')->first();
            if (! $debitAccount) {
                throw new \Exception('حساب رواتب الموظفين (5301) غير موجود');
            }

            $journalService = app(JournalService::class);

            $lines = [
                [
                    'account_id' => $debitAccount->id,
                    'debit' => $processing->total_salary,
                    'credit' => 0,
                    'type' => 1,
                    'info' => "راتب مرن للموظف: {$employee->name} - معالجة #{$processingId}",
                ],
                [
                    'account_id' => $employee->account->id,
                    'debit' => 0,
                    'credit' => $processing->total_salary,
                    'type' => 0,
                    'info' => "راتب مرن للموظف: {$employee->name} - معالجة #{$processingId}",
                ],
            ];

            $meta = [
                'pro_type' => 77,
                'date' => now(),
                'info' => "راتب مرن للموظف: {$employee->name}",
                'details' => "معالجة الراتب المرن #{$processingId} - الفترة من {$processing->period_start->format('Y-m-d')} إلى {$processing->period_end->format('Y-m-d')}",
                'emp_id' => $employee->id,
            ];

            $journalId = $journalService->createJournal($lines, $meta);

            // Update processing status
            $processing->status = 'approved';
            $processing->journal_id = $journalId;
            $processing->save();

            DB::commit();
            $this->resetPage();
            session()->flash('success', 'تم الموافقة على المعالجة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving flexible salary processing: '.$e->getMessage(), [
                'processing_id' => $processingId,
                'exception' => $e,
            ]);
            session()->flash('error', 'حدث خطأ أثناء الموافقة: '.$e->getMessage());
        }
    }

    public function rejectProcessing(int $processingId): void
    {
        try {
            $processing = FlexibleSalaryProcessing::findOrFail($processingId);
            $processing->status = 'rejected';
            $processing->save();

            $this->resetPage();
            session()->flash('success', 'تم رفض المعالجة بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء الرفض: '.$e->getMessage());
        }
    }

    public function openEditModal(int $processingId): void
    {
        try {
            $processing = FlexibleSalaryProcessing::with('employee')->findOrFail($processingId);

            if ($processing->status !== 'pending') {
                session()->flash('error', 'يمكن تعديل المعالجة فقط إذا كانت قيد المراجعة');
                return;
            }

            $this->editingProcessingId = $processingId;
            $this->editingHoursWorked = (float) $processing->hours_worked;
            $this->editingNotes = $processing->notes ?? '';
            $this->showEditModal = true;
            
            // إجبار Livewire على تحديث الـ DOM
            $this->dispatch('$refresh');
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء فتح نافذة التعديل: '.$e->getMessage());
            Log::error('Error opening edit modal: '.$e->getMessage(), [
                'processing_id' => $processingId,
                'exception' => $e,
            ]);
        }
    }

    public function openCreateModal(): void
    {
        $this->resetForm();
        $this->showCreateModal = true;
    }

    public function closeCreateModal(): void
    {
        $this->showCreateModal = false;
        $this->resetForm();
    }

    private function resetForm(): void
    {
        $this->processingType = 'single'; // القيمة الافتراضية هي 'single' وليس فارغة
        $this->selectedEmployee = null;
        $this->selectedDepartment = null;
        $this->startDate = '';
        $this->endDate = '';
        $this->notes = '';
        $this->employeeHours = [];
        $this->showResults = false;
        $this->processingResults = [];
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingProcessingId = null;
        $this->editingHoursWorked = 0;
        $this->editingNotes = '';
    }

    public function updateProcessing(): void
    {
        $this->validate([
            'editingHoursWorked' => 'required|numeric|min:0.01',
        ], [
            'editingHoursWorked.required' => 'عدد الساعات مطلوب',
            'editingHoursWorked.numeric' => 'عدد الساعات يجب أن يكون رقماً',
            'editingHoursWorked.min' => 'عدد الساعات يجب أن يكون أكبر من صفر',
        ]);

        DB::beginTransaction();

        try {
            $processing = FlexibleSalaryProcessing::with('employee')->findOrFail($this->editingProcessingId);

            if ($processing->status !== 'pending') {
                throw new \Exception('يمكن تعديل المعالجة فقط إذا كانت قيد المراجعة');
            }

            $employee = $processing->employee;
            $fixedSalary = $employee->salary;
            $hourlyWage = $employee->flexible_hourly_wage ?? 0;
            $flexibleSalary = $this->editingHoursWorked * $hourlyWage;
            $totalSalary = $fixedSalary + $flexibleSalary;

            $processing->update([
                'hours_worked' => $this->editingHoursWorked,
                'total_salary' => $totalSalary,
                'notes' => $this->editingNotes,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();
            $this->closeEditModal();
            $this->resetPage();
            session()->flash('success', 'تم تحديث المعالجة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating flexible salary processing: '.$e->getMessage(), [
                'processing_id' => $this->editingProcessingId,
                'exception' => $e,
            ]);
            session()->flash('error', 'حدث خطأ أثناء التحديث: '.$e->getMessage());
        }
    }

    public function getEmployeesProperty(): Collection
    {
        $query = Employee::with('department')
            ->where('salary_type', 'ثابت + ساعات عمل مرن')
            ->where('status', 'مفعل')
            ->orderBy('name');

        if ($this->processingType === 'single') {
            // For single employee selection, return all eligible employees
            // The selected employee will be filtered in the view
        } elseif ($this->processingType === 'department' && $this->selectedDepartment) {
            // For department selection, filter by department
            $query->where('department_id', $this->selectedDepartment);
        }

        return $query->get();
    }

    public function getDepartmentsProperty(): Collection
    {
        return Department::orderBy('title')->get();
    }

    public function getProcessingsProperty()
    {
        return FlexibleSalaryProcessing::with(['employee', 'department'])
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.flexible-salary-processor', [
            'employees' => $this->employees,
            'departments' => $this->departments,
            'processings' => $this->processings,
        ]);
    }
}
