<?php

declare(strict_types=1);

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use App\Livewire\HrManagement\Employees\Concerns\HandlesEmployeeForm;

new class extends Component {
    use WithFileUploads, HandlesEmployeeForm;

    public function mount(int $employeeId): void
    {
        // Authorization check
        abort_unless(auth()->user()->can('edit Hr-Employees'), 403, __('hr.unauthorized_action'));

        $this->loadFormData();
        $this->loadEmployee($employeeId);
    }
}; ?>

@include('livewire.hr-management.employees.partials.layouts.form-layout', [
    'title' => __('hr.edit_employee'),
    'isEdit' => true
])

