<?php

declare(strict_types=1);

namespace App\Livewire\FlexibleSalaryProcessing;

use App\Models\FlexibleSalaryProcessing;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class Edit extends Component
{
    public FlexibleSalaryProcessing $processing;

    public float $editingHoursWorked = 0;

    public string $editingNotes = '';

    protected $rules = [
        'editingHoursWorked' => 'required|numeric|min:0.01',
    ];

    protected $messages = [
        'editingHoursWorked.required' => 'عدد الساعات مطلوب',
        'editingHoursWorked.numeric' => 'عدد الساعات يجب أن يكون رقماً',
        'editingHoursWorked.min' => 'عدد الساعات يجب أن يكون أكبر من صفر',
    ];

    public function mount(FlexibleSalaryProcessing $processing): void
    {
        if ($processing->status !== 'pending') {
            session()->flash('error', 'يمكن تعديل المعالجة فقط إذا كانت قيد المراجعة');
            $this->redirect(route('flexible-salary.processing.index'), navigate: true);
            return;
        }

        $this->processing = $processing;
        $this->editingHoursWorked = (float) $processing->hours_worked;
        $this->editingNotes = $processing->notes ?? '';
    }

    public function update(): void
    {
        $this->validate();

        DB::beginTransaction();

        try {
            $this->processing->refresh();

            if ($this->processing->status !== 'pending') {
                throw new \Exception('يمكن تعديل المعالجة فقط إذا كانت قيد المراجعة');
            }

            $employee = $this->processing->employee;
            $fixedSalary = $employee->salary;
            $hourlyWage = $employee->flexible_hourly_wage ?? 0;
            $flexibleSalary = $this->editingHoursWorked * $hourlyWage;
            $totalSalary = $fixedSalary + $flexibleSalary;

            $this->processing->update([
                'hours_worked' => $this->editingHoursWorked,
                'total_salary' => $totalSalary,
                'notes' => $this->editingNotes,
                'updated_by' => Auth::id(),
            ]);

            DB::commit();
            session()->flash('success', 'تم تحديث المعالجة بنجاح');
            $this->redirect(route('flexible-salary.processing.index'), navigate: true);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating flexible salary processing: '.$e->getMessage(), [
                'processing_id' => $this->processing->id,
                'exception' => $e,
            ]);
            session()->flash('error', 'حدث خطأ أثناء التحديث: '.$e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.flexible-salary-processing.edit', [
            'processing' => $this->processing,
        ]);
    }
}

