<?php

namespace Modules\MyResources\Livewire;

use Livewire\Component;
use Livewire\WithPagination;
use Modules\MyResources\Models\Resource;
use Modules\MyResources\Models\ResourceCategory;
use Modules\MyResources\Models\ResourceType;
use Modules\MyResources\Models\ResourceStatus;

class ResourcesIndex extends Component
{
    use WithPagination;

    public $search = '';
    public $categoryFilter = '';
    public $typeFilter = '';
    public $statusFilter = '';
    public $branchFilter = '';
    public $perPage = 15;

    protected $queryString = ['search', 'categoryFilter', 'typeFilter', 'statusFilter'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->typeFilter = '';
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'categoryFilter', 'typeFilter', 'statusFilter', 'branchFilter']);
        $this->resetPage();
    }

    public function deleteResource(int $resourceId): void
    {
        $resource = Resource::findOrFail($resourceId);
        
        if (auth()->user()->can('delete MyResources')) {
            $resource->delete();
            session()->flash('success', 'تم حذف المورد بنجاح');
        } else {
            session()->flash('error', 'ليس لديك صلاحية لحذف الموارد');
        }
    }

    public function render()
    {
        $resources = Resource::query()
            ->with(['category', 'type', 'status', 'branch', 'employee'])
            ->when($this->search, function ($query) {
                $query->search($this->search);
            })
            ->when($this->categoryFilter, function ($query) {
                $query->byCategory($this->categoryFilter);
            })
            ->when($this->typeFilter, function ($query) {
                $query->byType($this->typeFilter);
            })
            ->when($this->statusFilter, function ($query) {
                $query->byStatus($this->statusFilter);
            })
            ->latest()
            ->paginate($this->perPage);

        $categories = ResourceCategory::active()->ordered()->get();
        $types = $this->categoryFilter 
            ? ResourceType::active()->forCategory($this->categoryFilter)->get() 
            : collect();
        $statuses = ResourceStatus::active()->ordered()->get();

        return view('myresources::livewire.resources-index', compact('resources', 'categories', 'types', 'statuses'));
    }
}

