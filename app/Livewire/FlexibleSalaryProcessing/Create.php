<?php

declare(strict_types=1);

namespace App\Livewire\FlexibleSalaryProcessing;

use App\Models\Department;
use App\Models\Employee;
use App\Models\FlexibleSalaryProcessing;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Create extends Component
{
    public string $processingType = 'single';

    public ?int $selectedEmployee = null;

    public ?int $selectedDepartment = null;

    /** @var array<int, float> */
    public array $employeeHours = [];

    public string $startDate = '';

    public string $endDate = '';

    public string $notes = '';

    public bool $isProcessing = false;

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
        // تعيين التاريخ الافتراضي للشهر الحالي
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
    }

    public function store(): void
    {
        $this->validate();

        $this->isProcessing = true;

        try {
            $startDate = Carbon::parse($this->startDate);
            $endDate = Carbon::parse($this->endDate);

            $results = [];
            $errors = [];

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
                            $errorMsg = $e->getMessage();
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

            if (! empty($errors)) {
                if (! empty($results)) {
                    $errorMessage = 'تم معالجة '.count($results).' موظف بنجاح، ولكن فشلت معالجة '.count($errors).' موظف:';
                    session()->flash('warning', $errorMessage);
                    session()->flash('error_details', $errors);
                } else {
                    $errorMessage = 'فشلت معالجة جميع الموظفين ('.count($errors).' موظف):';
                    session()->flash('error', $errorMessage);
                    session()->flash('error_details', $errors);
                }
            } elseif (! empty($results)) {
                session()->flash('success', 'تم معالجة الرواتب بنجاح لـ '.count($results).' موظف');
                $this->redirect(route('flexible-salary.processing.index'), navigate: true);
                return;
            } else {
                session()->flash('error', 'لم يتم معالجة أي موظف. يرجى التحقق من البيانات المدخلة.');
            }
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
            $existingProcessing = FlexibleSalaryProcessing::where('employee_id', $employee->id)
                ->where('period_start', $startDate->format('Y-m-d'))
                ->where('period_end', $endDate->format('Y-m-d'))
                ->lockForUpdate()
                ->first();

            if ($existingProcessing) {
                if ($existingProcessing->status === 'rejected') {
                    $existingProcessing->delete();
                } elseif ($existingProcessing->status === 'approved') {
                    throw new \Exception("تمت معالجة الراتب بالفعل في الفترة من {$startDate->format('Y-m-d')} إلى {$endDate->format('Y-m-d')} (الحالة: معتمدة). لا يمكن إنشاء معالجة جديدة لنفس الفترة.");
                } elseif ($existingProcessing->status === 'pending') {
                    throw new \Exception("تمت معالجة الراتب بالفعل في الفترة من {$startDate->format('Y-m-d')} إلى {$endDate->format('Y-m-d')} (الحالة: قيد المراجعة). لا يمكن إنشاء معالجة جديدة لنفس الفترة. يمكنك تعديل المعالجة الموجودة.");
                } else {
                    throw new \Exception("تمت معالجة الراتب بالفعل في الفترة من {$startDate->format('Y-m-d')} إلى {$endDate->format('Y-m-d')}. لا يمكن إنشاء معالجة جديدة لنفس الفترة.");
                }
            }

            $overlappingApproved = FlexibleSalaryProcessing::where('employee_id', $employee->id)
                ->where('status', 'approved')
                ->where(function ($query) use ($startDate, $endDate) {
                    $query->where(function ($q) use ($startDate, $endDate) {
                        $q->whereBetween('period_start', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
                            ->orWhereBetween('period_end', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
                    })
                        ->orWhere(function ($q) use ($startDate, $endDate) {
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

    public function getEmployeesProperty(): Collection
    {
        $query = Employee::with('department')
            ->where('salary_type', 'ثابت + ساعات عمل مرن')
            ->where('status', 'مفعل')
            ->orderBy('name');

        if ($this->processingType === 'single') {
            // For single employee selection, return all eligible employees
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

    public function render()
    {
        return view('livewire.flexible-salary-processing.create', [
            'employees' => $this->employees,
            'departments' => $this->departments,
        ]);
    }
}

