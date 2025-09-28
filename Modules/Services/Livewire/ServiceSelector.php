<?php

namespace Modules\Services\Livewire;

use Livewire\Component;
use Modules\Services\Models\Service;
use App\Models\Note;

class ServiceSelector extends Component
{
    public $searchTerm = '';
    public $selectedCategory = null;
    public $selectedService = null;
    public $services = [];
    public $categories = [];
    public $showServiceDetails = false;

    protected $listeners = ['serviceSelected' => 'selectService'];

    public function mount()
    {
        $this->loadServices();
        $this->loadCategories();
    }

    public function loadServices()
    {
        $query = Service::with(['categories', 'units', 'prices'])
            ->active();

        if ($this->searchTerm) {
            $query->where(function ($q) {
                $q->where('name', 'like', "%{$this->searchTerm}%")
                  ->orWhere('code', 'like', "%{$this->searchTerm}%")
                  ->orWhere('description', 'like', "%{$this->searchTerm}%");
            });
        }

        if ($this->selectedCategory) {
            $query->whereHas('categories', function ($q) {
                $q->where('category_id', $this->selectedCategory);
            });
        }

        $this->services = $query->orderBy('name')->get();
    }

    public function loadCategories()
    {
        $this->categories = Note::orderBy('name')->get();
    }

    public function updatedSearchTerm()
    {
        $this->loadServices();
    }

    public function updatedSelectedCategory()
    {
        $this->loadServices();
    }

    public function selectService($serviceId)
    {
        $this->selectedService = Service::with(['categories', 'units', 'prices'])->find($serviceId);
        $this->showServiceDetails = true;
        
        // Emit event to parent component
        $this->emit('serviceSelectedForPOS', $this->selectedService);
    }

    public function clearSelection()
    {
        $this->selectedService = null;
        $this->showServiceDetails = false;
        $this->emit('serviceCleared');
    }

    public function addToPOS()
    {
        if ($this->selectedService) {
            $this->emit('addServiceToPOS', $this->selectedService);
            $this->clearSelection();
        }
    }

    public function render()
    {
        return view('services::livewire.service-selector');
    }
}
