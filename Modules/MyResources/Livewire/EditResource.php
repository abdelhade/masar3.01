<?php

namespace Modules\MyResources\Livewire;

use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Auth;
use Modules\Branches\Models\Branch;
use Modules\MyResources\Models\Resource;
use Modules\MyResources\Models\ResourceCategory;
use Modules\MyResources\Models\ResourceType;
use Modules\MyResources\Models\ResourceStatus;
use Modules\HR\Models\Employee;

class EditResource extends Component
{
    use WithFileUploads;

    public Resource $resource;
    
    public $name;
    public $description;
    public $resource_category_id;
    public $resource_type_id;
    public $resource_status_id;
    public $branch_id;
    public $employee_id;
    public $serial_number;
    public $model_number;
    public $manufacturer;
    public $purchase_date;
    public $purchase_cost;
    public $daily_rate;
    public $hourly_rate;
    public $current_location;
    public $warranty_expiry;
    public $notes;
    public $is_active;

    public $availableTypes = [];
    public $oldStatusId;

    public function mount(Resource $resource): void
    {
        $this->resource = $resource;
        $this->fill([
            'name' => $resource->name,
            'description' => $resource->description,
            'resource_category_id' => $resource->resource_category_id,
            'resource_type_id' => $resource->resource_type_id,
            'resource_status_id' => $resource->resource_status_id,
            'branch_id' => $resource->branch_id,
            'employee_id' => $resource->employee_id,
            'serial_number' => $resource->serial_number,
            'model_number' => $resource->model_number,
            'manufacturer' => $resource->manufacturer,
            'purchase_date' => $resource->purchase_date?->format('Y-m-d'),
            'purchase_cost' => $resource->purchase_cost,
            'daily_rate' => $resource->daily_rate,
            'hourly_rate' => $resource->hourly_rate,
            'current_location' => $resource->current_location,
            'warranty_expiry' => $resource->warranty_expiry?->format('Y-m-d'),
            'notes' => $resource->notes,
            'is_active' => $resource->is_active,
        ]);

        $this->oldStatusId = $resource->resource_status_id;
        $this->loadTypes();
    }

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
        // Load all active types regardless of category
        $this->availableTypes = ResourceType::active()->get();
    }

    public function save()
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
            'updated_by' => Auth::id(),
        ];

        // Track status change
        if ($this->resource_status_id != $this->oldStatusId) {
            $this->resource->statusHistory()->create([
                'old_status_id' => $this->oldStatusId,
                'new_status_id' => $this->resource_status_id,
                'changed_by' => Auth::id(),
                'reason' => 'تحديث المورد',
            ]);
        }

        $this->resource->update($data);

        session()->flash('success', 'تم تحديث المورد بنجاح');
        
        return $this->redirect(route('myresources.index'));
    }

    public function render()
    {
        $categories = ResourceCategory::active()->ordered()->get();
        $statuses = ResourceStatus::active()->ordered()->get();
        $branches = Branch::all();
        $employees = Employee::where('status', 'active')->get();

        return view('myresources::livewire.edit-resource', compact('categories', 'statuses', 'branches', 'employees'));
    }
}

