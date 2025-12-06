<?php

declare(strict_types=1);

namespace App\Livewire\EmployeeDeductionsRewards;

use App\Models\Employee;
use App\Models\EmployeeDeductionReward;
use App\Services\EmployeeDeductionRewardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;
use Livewire\WithPagination;

class ManageEmployeeDeductionsRewards extends Component
{
    use WithPagination;

    protected $paginationTheme = 'bootstrap';

    public bool $showForm = false;

    public ?int $editingId = null;

    public ?int $selectedEmployee = null;

    public string $type = 'deduction';

    public float $amount = 0;

    public string $date = '';

    public string $reason = '';

    public ?string $notes = null;

    protected $rules = [
        'selectedEmployee' => 'required|exists:employees,id',
        'type' => 'required|in:deduction,reward',
        'amount' => 'required|numeric|min:0.01',
        'date' => 'required|date',
        'reason' => 'required|string|max:255',
        'notes' => 'nullable|string',
    ];

    protected $messages = [
        'selectedEmployee.required' => 'يرجى اختيار موظف',
        'type.required' => 'النوع مطلوب',
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
            $item = EmployeeDeductionReward::findOrFail($id);
            $this->selectedEmployee = $item->employee_id;
            $this->type = $item->type;
            $this->amount = (float) $item->amount; // Convert decimal string to float
            $this->date = $item->date->format('Y-m-d');
            $this->reason = $item->reason;
            $this->notes = $item->notes;
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
        $this->type = 'deduction';
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
                'type' => $this->type,
                'amount' => $this->amount,
                'date' => $this->date,
                'reason' => $this->reason,
                'notes' => $this->notes,
                'created_by' => Auth::id(),
            ];

            if ($this->editingId) {
                $item = EmployeeDeductionReward::findOrFail($this->editingId);

                // Can't edit if has journal
                if ($item->hasJournal()) {
                    session()->flash('error', 'لا يمكن تعديل '.($item->isDeduction() ? 'خصم' : 'مكافأة').' مرتبط بقيد محاسبي');
                    DB::rollBack();

                    return;
                }

                $data['updated_by'] = Auth::id();
                $item->update($data);
                session()->flash('success', 'تم التحديث بنجاح');
            } else {
                EmployeeDeductionReward::create($data);
                session()->flash('success', 'تم الإضافة بنجاح');
            }

            DB::commit();
            $this->closeForm();
            $this->resetPage();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving deduction/reward: '.$e->getMessage(), [
                'exception' => $e,
            ]);
            session()->flash('error', 'حدث خطأ أثناء الحفظ: '.$e->getMessage());
        }
    }

    public function approve(int $id): void
    {
        DB::beginTransaction();

        try {
            $item = EmployeeDeductionReward::findOrFail($id);

            if ($item->hasJournal()) {
                session()->flash('error', 'معتمد بالفعل');
                DB::rollBack();

                return;
            }

            $deductionService = app(EmployeeDeductionRewardService::class);

            if ($item->isDeduction()) {
                $deductionService->createDeductionJournal($item);
            } else {
                $deductionService->createRewardJournal($item);
            }

            DB::commit();
            $this->resetPage();
            session()->flash('success', 'تم الموافقة بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving deduction/reward: '.$e->getMessage(), [
                'item_id' => $id,
                'exception' => $e,
            ]);
            session()->flash('error', 'حدث خطأ أثناء الموافقة: '.$e->getMessage());
        }
    }

    public function delete(int $id): void
    {
        try {
            $item = EmployeeDeductionReward::findOrFail($id);

            if ($item->hasJournal()) {
                session()->flash('error', 'لا يمكن حذف '.($item->isDeduction() ? 'خصم' : 'مكافأة').' مرتبط بقيد محاسبي');

                return;
            }

            $item->delete();

            $this->resetPage();
            session()->flash('success', 'تم الحذف بنجاح');
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

    public function getItemsProperty()
    {
        return EmployeeDeductionReward::with('employee')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
    }

    public function render()
    {
        return view('livewire.hr-management.employee-deductions-rewards.index', [
            'employees' => $this->employees,
            'items' => $this->items,
        ]);
    }
}
