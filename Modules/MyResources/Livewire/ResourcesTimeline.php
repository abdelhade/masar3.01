<?php

namespace Modules\MyResources\Livewire;

use Livewire\Component;
use Carbon\Carbon;
use Modules\MyResources\Models\Resource;
use Modules\MyResources\Models\ResourceAssignment;
use Modules\MyResources\Models\ResourceCategory;

class ResourcesTimeline extends Component
{
    public $startDate;
    public $endDate;
    public $categoryFilter = '';
    public $resourceFilter = '';

    public function mount(): void
    {
        $this->startDate = now()->startOfMonth()->format('Y-m-d');
        $this->endDate = now()->endOfMonth()->format('Y-m-d');
    }

    public function render()
    {
        $categories = ResourceCategory::active()->ordered()->get();
        
        $resources = Resource::query()
            ->with(['category', 'type', 'status'])
            ->when($this->categoryFilter, function ($query) {
                $query->byCategory($this->categoryFilter);
            })
            ->when($this->resourceFilter, function ($query) {
                $query->where('id', $this->resourceFilter);
            })
            ->active()
            ->get();

        $assignments = ResourceAssignment::query()
            ->with(['resource', 'project'])
            ->whereBetween('start_date', [$this->startDate, $this->endDate])
            ->orWhereBetween('end_date', [$this->startDate, $this->endDate])
            ->orWhere(function ($query) {
                $query->where('start_date', '<=', $this->startDate)
                    ->where('end_date', '>=', $this->endDate);
            })
            ->get();

        return view('myresources::livewire.resources-timeline', compact('resources', 'assignments', 'categories'));
    }
}

