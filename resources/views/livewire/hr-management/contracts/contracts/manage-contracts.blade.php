<?php

use App\Models\Contract;
use App\Models\ContractPoint;
use App\Models\ContractType;
use App\Models\Employee;
use App\Models\EmployeesJob;
use App\Models\SalaryPoint;
use Livewire\Volt\Component;
use Livewire\WithPagination;

new class extends Component {
    use WithPagination;

    public string $search = '';

    public bool $showModal = false;
    public bool $isEdit = false;
    public ?int $contractId = null;

    public $name, $contract_type_id, $contract_start_date, $contract_end_date, $fixed_work_hours, $additional_work_hours, $monthly_holidays, $monthly_sick_days, $information, $job_id, $job_description, $employee_id;

    public $contractTypes, $jobs, $employees;

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

    public function mount()
    {
        $this->contractTypes = ContractType::all();
        $this->jobs = EmployeesJob::all();
        $this->employees = Employee::all();
    }

    public function with(): array
    {
        $contracts = Contract::with(['employee', 'contract_type', 'contract_points', 'salary_points'])
            ->when($this->search, function ($query) {
                $query->where('name', 'like', '%' . $this->search . '%')->orWhereHas('employee', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->orderByDesc('id')
            ->paginate(15);

        return [
            'contracts' => $contracts,
        ];
    }

    public function create()
    {
        $this->resetValidation();
        $this->resetExcept('contractTypes', 'jobs', 'employees', 'search');
        $this->isEdit = false;
        $this->contractPoints = [];
        $this->salaryPoints = [];
        $this->addContractPointInput();
        $this->addSalaryPointInput();
        $this->showModal = true;
    }

    public function edit($id)
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
        $this->information = $contract->information;
        $this->job_description = $contract->job_description;

        $this->contractPoints = $contract->contract_points->map(fn($p) => ['name' => $p->name, 'information' => $p->information, 'sequence' => $p->sequence])->toArray();
        $this->salaryPoints = $contract->salary_points->map(fn($p) => ['name' => $p->name, 'information' => $p->information, 'sequence' => $p->sequence])->toArray();

        if (empty($this->contractPoints)) {
            $this->addContractPointInput();
        }
        if (empty($this->salaryPoints)) {
            $this->addSalaryPointInput();
        }
        $this->showModal = true;
    }

    public function save()
    {
        $validatedData = $this->validate();

        $contractData = collect($validatedData)
            ->except(['contractPoints', 'salaryPoints'])
            ->toArray();

        if ($this->isEdit) {
            $contract = Contract::find($this->contractId);
            $contract->update($contractData);
            session()->flash('success', __('Contract updated successfully.'));
        } else {
            $contractData['created_by'] = auth()->id();
            $contract = Contract::create($contractData);
            session()->flash('success', __('Contract created successfully.'));
        }

        $contract->contract_points()?->delete();
        foreach ($this->contractPoints as $index => $point) {
            if (!empty($point['name'])) {
                $contract->contract_points()->create([
                    'name' => $point['name'],
                    'information' => $point['information'],
                    'sequence' => $point['sequence'],
                ]);
            }
        }

        $contract->salary_points()?->delete();
        foreach ($this->salaryPoints as $index => $point) {
            if (!empty($point['name'])) {
                $contract->salary_points()->create([
                    'name' => $point['name'],
                    'information' => $point['information'],
                    'sequence' => $point['sequence'],
                ]);
            }
        }

        $this->showModal = false;
    }

    public function delete($id)
    {
        Contract::findOrFail($id)->delete();
        session()->flash('success', __('Contract deleted successfully.'));
    }

    public function view($id)
    {
        $this->viewContract = Contract::with(['contract_points', 'salary_points', 'employee', 'contract_type', 'job'])->findOrFail($id);
        $this->showViewModal = true;
    }
}; ?>

<div style="font-family: 'Cairo', sans-serif; direction: rtl;">
    <div class="d-flex justify-content-between align-items-center mb-3 mt-3">
        <button class="btn btn-primary font-family-cairo fw-bold font-14" wire:click="create">
            <i class="las la-plus font-14"></i> {{ __('Add Contract') }}
        </button>
        <input type="text" class="form-control w-25" placeholder="{{ __('Search by name or employee...') }}"
            wire:model.live.debounce.300ms="search">

        @can('إنشاء العقود')
            <button class="btn btn-primary font-family-cairo fw-bold font-14" wire:click="create">
                <i class="las la-plus font-14"></i> {{ __('Add Contract') }}
            </button>
        @endcan
        @can('البحث عن العقود')
            <input type="text" class="form-control w-25" placeholder="{{ __('Search by name or employee...') }}"
                wire:model.live.debounce.300ms="search">
        @endcan

    </div>

    @if (session()->has('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
            class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"
                x-on:click="show = false"></button>
        </div>
    @endif
    <div class="container bg-white p-3 rounded">
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table table-striped mb-0" style="min-width: 1200px;">
                <thead class="table-light text-center align-middle">

                    <tr>
                        <<<<<<< HEAD <th class="font-family-cairo text-center fw-bold font-14">#</th>
                            <th class="font-family-cairo text-center fw-bold font-14">{{ __('Contract Name') }}</th>
                            <th class="font-family-cairo text-center fw-bold font-14">{{ __('Employee') }}</th>
                            <th class="font-family-cairo text-center fw-bold font-14">{{ __('Contract Type') }}</th>
                            <th class="font-family-cairo text-center fw-bold font-14">{{ __('Start Date') }}</th>
                            <th class="font-family-cairo text-center fw-bold font-14">{{ __('End Date') }}</th>
                            <th class="font-family-cairo text-center fw-bold font-14">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($contracts as $contract)
                        <tr>
                            <td class="font-family-cairo fw-bold text-center font-14">{{ $loop->iteration }}</td>
                            <td class="font-family-cairo fw-bold text-center font-14">{{ $contract->name }}</td>
                            <td class="font-family-cairo fw-bold text-center font-14">{{ $contract->employee?->name }}
                            </td>
                            <td class="font-family-cairo fw-bold text-center font-14">
                                {{ $contract->contract_type?->name }}
                            </td>
                            <td class="font-family-cairo fw-bold text-center font-14">
                                {{ $contract->contract_start_date }}
                            </td>
                            <td class="font-family-cairo fw-bold text-center font-14">
                                {{ $contract->contract_end_date }}
                            </td>
                            <td class="font-family-cairo fw-bold font-14 text-center">
                                <button class="btn btn-md btn-info font-family-cairo fw-bold"
                                    wire:click="view({{ $contract->id }})"><i class="las la-eye font-18"></i></button>
                                <button class="btn btn-success btn-icon-square-sm font-family-cairo fw-bold"
                                    wire:click="edit({{ $contract->id }})"><i class="las la-edit font-18"></i></button>
                                <button class="btn btn-danger btn-icon-square-sm font-family-cairo fw-bold"
                                    wire:click="delete({{ $contract->id }})"
                                    wire:confirm="{{ __('Are you sure?') }}"><i
                                        class="las la-trash font-18"></i></button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center">
                                <div class="alert alert-info py-3 mb-0" style="font-size: 1.2rem; font-weight: 500;">
                                    <i class="las la-info-circle me-2"></i>
                                    لا توجد بيانات
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <td class="font-family-cairo fw-bold font-14">{{ $loop->iteration }}</td>
        <td class="font-family-cairo fw-bold font-14">{{ $contract->name }}</td>
        <td class="font-family-cairo fw-bold font-14">{{ $contract->employee?->name }}</td>
        <td class="font-family-cairo fw-bold font-14">{{ $contract->contract_type?->name }}</td>
        <td class="font-family-cairo fw-bold font-14">{{ $contract->contract_start_date }}</td>
        <td class="font-family-cairo fw-bold font-14">{{ $contract->contract_end_date }}</td>
        <td>
            <button class="btn btn-md btn-info font-family-cairo fw-bold" wire:click="view({{ $contract->id }})"><i
                    class="las la-eye font-18"></i></button>
            <button class="btn btn-md btn-warning font-family-cairo fw-bold" wire:click="edit({{ $contract->id }})"><i
                    class="las la-edit font-18"></i></button>
            <button class="btn btn-md btn-danger font-family-cairo fw-bold" wire:click="delete({{ $contract->id }})"
                wire:confirm="{{ __('Are you sure?') }}"><i class="las la-trash font-18"></i></button>
        </td>
        </tr>
    @empty
        <tr>
            <td colspan="12" class="font-family-cairo fw-bold font-18 text-center">
                {{ __('No contracts found.') }}</td>
        </tr>
        @endforelse
        </tbody>
        </table>
    </div>


    <div class="mt-4">
        {{ $contracts->links('pagination::bootstrap-5') }}
    </div>

    <!-- Create/Edit Modal -->
    <div class="modal fade @if ($showModal) show d-block @endif" tabindex="-1">
        <div class="modal-dialog modal-fullscreen">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ $isEdit ? __('Edit Contract') : __('Add Contract') }}</h5>
                    <button type="button" class="btn-close p-4" wire:click="$set('showModal', false)"></button>
                </div>
                <div class="modal-body">
                    <form wire:submit.prevent="save">
                        <div class="container-fluid" style="direction: rtl;">
                            <div class="row">
                                <!-- Basic Info Section -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0"><i class="las la-info-circle"></i>
                                            {{ __('Basic Information') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <label class="form-label">{{ __('Contract Name') }}</label>
                                                <input type="text"
                                                    class="form-control font-family-cairo fw-bold font-18"
                                                    wire:model="name" required>
                                                @error('name') <span class="text-danger">
                                                        {{ $message }}</span>
                                                @enderror

                                                @error('name') <span class="text-danger">{{ $message }}</span>
                                                @enderror
                                            </div>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <label class="form-label">{{ __('Contract Type') }}</label>
                                                <select class="form-select font-family-cairo fw-bold font-18"
                                                    wire:model="contract_type_id" required style="height: 50px;">
                                                    <option class="font-family-cairo fw-bold" value="">
                                                        {{ __('Select...') }}</option>
                                                    @foreach ($contractTypes as $type)
                                                        <option value="{{ $type->id }}">{{ $type->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('contract_type_id')
                                                    <span class="text-danger">{{ $message }}</span>
                                                @enderror

                                                @foreach ($contractTypes as $type)
                                                    <option value="{{ $type->id }}">{{ $type->name }}</option>
                                                @endforeach
                                                </select>
                                                @error('contract_type_id') <span
                                                    class="text-danger">{{ $message }}</span> @enderror
                                            </div>
                                            <div class="col-md-6 col-lg-4 mb-3">
                                                <label class="form-label">{{ __('Employee') }}</label>
                                                <select class="form-select font-family-cairo fw-bold font-18"
                                                    wire:model="employee_id" required style="height: 50px;">
                                                    <option class="font-family-cairo fw-bold font-14" value="">
                                                        {{ __('Select...') }}</option>
                                                    @foreach ($employees as $employee)
                                                        <option class="font-family-cairo fw-bold font-14"
                                                            value="{{ $employee->id }}">{{ $employee->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error('employee_id') <span class="text-danger">
                                                        {{ $message }}</span>

                                                    @error('employee_id') <span
                                                            class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <label class="form-label">{{ __('Job') }}</label>
                                                    <select class="form-select font-family-cairo fw-bold font-18"
                                                        wire:model="job_id" required style="height: 50px;">
                                                        <option class="font-family-cairo fw-bold font-14" value="">
                                                            {{ __('Select...') }}</option>
                                                        @foreach ($jobs as $job)
                                                            <option class="font-family-cairo fw-bold font-14"
                                                                value="{{ $job->id }}">{{ $job->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>

                                                    @error('job_id') <span class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <label class="form-label">{{ __('Start Date') }}</label>
                                                    <input type="date"
                                                        class="form-control font-family-cairo fw-bold font-18"
                                                        wire:model="contract_start_date" required>

                                                    @error('contract_start_date') <span
                                                        class="text-danger">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-4 mb-3">
                                                    <label class="form-label">{{ __('End Date') }}</label>
                                                    <input type="date"
                                                        class="form-control font-family-cairo fw-bold font-18"
                                                        wire:model="contract_end_date" required>

                                                    @error('contract_end_date') <span
                                                        class="text-danger">{{ $message }}</span> @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Work Hours & Details Section -->
                                    <div class="card mb-4">
                                        <div class="card-header bg-light">

                                            <h5 class="mb-0"><i class="las la-clock"></i>
                                                {{ __('Work Hours & Details') }}
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-6 col-lg-3 mb-3">
                                                    <label class="form-label">{{ __('Fixed Work Hours') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i class="las la-clock"></i></span>
                                                        <input type="number" step="0.01" class="form-control"
                                                            wire:model="fixed_work_hours">
                                                    </div>

                                                    @error('fixed_work_hours') <span
                                                        class="text-danger">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-3 mb-3">
                                                    <label class="form-label">{{ __('Additional Work Hours') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="las la-business-time"></i></span>
                                                        <input type="number" step="0.01" class="form-control"
                                                            wire:model="additional_work_hours">
                                                    </div>

                                                    @error('additional_work_hours') <span
                                                        class="text-danger">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-3 mb-3">
                                                    <label class="form-label">{{ __('Monthly Holidays') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="las la-calendar"></i></span>
                                                        <input type="number" step="0.01" class="form-control"
                                                            wire:model="monthly_holidays">
                                                    </div>

                                                    @error('monthly_holidays') <span
                                                        class="text-danger">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="col-md-6 col-lg-3 mb-3">
                                                    <label class="form-label">{{ __('Monthly Sick Days') }}</label>
                                                    <div class="input-group">

                                                        <span class="input-group-text"><i
                                                                class="las la-medkit"></i></span>
                                                        <input type="number" step="0.01" class="form-control"
                                                            wire:model="monthly_sick_days">
                                                    </div>
                                                    @error('monthly_sick_days') <span
                                                        class="text-danger">{{ $message }}</span> @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">{{ __('Information') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="las la-info-circle"></i></span>

                                                        <textarea class="form-control" wire:model="information" rows="3"></textarea>
                                                    </div>
                                                    @error('information') <span
                                                            class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                </div>
                                                <div class="col-md-6 mb-3">
                                                    <label class="form-label">{{ __('Job Description') }}</label>
                                                    <div class="input-group">
                                                        <span class="input-group-text"><i
                                                                class="las la-briefcase"></i></span>

                                                        <textarea class="form-control" wire:model="job_description" rows="3"></textarea>
                                                    </div>
                                                    @error('job_description') <span
                                                            class="text-danger">{{ $message }}</span>
                                                    @enderror
                                                    >>>>>>> origin/main
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contract Points Section -->
                                    <div class="card mb-4">

                                        <div
                                            class="card-header bg-light d-flex justify-content-between align-items-center">
                                            <h5 class="mb-0"><i class="las la-list-ul"></i> {{ __('Contract Points') }}
                                            </h5>
                                            <button type="button"
                                                class="btn btn-md btn-primary font-family-cairo fw-bold 18"
                                                wire:click="addContractPointInput">
                                                <i class="las la-plus font-18"></i> {{ __('Add Point') }}
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

                                                            <div class="table-responsive">
                                                                <table class="table table-bordered table-hover">
                                                                    <thead class="table-light">
                                                                        <tr>
                                                                            <th width="10%">{{ __('Sequence') }}</th>
                                                                            <th width="25%">{{ __('Point Name') }}</th>
                                                                            <th width="55%">{{ __('Information') }}
                                                                            </th>
                                                                            <th width="10%">{{ __('Actions') }}</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        @forelse ($contractPoints as $index => $point)
                                                                            <tr
                                                                                wire:key="contract-point-{{ $index }}">
                                                                                <td>
                                                                                    <input type="number"
                                                                                        class="form-control form-control-sm"
                                                                                        wire:model="contractPoints.{{ $index }}.sequence"
                                                                                        required>
                                                                                    @error('contractPoints.' . $index .
                                                                                        '.sequence')
                                                                                        <span
                                                                                            class="text-danger">{{ $message }}</span>
                                                                                    @enderror
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text"
                                                                                        class="form-control form-control-sm"
                                                                                        wire:model="contractPoints.{{ $index }}.name"
                                                                                        required>
                                                                                    @error('contractPoints.' . $index .
                                                                                        '.name')
                                                                                        <span
                                                                                            class="text-danger">{{ $message }}</span>
                                                                                    @enderror
                                                                                </td>
                                                                                <td>
                                                                                    <textarea class="form-control form-control-sm" wire:model="contractPoints.{{ $index }}.information"
                                                                                        rows="1"></textarea>
                                                                                    @error('contractPoints.' . $index .
                                                                                        '.information')
                                                                                        <span
                                                                                            class="text-danger">{{ $message }}</span>
                                                                                    @enderror
                                                                                </td>
                                                                                <td class="text-center">
                                                                                    <button type="button"
                                                                                        class="btn btn-danger btn-icon-square-sm"=======<input
                                                                                        type="number"
                                                                                        class="form-control form-control-sm"
                                                                                        wire:model="contractPoints.{{ $index }}.sequence"
                                                                                        required>
                                                                                        @error('contractPoints.' . $index .
                                                                                            '.sequence')
                                                                                            <span
                                                                                                class="text-danger">{{ $message }}</span>
                                                                                        @enderror
                                                                                </td>
                                                                                <td>
                                                                                    <input type="text"
                                                                                        class="form-control form-control-sm"
                                                                                        wire:model="contractPoints.{{ $index }}.name"
                                                                                        required>
                                                                                    @error('contractPoints.' . $index .
                                                                                        '.name') <span
                                                                                            class="text-danger">{{ $message }}</span>
                                                                                    @enderror
                                                                                </td>
                                                                                <td>
                                                                                    <textarea class="form-control form-control-sm" wire:model="contractPoints.{{ $index }}.information"
                                                                                        rows="1"></textarea>
                                                                                    @error('contractPoints.' . $index .
                                                                                        '.information')
                                                                                        <span
                                                                                            class="text-danger">{{ $message }}</span>
                                                                                    @enderror
                                                                                </td>
                                                                                <td class="text-center">
                                                                                    <button type="button"
                                                                                        class="btn btn-md btn-danger">>>>>>>
                                                                                        origin/main
                                                                                        wire:click="removeContractPointInput({{ $index }})">
                                                                                        <i
                                                                                            class="las la-trash font-18"></i>
                                                                                    </button>
                                                                                </td>
                                                                            </tr>
                                                                        @empty
                                                                            <tr>
                                                                                <td colspan="13" class="text-center">
                                                                                    <div class="alert alert-info py-3 mb-0"
                                                                                        style="font-size: 1.2rem; font-weight: 500;">
                                                                                        <i
                                                                                            class="las la-info-circle me-2"></i>
                                                                                        لا توجد بيانات
                                                                                    </div>
                                                                                </td>

                                                                                <td colspan="4" class="text-center">
                                                                                    {{ __('No contract points added yet.') }}
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
                                                <h5 class="mb-0"><i class="las la-money-bill"></i>
                                                    {{ __('Salary Points') }}
                                                </h5>
                                                <button type="button"
                                                    class="btn btn-md btn-primary font-family-cairo fw-bold 18"
                                                    wire:click="addSalaryPointInput">
                                                    <i class="las la-plus font-18"></i> {{ __('Add Point') }}
                                                </button>
                                            </div>
                                            <div class="card-body">
                                                <<<<<<< HEAD <div class="table-responsive" style="overflow-x: auto;">
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

                                                                <div class="table-responsive">
                                                                    <table class="table table-bordered table-hover">
                                                                        <thead class="table-light">
                                                                            <tr>
                                                                                <th width="10%">{{ __('Sequence') }}
                                                                                </th>
                                                                                <th width="25%">{{ __('Point Name') }}
                                                                                </th>
                                                                                <th width="55%">
                                                                                    {{ __('Information') }}</th>
                                                                                <th width="10%">{{ __('Actions') }}
                                                                                </th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            @forelse ($salaryPoints as $index => $point)
                                                                                <tr
                                                                                    wire:key="salary-point-{{ $index }}">
                                                                                    <td>
                                                                                        <input type="number"
                                                                                            class="form-control form-control-sm"
                                                                                            wire:model="salaryPoints.{{ $index }}.sequence"
                                                                                            required>
                                                                                        @error('salaryPoints.' . $index .
                                                                                            '.sequence')
                                                                                            <span
                                                                                                class="text-danger">{{ $message }}</span>
                                                                                        @enderror
                                                                                    </td>
                                                                                    <td>
                                                                                        <input type="text"
                                                                                            class="form-control form-control-sm"
                                                                                            wire:model="salaryPoints.{{ $index }}.name"
                                                                                            required>
                                                                                        @error('salaryPoints.' . $index .
                                                                                            '.name')
                                                                                            <span
                                                                                                class="text-danger">{{ $message }}</span>
                                                                                        @enderror
                                                                                    </td>
                                                                                    <td>
                                                                                        <textarea class="form-control form-control-sm" wire:model="salaryPoints.{{ $index }}.information"
                                                                                            rows="1"></textarea>
                                                                                        @error('salaryPoints.' . $index .
                                                                                            '.information')
                                                                                            <span
                                                                                                class="text-danger">{{ $message }}</span>
                                                                                        @enderror
                                                                                    </td>
                                                                                    <td class="text-center">
                                                                                        <button type="button"
                                                                                            class="btn btn-danger btn-icon-square-sm"=======<input
                                                                                            type="number"
                                                                                            class="form-control form-control-sm"
                                                                                            wire:model="salaryPoints.{{ $index }}.sequence"
                                                                                            required>
                                                                                            @error('salaryPoints.' . $index
                                                                                                . '.sequence')
                                                                                                <span
                                                                                                    class="text-danger">{{ $message }}</span>
                                                                                            @enderror
                                                                                    </td>
                                                                                    <td>
                                                                                        <input type="text"
                                                                                            class="form-control form-control-sm"
                                                                                            wire:model="salaryPoints.{{ $index }}.name"
                                                                                            required>
                                                                                        @error('salaryPoints.' . $index .
                                                                                            '.name') <span
                                                                                                class="text-danger">{{ $message }}</span>
                                                                                        @enderror
                                                                                    </td>
                                                                                    <td>
                                                                                        <textarea class="form-control form-control-sm" wire:model="salaryPoints.{{ $index }}.information"
                                                                                            rows="1"></textarea>
                                                                                        @error('salaryPoints.' . $index .
                                                                                            '.information')
                                                                                            <span
                                                                                                class="text-danger">{{ $message }}</span>
                                                                                        @enderror
                                                                                    </td>
                                                                                    <td class="text-center">
                                                                                        <button type="button"
                                                                                            class="btn btn-md btn-danger">>>>>>>
                                                                                            origin/main
                                                                                            wire:click="removeSalaryPointInput({{ $index }})">
                                                                                            <i
                                                                                                class="las la-trash font-18"></i>
                                                                                        </button>
                                                                                    </td>
                                                                                </tr>
                                                                            @empty
                                                                                <tr>
                                                                                    <td colspan="13"
                                                                                        class="text-center">
                                                                                        <div class="alert alert-info py-3 mb-0"
                                                                                            style="font-size: 1.2rem; font-weight: 500;">
                                                                                            <i
                                                                                                class="las la-info-circle me-2"></i>
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
                                        <i class="las la-times"></i> {{ __('Cancel') }}
                                    </button>
                                    <button type="submit" class="btn btn-primary font-family-cairo fw-bold font-14">
                                        <i class="las la-save"></i> {{ $isEdit ? __('Update') : __('Save') }}
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
                        <h5 class="modal-title">{{ __('View Contract') }}: {{ $viewContract?->name }}</h5>
                        <button type="button" class="btn-close p-4" wire:click="$set('showViewModal', false)"></button>
                    </div>
                    <div class="modal-body">
                        @if ($viewContract)
                            <div class="container-fluid px-0">
                                <!-- Basic Info Section -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0"><i class="las la-info-circle"></i>
                                            {{ __('Basic Information') }}</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-4 col-lg-3 mb-3">
                                                <div class="d-flex">

                                                    <span class="fw-bold me-2"><i
                                                            class="las la-file-contract text-primary"></i>
                                                        {{ __('Contract Name') }}:</span>
                                                    <span>{{ $viewContract->name }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-lg-3 mb-3">
                                                <div class="d-flex">
                                                    <span class="fw-bold me-2"><i class="las la-user text-primary"></i>
                                                        {{ __('Employee') }}:</span>
                                                    <span>{{ $viewContract->employee?->name }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-lg-3 mb-3">
                                                <div class="d-flex">
                                                    <span class="fw-bold me-2"><i class="las la-tag text-primary"></i>
                                                        {{ __('Contract Type') }}:</span>
                                                    <span>{{ $viewContract->contract_type?->name }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-lg-3 mb-3">
                                                <div class="d-flex">

                                                    <span class="fw-bold me-2"><i
                                                            class="las la-briefcase text-primary"></i>
                                                        {{ __('Job') }}:</span>
                                                    <span>{{ $viewContract->job?->title }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-lg-3 mb-3">
                                                <div class="d-flex">

                                                    <span class="fw-bold me-2"><i
                                                            class="las la-calendar-plus text-primary"></i>
                                                        {{ __('Start Date') }}:</span>
                                                    <span>{{ $viewContract->contract_start_date }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-4 col-lg-3 mb-3">
                                                <div class="d-flex">
                                                    <span class="fw-bold me-2"><i
                                                            class="las la-calendar-minus text-primary"></i>
                                                        {{ __('End Date') }}:</span>
                                                    <span>{{ $viewContract->contract_end_date }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Work Hours & Details Section -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0"><i class="las la-clock"></i> {{ __('Work Hours & Details') }}
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-3 col-lg-3 mb-3">
                                                <div class="d-flex">
                                                    <span class="fw-bold me-2"><i class="las la-clock text-primary"></i>
                                                        {{ __('Fixed Work Hours') }}:</span>
                                                    <span>{{ $viewContract->fixed_work_hours }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3 mb-3">
                                                <div class="d-flex">

                                                    <span class="fw-bold me-2"><i
                                                            class="las la-business-time text-primary"></i>
                                                        >>>>>>> origin/main
                                                        {{ __('Additional Work Hours') }}:</span>
                                                    <span>{{ $viewContract->additional_work_hours }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3 mb-3">
                                                <div class="d-flex">

                                                    <span class="fw-bold me-2"><i
                                                            class="las la-calendar text-primary"></i>
                                                        >>>>>>> origin/main
                                                        {{ __('Monthly Holidays') }}:</span>
                                                    <span>{{ $viewContract->monthly_holidays }}</span>
                                                </div>
                                            </div>
                                            <div class="col-md-3 col-lg-3 mb-3">
                                                <div class="d-flex">
                                                    <span class="fw-bold me-2"><i class="las la-medkit text-primary"></i>
                                                        {{ __('Monthly Sick Days') }}:</span>
                                                    <span>{{ $viewContract->monthly_sick_days }}</span>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row mt-3">
                                            <div class="col-md-6 mb-3">
                                                <div class="mb-2">
                                                    <span class="fw-bold"><i class="las la-info-circle text-primary"></i>
                                                        {{ __('Information') }}:</span>
                                                </div>
                                                <div class="border rounded p-2 bg-light">
                                                    {{ $viewContract->information ?: __('No information provided.') }}
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <div class="mb-2">
                                                    <span class="fw-bold"><i class="las la-briefcase text-primary"></i>
                                                        {{ __('Job Description') }}:</span>
                                                </div>
                                                <div class="border rounded p-2 bg-light">
                                                    {{ $viewContract->job_description ?: __('No job description provided.') }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Contract Points Section -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0"><i class="las la-list-ul"></i> {{ __('Contract Points') }}
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive" style="overflow-x: auto;">
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
                                                                {{ $point->information ?: __('No information provided.') }}
                                                            </td>

                                                            <td class="font-family-cairo fw-bold font-14">
                                                                {{ $point->name }}</td>
                                                            <td class="font-family-cairo fw-bold font-14">
                                                                {{ $point->information ?: __('No information provided.') }}
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3" class="text-center">
                                                                {{ __('No contract points found.') }}</td>
                                                        </tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>

                                <!-- Salary Points Section -->
                                <div class="card mb-4">
                                    <div class="card-header bg-light">
                                        <h5 class="mb-0"><i class="las la-money-bill"></i> {{ __('Salary Points') }}
                                        </h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive" style="overflow-x: auto;">
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
                                                                {{ $point->information ?: __('No information provided.') }}
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr>
                                                            <td colspan="3"
                                                                class="text-center font-family-cairo fw-bold font-18">
                                                            <td class="font-family-cairo fw-bold font-14">
                                                                {{ $point->name }}
                                                            </td>
                                                            <td class="font-family-cairo fw-bold font-14">
                                                                {{ $point->information ?: __('No information provided.') }}
                                                            </td>
                                                        </tr>
                                                        @empty
                                                            <tr>
                                                                <td colspan="3"
                                                                    class="text-center font-family-cairo fw-bold font-18">
                                                                    {{ __('No salary points found.') }}</td>
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
                                <i class="las la-times"></i> {{ __('Close') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
