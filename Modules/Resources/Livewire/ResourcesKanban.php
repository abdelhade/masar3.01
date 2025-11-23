<?php

namespace Modules\MyResources\Livewire;

use Livewire\Component;
use Modules\MyResources\Models\Resource;
use Modules\MyResources\Models\ResourceStatus;
use Modules\MyResources\Models\ResourceCategory;

class ResourcesKanban extends Component
{
    public $categoryFilter = '';
    public $search = '';

    public function updateResourceStatus(int $resourceId, int $newStatusId): void
    {
        if (!auth()->user()->can('change Resource Status')) {
            session()->flash('error', 'ليس لديك صلاحية لتغيير حالة الموارد');
            return;
        }

        $resource = Resource::findOrFail($resourceId);
        $oldStatusId = $resource->resource_status_id;

        $resource->update(['resource_status_id' => $newStatusId]);

        $resource->statusHistory()->create([
            'old_status_id' => $oldStatusId,
            'new_status_id' => $newStatusId,
            'changed_by' => auth()->id(),
            'reason' => 'تغيير الحالة من Kanban Board',
        ]);

        session()->flash('success', 'تم تحديث حالة المورد بنجاح');
    }

    public function render()
    {
        $statuses = ResourceStatus::active()->ordered()->get();
        $categories = ResourceCategory::active()->ordered()->get();

        $resourcesByStatus = [];
        
        foreach ($statuses as $status) {
            $resourcesByStatus[$status->id] = Resource::query()
                ->with(['category', 'type', 'branch'])
                ->where('resource_status_id', $status->id)
                ->when($this->categoryFilter, function ($query) {
                    $query->byCategory($this->categoryFilter);
                })
                ->when($this->search, function ($query) {
                    $query->search($this->search);
                })
                ->get();
        }

        return view('myresources::livewire.resources-kanban', compact('statuses', 'resourcesByStatus', 'categories'));
    }
}

