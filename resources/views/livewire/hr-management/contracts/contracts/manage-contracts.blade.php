<?php

declare(strict_types=1);

use App\Models\Contract;
use App\Models\ContractPoint;
use App\Models\ContractType;
use App\Models\Employee;
use App\Models\EmployeesJob;
use App\Models\SalaryPoint;
use Livewire\Volt\Component;
use Livewire\WithPagination;
use Livewire\Attributes\Computed;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;
    public bool $isEdit = false;
    public ?int $contractId = null;

    public string $name = '';
    public ?int $contract_type_id = null;
    public ?string $contract_start_date = null;
    public ?string $contract_end_date = null;
    public ?float $fixed_work_hours = null;
    public ?float $additional_work_hours = null;
    public ?float $monthly_holidays = null;
    public ?float $monthly_sick_days = null;
    public ?string $information = null;
    public ?int $job_id = null;
    public ?string $job_description = null;
    public ?int $employee_id = null;

    public array $contractPoints = [];
    public array $salaryPoints = [];

    public bool $showViewModal = false;
    public ?Contract $viewContract = null;

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'contract_type_id' => 'required|exists:contract_types,id',
            'employee_id' => 'required|exists:employees,id',
            'job_id' => 'required|exists:employees_jobs,id',
            'contract_start_date' => 'required|date',
            'contract_end_date' => 'required|date|after_or_equal:contract_start_date',
            'fixed_work_hours' => 'nullable|numeric|min:0',
            'additional_work_hours' => 'nullable|numeric|min:0',
            'monthly_holidays' => 'nullable|numeric|min:0',
            'monthly_sick_days' => 'nullable|numeric|min:0',
            'information' => 'nullable|string',
            'job_description' => 'nullable|string',
            'contractPoints.*.name' => 'required|string|max:255',
            'contractPoints.*.information' => 'nullable|string',
            'salaryPoints.*.name' => 'required|string|max:255',
            'salaryPoints.*.information' => 'nullable|string',
        ];
    }

    public function addContractPointInput()
    {
        $this->contractPoints[] = ['name' => '', 'information' => '', 'sequence' => count($this->contractPoints) + 1];
    }

    public function removeContractPointInput($index)
    {
        unset($this->contractPoints[$index]);
        $this->contractPoints = array_values($this->contractPoints);
    }

    public function addSalaryPointInput()
    {
        $this->salaryPoints[] = ['name' => '', 'information' => '', 'sequence' => count($this->salaryPoints) + 1];
    }

    public function removeSalaryPointInput($index)
    {
        unset($this->salaryPoints[$index]);
        $this->salaryPoints = array_values($this->salaryPoints);
    }

    /**
     * Reset pagination when search changes.
     */
    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Get contract types list for dropdown.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, ContractType>
     */
    #[Computed]
    public function contractTypes()
    {
        return ContractType::orderBy('name')->get();
    }

    /**
     * Get jobs list for dropdown.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, EmployeesJob>
     */
    #[Computed]
    public function jobs()
    {
        return EmployeesJob::orderBy('title')->get();
    }

    /**
     * Get employees list for dropdown.
     *
     * @return \Illuminate\Database\Eloquent\Collection<int, Employee>
     */
    #[Computed]
    public function employees()
    {
        return Employee::orderBy('name')->get();
    }

    /**
     * Get filtered contracts list.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function with(): array
    {
        $contracts = Contract::with(['employee', 'contract_type', 'contract_points', 'salary_points'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')
                      ->orWhereHas('employee', function ($q) {
                          $q->where('name', 'like', '%' . $this->search . '%');
                      });
            })
            ->orderByDesc('id')
            ->paginate(15);

        return [
            'contracts' => $contracts,
        ];
    }

    /**
     * Open create modal and reset form.
     */
    public function create(): void
    {
        $this->resetValidation();
        $this->resetExcept(['search']);
        $this->isEdit = false;
        $this->contractPoints = [];
        $this->salaryPoints = [];
        $this->addContractPointInput();
        $this->addSalaryPointInput();
        $this->showModal = true;
    }

    /**
     * Open edit modal and load contract data.
     *
     * @param int $id
     */
    public function edit(int $id): void
    {
        $this->resetValidation();
        $contract = Contract::with('contract_points', 'salary_points')->findOrFail($id);
        $this->contractId = $id;
        $this->isEdit = true;
        $this->name = $contract->name;
        $this->contract_type_id = $contract->contract_type_id;
        $this->employee_id = $contract->employee_id;
        $this->job_id = $contract->job_id;
        $this->contract_start_date = $contract->contract_start_date;
        $this->contract_end_date = $contract->contract_end_date;
        $this->fixed_work_hours = $contract->fixed_work_hours;
        $this->additional_work_hours = $contract->additional_work_hours;
        $this->monthly_holidays = $contract->monthly_holidays;
        $this->monthly_sick_days = $contract->monthly_sick_days;
        $this->information = $contract->information ?? '';
        $this->job_description = $contract->job_description ?? '';

        $this->contractPoints = $contract->contract_points->map(fn($p) => ['name' => $p->name, 'information' => $p->information ?? '', 'sequence' => $p->sequence])->toArray();
        $this->salaryPoints = $contract->salary_points->map(fn($p) => ['name' => $p->name, 'information' => $p->information ?? '', 'sequence' => $p->sequence])->toArray();

        if (empty($this->contractPoints)) {
            $this->addContractPointInput();
        }
        if (empty($this->salaryPoints)) {
            $this->addSalaryPointInput();
        }
        $this->showModal = true;
    }

    /**
     * Save contract (create or update).
     */
    public function save(): void
    {
        $validatedData = $this->validate();

        $contractData = collect($validatedData)
            ->except(['contractPoints', 'salaryPoints'])
            ->toArray();

        if ($this->isEdit) {
            $contract = Contract::findOrFail($this->contractId);
            $contract->update($contractData);
            session()->flash('success', __('hr.contract_updated_successfully'));
        } else {
            $contractData['created_by'] = auth()->id();
            $contract = Contract::create($contractData);
            session()->flash('success', __('hr.contract_created_successfully'));
        }

        $contract->contract_points()->delete();
        foreach ($this->contractPoints as $index => $point) {
            if (!empty($point['name'])) {
                $contract->contract_points()->create([
                    'name' => $point['name'],
                    'information' => $point['information'] ?? '',
                    'sequence' => $point['sequence'] ?? ($index + 1),
                ]);
            }
        }

        $contract->salary_points()->delete();
        foreach ($this->salaryPoints as $index => $point) {
            if (!empty($point['name'])) {
                $contract->salary_points()->create([
                    'name' => $point['name'],
                    'information' => $point['information'] ?? '',
                    'sequence' => $point['sequence'] ?? ($index + 1),
                ]);
            }
        }

        $this->showModal = false;
        $this->reset(['name', 'contract_type_id', 'contract_start_date', 'contract_end_date', 'fixed_work_hours', 'additional_work_hours', 'monthly_holidays', 'monthly_sick_days', 'information', 'job_id', 'job_description', 'employee_id', 'contractPoints', 'salaryPoints', 'contractId', 'isEdit']);
    }

    /**
     * Delete contract.
     *
     * @param int $id
     */
    public function delete(int $id): void
    {
        $contract = Contract::findOrFail($id);
        $contract->contract_points()->delete();
        $contract->salary_points()->delete();
        $contract->delete();

        session()->flash('success', __('hr.contract_deleted_successfully'));
    }

    /**
     * View contract details.
     *
     * @param int $id
     */
    public function view(int $id): void
    {
        $this->viewContract = Contract::with(['contract_points', 'salary_points', 'employee', 'contract_type', 'job'])->findOrFail($id);
        $this->showViewModal = true;
    }
}; ?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;">

    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center mb-3 mt-3">

            @can('create Contracts')
                <button class="btn btn-primary font-family-cairo fw-bold font-14" wire:click="create">
                    <i class="las la-plus font-14 me-2"></i>{{ __('hr.add_contract') }}
                </button>
            @endcan
            <input type="text" 
                   class="form-control w-25 font-family-cairo" 
                   placeholder="{{ __('hr.search_by_title') }}"
                   wire:model.live.debounce.300ms="search">
        </div>

        @if (session()->has('success'))
            <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
                class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                    x-on:click="show = false"></button>
            </div>
        @endif
        <div class="card">

            <div class="card-body">
                <div class="table-responsive" style="overflow-x: auto;">

                    <x-table-export-actions table-id="contracts-table" filename="contracts-table"
                        excel-label="تصدير Excel" pdf-label="تصدير PDF" print-label="طباعة" />

                    <table id="contracts-table" class="table table-striped mb-0" style="min-width: 1200px;">
                        <thead class="table-light text-center align-middle">
                            <tr>
                                <th class="font-family-cairo fw-bold font-14">#</th>
                                <th class="font-family-cairo fw-bold font-14">{{ __('Contract Name') }}</th>
                                <th class="font-family-cairo fw-bold font-14">{{ __('Employee') }}</th>
                                <th class="font-family-cairo fw-bold font-14">{{ __('Contract Type') }}</th>
                                <th class="font-family-cairo fw-bold font-14">{{ __('Start Date') }}</th>
                                <th class="font-family-cairo fw-bold font-14">{{ __('End Date') }}</th>

                                @canany(['edit Contracts', 'delete Contracts', 'view Contracts'])
                                    <th class="font-family-cairo fw-bold font-14">{{ __('hr.actions') }}</th>
                                @endcanany
                            </tr>
                        </thead>
                        <tbody class="table-light text-center align-middle">
                            @forelse ($contracts as $contract)
                                <tr>
                                    <td class="font-family-cairo fw-bold font-14">{{ $loop->iteration }}</td>
                                    <td class="font-family-cairo fw-bold font-14">{{ $contract->name }}</td>
                                    <td class="font-family-cairo fw-bold font-14">{{ $contract->employee?->name }}
                                    </td>
                                    <td class="font-family-cairo fw-bold font-14">
                                        {{ $contract->contract_type?->name }}
                                    </td>
                                    <td class="font-family-cairo fw-bold font-14">
                                        {{ $contract->contract_start_date }}
                                    </td>
                                    <td class="font-family-cairo fw-bold font-14">
                                        {{ $contract->contract_end_date }}
                                    </td>
                                    @canany(['edit Contracts', 'delete Contracts', 'view Contracts'])
                                        <td class="font-family-cairo fw-bold font-14">
                                            <div class="btn-group" role="group">
                                                @can('view Contracts')
                                                    <button type="button" 
                                                            class="btn btn-primary btn-icon-square-sm font-family-cairo fw-bold"
                                                            wire:click="view({{ $contract->id }})"
                                                            title="{{ __('hr.view') }}">
                                                        <i class="las la-eye font-18"></i>
                                                    </button>
                                                @endcan
                                                @can('edit Contracts')
                                                    <button type="button" 
                                                            class="btn btn-success btn-icon-square-sm font-family-cairo fw-bold"
                                                            wire:click="edit({{ $contract->id }})"
                                                            title="{{ __('hr.edit') }}">
                                                        <i class="las la-edit font-18"></i>
                                                    </button>
                                                @endcan
                                                @can('delete Contracts')
                                                    <button type="button" 
                                                            class="btn btn-danger btn-icon-square-sm font-family-cairo fw-bold"
                                                            wire:click="delete({{ $contract->id }})"
                                                            wire:confirm="{{ __('hr.confirm_delete_contract') }}"
                                                            title="{{ __('hr.delete') }}">
                                                        <i class="las la-trash font-18"></i>
                                                    </button>
                                                @endcan
                                            </div>
                                        </td>
                                    @endcanany
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->canany(['edit Contracts', 'delete Contracts', 'view Contracts']) ? '7' : '6' }}" 
                                        class="text-center font-family-cairo fw-bold py-4">
                                        <div class="alert alert-info mb-0">
                                            <i class="las la-info-circle me-2"></i>
                                            {{ __('hr.no_contracts_found') }}
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="mt-4">
            {{ $contracts->links('pagination::bootstrap-5') }}
        </div>

        <!-- Create/Edit Modal -->
        <div class="modal fade @if ($showModal) show d-block @endif" tabindex="-1">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo fw-bold">{{ $isEdit ? __('hr.edit_contract') : __('hr.add_contract') }}</h5>
                        <button type="button" class="btn-close p-4" wire:click="$set('showModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        <form wire:submit.prevent="save">
                            <div class="container-fluid" style="direction: rtl;">
                                <div class="row">
                                    <!-- Basic Info Section -->
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0 font-family-cairo fw-bold"><i class="las la-info-circle"></i>
                                                {{ __('hr.basic_information') }}</h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <label class="form-label">{{ __('Contract Name') }}</label>
                                                    <input type="text"
                                                        class="form-control font-family-cairo fw-bold font-18"
                                                        wire:model.blur="name" required>
                                                    @error('name')
                                                        <span class="text-danger">
                                                            {{ $message }}</span>
                                                    @enderror

                                                    @error('name')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <label class="form-label">{{ __('Contract Type') }}</label>
                                                    <select class="form-select font-family-cairo fw-bold font-18"
                                                        wire:model.blur="contract_type_id" required style="height: 50px;">
                                                        <option class="font-family-cairo fw-bold" value="">
                                                            {{ __('hr.select_option') }}</option>
                                                        @foreach ($this->contractTypes as $type)
                                                            <option value="{{ $type->id }}">{{ $type->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('contract_type_id')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <label class="form-label">{{ __('Employee') }}</label>
                                                    <select class="form-select font-family-cairo fw-bold font-18"
                                                        wire:model.blur="employee_id" required style="height: 50px;">
                                                        <option class="font-family-cairo fw-bold font-14"
                                                            value="">
                                                            {{ __('hr.select_option') }}</option>
                                                        @foreach ($this->employees as $employee)
                                                            <option class="font-family-cairo fw-bold font-14"
                                                                value="{{ $employee->id }}">{{ $employee->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('employee_id')
                                                        <span class="text-danger">
                                                            {{ $message }}</span>
                                                    @enderror
                                                    @error('employee_id')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <label class="form-label">{{ __('Job') }}</label>
                                                    <select class="form-select font-family-cairo fw-bold font-18"
                                                        wire:model.blur="job_id" required style="height: 50px;">
                                                        <option class="font-family-cairo fw-bold font-14"
                                                            value="">
                                                            {{ __('hr.select_option') }}</option>
                                                        @foreach ($this->jobs as $job)
                                                            <option class="font-family-cairo fw-bold font-14"
                                                                value="{{ $job->id }}">{{ $job->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @error('job_id')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <label class="form-label">{{ __('Start Date') }}</label>
                                                    <input type="date"
                                                        class="form-control font-family-cairo fw-bold font-18"
                                                        wire:model.blur="contract_start_date" required>

                                                    @error('contract_start_date')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <label class="form-label">{{ __('End Date') }}</label>
                                                    <input type="date"
                                                        class="form-control font-family-cairo fw-bold font-18"
                                                        wire:model.blur="contract_end_date" required>

                                                    @error('contract_end_date')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Work Hours & Details Section -->
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">
                                            <h5 class="mb-0 font-family-cairo fw-bold"><i class="las la-clock"></i>
                                                {{ __('hr.work_hours_details') }}
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 col-lg-3 mb-3">
                                                    <label class="form-label font-family-cairo fw-bold">{{ __('hr.fixed_work_hours') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="las la-clock"></i></span>
                                                        <input type="number" step="0.01" class="form-control"
                                                            wire:model.blur="fixed_work_hours">
                                                    </div>

                                                    @error('fixed_work_hours')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-3 mb-3">
                                                    <label
                                                        class="form-label font-family-cairo fw-bold">{{ __('hr.additional_work_hours') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="las la-business-time"></i></span>
                                                        <input type="number" step="0.01" class="form-control"
                                                            wire:model.blur="additional_work_hours">
                                                    </div>

                                                    @error('additional_work_hours')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-3 mb-3">
                                                    <label class="form-label font-family-cairo fw-bold">{{ __('hr.monthly_holidays') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="las la-calendar"></i></span>
                                                        <input type="number" step="0.01" class="form-control"
                                                            wire:model.blur="monthly_holidays">
                                                    </div>

                                                    @error('monthly_holidays')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-3 mb-3">
                                                    <label class="form-label font-family-cairo fw-bold">{{ __('hr.monthly_sick_days') }}</label>
                                                    <div class="input-group">

                                                        <span class="input-group-text"><i
                                                                class="las la-medkit"></i></span>
                                                        <input type="number" step="0.01" class="form-control"
                                                            wire:model.blur="monthly_sick_days">
                                                    </div>
                                                    @error('monthly_sick_days')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label font-family-cairo fw-bold">{{ __('hr.description') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="las la-info-circle"></i></span>

                                                        <textarea class="form-control" wire:model.blur="information" rows="3"></textarea>
                                                    </div>
                                                    @error('information')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label font-family-cairo fw-bold">{{ __('hr.job_description') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="las la-briefcase"></i></span>

                                                        <textarea class="form-control" wire:model.blur="job_description" rows="3"></textarea>
                                                    </div>
                                                    @error('job_description')
                                                        <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contract Points Section -->
                                    <div class="card mb-4">
                                        <div
                                            class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0 font-family-cairo fw-bold"><i class="las la-list-ul"></i>
                                                {{ __('hr.contract_points') }}
                                            </h5>
                                            <button type="button"
                                                class="btn btn-md btn-primary font-family-cairo fw-bold"
                                                wire:click="addContractPointInput">
                                                <i class="las la-plus font-18 me-1"></i> {{ __('hr.add_point') }}
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive" style="overflow-x: auto;">
                                                <table class="table table-striped mb-0" style="min-width: 1200px;">
                                                    <thead class="table-light text-center align-middle">

                                                        <tr>
                                                            <th class="font-family-cairo fw-bold font-14 text-center"
                                                                width="10%">
                                                                {{ __('Sequence') }}</th>
                                                            <th class="font-family-cairo fw-bold font-14 text-center"
                                                                width="25%">
                                                                {{ __('Point Name') }}</th>
                                                            <th class="font-family-cairo fw-bold font-14 text-center"
                                                                width="55%">
                                                                {{ __('Information') }}</th>
                                                            <th class="font-family-cairo fw-bold font-14 text-center"
                                                                width="10%">
                                                                {{ __('Actions') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($contractPoints as $index => $point)
                                                            <tr wire:key="contract-point-{{ $index }}">
                                                                <td>
                                                                    <input type="number"
                                                                        class="form-control form-control-sm"
                                                                        wire:model.blur="contractPoints.{{ $index }}.sequence"
                                                                        required>
                                                                    @error('contractPoints.' . $index . '.sequence')
                                                                        <span
                                                                            class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm"
                                                                        wire:model.blur="contractPoints.{{ $index }}.name"
                                                                        required>
                                                                    @error('contractPoints.' . $index . '.name')
                                                                        <span
                                                                            class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <textarea class="form-control form-control-sm" wire:model.blur="contractPoints.{{ $index }}.information"
                                                                        rows="1"></textarea>
                                                                    @error('contractPoints.' . $index . '.information')
                                                                        <span
                                                                            class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button"
                                                                        class="btn btn-md btn-danger"
                                                                        wire:click="removeContractPointInput({{ $index }})">
                                                                        <i class="las la-trash font-18"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="13" class="text-center">
                                                                    <div class="alert alert-info py-3 mb-0"
                                                                        style="font-size: 1.2rem; font-weight: 500;">
                                                                        <i class="las la-info-circle me-2"></i>
                                                                        لا توجد بيانات
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Salary Points Section -->
                                    <div class="card mb-4">
                                        <div
                                            class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0 font-family-cairo fw-bold"><i class="las la-money-bill"></i>
                                                {{ __('hr.salary_points') }}
                                            </h5>
                                            <button type="button"
                                                class="btn btn-md btn-primary font-family-cairo fw-bold"
                                                wire:click="addSalaryPointInput">
                                                <i class="las la-plus font-18 me-1"></i> {{ __('hr.add_point') }}
                                            </button>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive" style="overflow-x: auto;">
                                                <table class="table table-striped mb-0" style="min-width: 1200px;">
                                                    <thead class="table-light text-center align-middle">
                                                        <tr>
                                                            <th class="font-family-cairo fw-bold font-14 text-center"
                                                                width="10%">
                                                                {{ __('Sequence') }}</th>
                                                            <th class="font-family-cairo fw-bold font-14 text-center"
                                                                width="25%">
                                                                {{ __('Point Name') }}</th>
                                                            <th class="font-family-cairo fw-bold font-14 text-center"
                                                                width="55%">
                                                                {{ __('Information') }}</th>
                                                            <th class="font-family-cairo fw-bold font-14 text-center"
                                                                width="10%">
                                                                {{ __('Actions') }}</th>

                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @forelse ($salaryPoints as $index => $point)
                                                            <tr wire:key="salary-point-{{ $index }}">
                                                                <td>
                                                                    <input type="number"
                                                                        class="form-control form-control-sm"
                                                                        wire:model.blur="salaryPoints.{{ $index }}.sequence"
                                                                        required>
                                                                    @error('salaryPoints.' . $index . '.sequence')
                                                                        <span
                                                                            class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <input type="text"
                                                                        class="form-control form-control-sm"
                                                                        wire:model.blur="salaryPoints.{{ $index }}.name"
                                                                        required>
                                                                    @error('salaryPoints.' . $index . '.name')
                                                                        <span
                                                                            class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </td>
                                                                <td>
                                                                    <textarea class="form-control form-control-sm" wire:model.blur="salaryPoints.{{ $index }}.information"
                                                                        rows="1"></textarea>
                                                                    @error('salaryPoints.' . $index . '.information')
                                                                        <span
                                                                            class="text-danger">{{ $message }}</span>
                                                                    @enderror
                                                                </td>
                                                                <td class="text-center">
                                                                    <button type="button"
                                                                        class="btn btn-md btn-danger"
                                                                        wire:click="removeSalaryPointInput({{ $index }})">
                                                                        <i class="las la-trash font-18"></i>
                                                                    </button>
                                                                </td>
                                                            </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="13" class="text-center">
                                                                    <div class="alert alert-info py-3 mb-0"
                                                                        style="font-size: 1.2rem; font-weight: 500;">
                                                                        <i class="las la-info-circle me-2"></i>
                                                                        لا توجد بيانات
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforelse
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer d-flex justify-content-center">
                                <button type="button" class="btn btn-secondary font-family-cairo fw-bold font-14"
                                    wire:click="$set('showModal', false)">
                                    <i class="las la-times me-1"></i> {{ __('hr.cancel') }}
                                </button>
                                <button type="submit" class="btn btn-primary font-family-cairo fw-bold font-14">
                                    <i class="las la-save me-1"></i> {{ $isEdit ? __('hr.update') : __('hr.save') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- View Modal -->
        <div class="modal fade @if ($showViewModal) show d-block @endif" tabindex="-1"
            style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-fullscreen">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title font-family-cairo fw-bold">{{ __('hr.view_contract') }}: {{ $viewContract?->name }}</h5>
                        <button type="button" class="btn-close p-4"
                            wire:click="$set('showViewModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        @if ($viewContract)
                            <div class="container-fluid px-0">
                                {{-- Basic Info --}}
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0 font-family-cairo fw-bold"><i class="las la-info-circle"></i>
                                            {{ __('hr.basic_information') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            {{-- repeat info blocks --}}
                                            {{-- <x-contract.view-info icon="file-contract" label="Contract Name"
                                                    :value="$viewContract->name" />
                                                <x-contract.view-info icon="user" label="Employee" :value="$viewContract->employee?->name" />
                                                <x-contract.view-info icon="tag" label="Contract Type"
                                                    :value="$viewContract->contract_type?->name" />
                                                <x-contract.view-info icon="briefcase" label="Job" :value="$viewContract->job?->title" />
                                                <x-contract.view-info icon="calendar-plus" label="Start Date"
                                                    :value="$viewContract->contract_start_date" />
                                                <x-contract.view-info icon="calendar-minus" label="End Date"
                                                    :value="$viewContract->contract_end_date" /> --}}
                                        </div>
                                    </div>
                                </div>

                                {{-- Work Hours --}}
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0 font-family-cairo fw-bold"><i class="las la-clock"></i>
                                            {{ __('hr.work_hours_details') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        {{-- <div class="row">
                                                <x-contract.view-info icon="clock" label="Fixed Work Hours"
                                                    :value="$viewContract->fixed_work_hours" />
                                                <x-contract.view-info icon="business-time" label="Additional Work Hours"
                                                    :value="$viewContract->additional_work_hours" />
                                                <x-contract.view-info icon="calendar" label="Monthly Holidays"
                                                    :value="$viewContract->monthly_holidays" />
                                                <x-contract.view-info icon="medkit" label="Monthly Sick Days"
                                                    :value="$viewContract->monthly_sick_days" />
                                            </div> --}}

                                        <div class="row mt-3">
                                            <div class="col-md-6 mb-3">
                                                <div class="mb-2">
                                                    <span class="fw-bold"><i
                                                            class="las la-info-circle text-primary"></i>
                                                        {{ __('Information') }}:</span>
                                                </div>
                                                <div class="border rounded p-2 bg-light">
                                                    {{ $viewContract->information ?: __('hr.no_information_provided') }}
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="mb-2">
                                                    <span class="fw-bold font-family-cairo"><i
                                                            class="las la-briefcase text-primary"></i>
                                                        {{ __('hr.job_description') }}:</span>
                                                </div>
                                                <div class="border rounded p-2 bg-light font-family-cairo">
                                                    {{ $viewContract->job_description ?: __('hr.no_job_description_provided') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Contract Points --}}
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0 font-family-cairo fw-bold"><i class="las la-list-ul"></i>
                                            {{ __('hr.contract_points') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0" style="min-width: 1200px;">
                                                <thead class="table-light text-center align-middle">
                                                    <tr>
                                                        <th width="10%" class="font-family-cairo fw-bold font-14">
                                                            {{ __('Sequence') }}</th>
                                                        <th width="30%" class="font-family-cairo fw-bold font-14">
                                                            {{ __('Point Name') }}</th>
                                                        <th width="60%" class="font-family-cairo fw-bold font-14">
                                                            {{ __('Information') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($viewContract->contract_points as $point)
                                                        <tr>
                                                            <td class="text-center font-family-cairo fw-bold font-14">
                                                                {{ $point->sequence }}</td>
                                                            <td class="font-family-cairo fw-bold font-14">
                                                                {{ $point->name }}</td>
                                                            <td class="font-family-cairo fw-bold font-14">
                                                                {{ $point->information ?: __('hr.no_information_provided') }}
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center font-family-cairo fw-bold py-4">
                                                                <div class="alert alert-info mb-0">
                                                                    <i class="las la-info-circle me-2"></i>
                                                                    {{ __('hr.no_contract_points_found') }}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                {{-- Salary Points --}}
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0 font-family-cairo fw-bold"><i class="las la-money-bill"></i>
                                            {{ __('hr.salary_points') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-striped mb-0" style="min-width: 1200px;">
                                                <thead class="table-light text-center align-middle">
                                                    <tr>
                                                        <th width="10%" class="font-family-cairo fw-bold font-14">
                                                            {{ __('Sequence') }}</th>
                                                        <th width="30%" class="font-family-cairo fw-bold font-14">
                                                            {{ __('Point Name') }}</th>
                                                        <th width="60%" class="font-family-cairo fw-bold font-14">
                                                            {{ __('Information') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse ($viewContract->salary_points as $point)
                                                        <tr>
                                                            <td class="text-center font-family-cairo fw-bold font-14">
                                                                {{ $point->sequence }}</td>
                                                            <td class="font-family-cairo fw-bold font-14">
                                                                {{ $point->name }}</td>
                                                            <td class="font-family-cairo fw-bold font-14">
                                                                {{ $point->information ?: __('hr.no_information_provided') }}
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center font-family-cairo fw-bold py-4">
                                                                <div class="alert alert-info mb-0">
                                                                    <i class="las la-info-circle me-2"></i>
                                                                    {{ __('hr.no_salary_points_found') }}
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div class="modal-footer d-flex justify-content-center">
                        <button type="button" class="btn btn-secondary font-family-cairo fw-bold font-14"
                            wire:click="$set('showViewModal', false)">
                            <i class="las la-times me-1"></i> {{ __('hr.close') }}
                        </button>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
