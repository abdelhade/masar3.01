<?php

namespace Modules\MyResources\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Modules\Branches\Models\Branch;
use Modules\MyResources\Models\Resource;
use Modules\MyResources\Models\ResourceCategory;
use Modules\MyResources\Models\ResourceType;
use Modules\MyResources\Models\ResourceStatus;
use App\Models\Employee;

class CreateResource extends Component
{
    use WithFileUploads;

    public $name = '';
    public $description = '';
    public $resource_category_id = '';
    public $resource_type_id = '';
    public $resource_status_id = '';
    public $branch_id = '';
    public $employee_id = '';
    public $serial_number = '';
    public $model_number = '';
    public $manufacturer = '';
    public $purchase_date = '';
    public $purchase_cost = '';
    public $daily_rate = '';
    public $hourly_rate = '';
    public $current_location = '';
    public $warranty_expiry = '';
    public $notes = '';
    public $is_active = true;

    public $availableTypes = [];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'resource_category_id' => 'required|exists:resource_categories,id',
            'resource_type_id' => 'required|exists:resource_types,id',
            'resource_status_id' => 'required|exists:resource_statuses,id',
            'branch_id' => 'nullable|exists:branches,id',
            'employee_id' => 'nullable|exists:employees,id',
            'serial_number' => 'nullable|string|max:255',
            'model_number' => 'nullable|string|max:255',
            'manufacturer' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_cost' => 'nullable|numeric|min:0',
            'daily_rate' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'current_location' => 'nullable|string|max:255',
            'warranty_expiry' => 'nullable|date',
            'notes' => 'nullable|string',
            'is_active' => 'boolean',
        ];
    }

    public function updatedResourceCategoryId(): void
    {
        $this->resource_type_id = '';
        $this->loadTypes();
    }

    public function loadTypes(): void
    {
        if ($this->resource_category_id) {
            $this->availableTypes = ResourceType::active()
                ->forCategory($this->resource_category_id)
                ->get();
        } else {
            $this->availableTypes = [];
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'description' => $this->description,
            'resource_category_id' => $this->resource_category_id,
            'resource_type_id' => $this->resource_type_id,
            'resource_status_id' => $this->resource_status_id,
            'branch_id' => $this->branch_id ?: null,
            'employee_id' => $this->employee_id ?: null,
            'serial_number' => $this->serial_number,
            'model_number' => $this->model_number,
            'manufacturer' => $this->manufacturer,
            'purchase_date' => $this->purchase_date ?: null,
            'purchase_cost' => $this->purchase_cost ?: null,
            'daily_rate' => $this->daily_rate ?: null,
            'hourly_rate' => $this->hourly_rate ?: null,
            'current_location' => $this->current_location,
            'warranty_expiry' => $this->warranty_expiry ?: null,
            'notes' => $this->notes,
            'is_active' => $this->is_active,
            'created_by' => auth()->id(),
        ];

        Resource::create($data);

        session()->flash('success', 'تم إضافة المورد بنجاح');
        
        return redirect()->route('myresources.index');
    }

    public function render()
    {
        $categories = ResourceCategory::active()->ordered()->get();
        $statuses = ResourceStatus::active()->ordered()->get();
        $branches = Branch::all();
        $employees = Employee::where('status', 'active')->get();

        return view('myresources::livewire.create-resource', compact('categories', 'statuses', 'branches', 'employees'));
    }
}

