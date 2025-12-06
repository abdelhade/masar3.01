<?php

declare(strict_types=1);

namespace App\Livewire\EmployeeAdvances;

use App\Models\Employee;
use App\Models\EmployeeAdvance;
use App\Services\JournalService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;
use Modules\Accounts\Models\AccHead;

class ManageEmployeeAdvances extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool $showForm = false;

    public ?int $editingId = null;

    public ?int $selectedEmployee = null;

    public float $amount = 0;

    public string $date = '';

    public string $reason = '';

    public ?string $notes = null;

    protected $rules = [
        'selectedEmployee' => 'required|exists:employees,id',
        'amount' => 'required|numeric|min:0.01',
        'date' => 'required|date',
        'reason' => 'required|string|max:255',
        'notes' => 'nullable|string',
    ];

    protected $messages = [
        'selectedEmployee.required' => 'يرجى اختيار موظف',
        'amount.required' => 'المبلغ مطلوب',
        'amount.min' => 'المبلغ يجب أن يكون أكبر من صفر',
        'date.required' => 'التاريخ مطلوب',
        'reason.required' => 'السبب مطلوب',
    ];

    public function mount(): void
    {
        $this->date = now()->format('Y-m-d');
    }

    public function openForm(?int $id = null): void
    {
        $this->editingId = $id;
        $this->showForm = true;

        if ($id) {
            $advance = EmployeeAdvance::findOrFail($id);
            $this->selectedEmployee = $advance->employee_id;
            $this->amount = (float) $advance->amount; // Convert decimal string to float
            $this->date = $advance->date->format('Y-m-d');
            $this->reason = $advance->reason;
            $this->notes = $advance->notes;
        } else {
            $this->resetForm();
        }
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->selectedEmployee = null;
        $this->amount = 0;
        $this->date = now()->format('Y-m-d');
        $this->reason = '';
        $this->notes = null;
        $this->resetErrorBag();
    }

    public function save(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $data = [
                'employee_id' => $this->selectedEmployee,
                'amount' => $this->amount,
                'date' => $this->date,
                'reason' => $this->reason,
                'notes' => $this->notes,
                'created_by' => Auth::id(),
            ];

            if ($this->editingId) {
                $advance = EmployeeAdvance::findOrFail($this->editingId);

                // Can't edit if approved
                if ($advance->isApproved()) {
                    session()->flash('error', 'لا يمكن تعديل سلف معتمد');
                    DB::rollBack();

                    return;
                }

                $data['updated_by'] = Auth::id();
                $advance->update($data);
                session()->flash('success', 'تم تحديث السلف بنجاح');
            } else {
                EmployeeAdvance::create($data);
                session()->flash('success', 'تم إضافة السلف بنجاح');
            }

            DB::commit();
            $this->closeForm();
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving employee advance: '.$e->getMessage(), [
                'exception' => $e,
            ]);
            session()->flash('error', 'حدث خطأ أثناء الحفظ: '.$e->getMessage());
        }
    }

    public function approve(int $id): void
    {
        DB::beginTransaction();

        try {
            $advance = EmployeeAdvance::with('employee')->findOrFail($id);

            if ($advance->isApproved()) {
                session()->flash('error', 'السلف معتمد بالفعل');
                DB::rollBack();

                return;
            }

            $employee = $advance->employee;

            // Get employee's advance account
            $employeeAdvanceAccount = AccHead::where('accountable_type', Employee::class)
                ->where('accountable_id', $employee->id)
                ->where('aname', 'like', '%سلف%')
                ->first();

            if (! $employeeAdvanceAccount) {
                throw new \Exception('حساب السلف للموظف غير موجود');
            }

            // Get parent advance account (110601)
            $parentAdvanceAccount = AccHead::where('code', '110601')->first();
            if (! $parentAdvanceAccount) {
                throw new \Exception('حساب السلف الرئيسي (110601) غير موجود');
            }

            // Get employee's main salary account (under 2102)
            $employeeMainAccount = $employee->account;
            if (! $employeeMainAccount) {
                throw new \Exception('حساب الموظف الرئيسي غير موجود');
            }

            // Create journal entry using JournalService
            // عند إعطاء سلفة جديدة: Debit حساب السلف، Credit النقدية/البنك
            // لكن المستخدم يريد: عند الاستحقاق يتم استقطاع السلف من الراتب
            // لذلك: عند إعطاء السلف نقداً:
            // Debit: حساب سلف الموظف (110601)
            // Credit: النقدية/البنك (101/102)
            // وعند الاستحقاق (في معالجة البصمات) يتم استقطاع السلف من حساب الموظف

            // للحصول على حساب النقدية/البنك - نستخدم حساب افتراضي أو نطلب من المستخدم
            $cashAccount = AccHead::where('code', '110101')->first(); // الصندوق الرئيسي
            if (! $cashAccount) {
                throw new \Exception('حساب الصندوق الرئيسي (110101) غير موجود');
            }

            $journalService = app(JournalService::class);

            $lines = [
                [
                    'account_id' => $employeeAdvanceAccount->id,
                    'debit' => $advance->amount,
                    'credit' => 0,
                    'type' => 1,
                    'info' => "سلف للموظف: {$employee->name} - {$advance->reason}",
                ],
                [
                    'account_id' => $cashAccount->id,
                    'debit' => 0,
                    'credit' => $advance->amount,
                    'type' => 0,
                    'info' => "سلف للموظف: {$employee->name} - {$advance->reason}",
                ],
            ];

            $meta = [
                'pro_type' => 78,
                'date' => $advance->date,
                'info' => "سلف للموظف: {$employee->name}",
                'details' => "سلف: {$advance->reason}",
                'emp_id' => $employee->id,
            ];

            $journalId = $journalService->createJournal($lines, $meta);

            // Update advance
            $advance->status = 'approved';
            $advance->journal_id = $journalId;
            $advance->approved_by = Auth::id();
            $advance->approved_at = now();
            $advance->save();

            DB::commit();
            $this->resetPage();
            session()->flash('success', 'تم الموافقة على السلف بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving employee advance: '.$e->getMessage(), [
                'advance_id' => $id,
                'exception' => $e,
            ]);
            session()->flash('error', 'حدث خطأ أثناء الموافقة: '.$e->getMessage());
        }
    }

    public function reject(int $id): void
    {
        try {
            $advance = EmployeeAdvance::findOrFail($id);

            if ($advance->isApproved()) {
                session()->flash('error', 'لا يمكن رفض سلف معتمد');

                return;
            }

            $advance->status = 'rejected';
            $advance->save();

            $this->resetPage();
            session()->flash('success', 'تم رفض السلف بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء الرفض: '.$e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        try {
            $advance = EmployeeAdvance::findOrFail($id);

            if ($advance->isApproved()) {
                session()->flash('error', 'لا يمكن حذف سلف معتمد');

                return;
            }

            $advance->delete();

            $this->resetPage();
            session()->flash('success', 'تم حذف السلف بنجاح');
        } catch (\Exception $e) {
            session()->flash('error', 'حدث خطأ أثناء الحذف: '.$e->getMessage());
        }
    }

    public function getEmployeesProperty()
    {
        return Employee::where('status', 'مفعل')
            ->orderBy('name')
            ->get();
    }

    public function getAdvancesProperty()
    {
        return EmployeeAdvance::with('employee')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.hr-management.employee-advances.index', [
            'employees' => $this->employees,
            'advances' => $this->advances,
        ]);
    }
}
