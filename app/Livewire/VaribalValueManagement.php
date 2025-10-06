<?php

namespace App\Livewire;

use App\Models\VaribalValue;
use App\Models\Varibal;
use Livewire\Component;
use Livewire\WithPagination;

class VaribalValueManagement extends Component
{
    use WithPagination;
    protected $paginationTheme = 'bootstrap';

    public $varibalId;
    public $search = '';
    
    // Form fields
    public $value = '';
    public $editingId = null;
    public $showForm = false;
    
    // Validation rules
    protected $rules = [
        'value' => 'required|string|max:255|unique:varibal_values,value',
    ];

    protected $messages = [
        'value.unique' => 'القيمة يجب أن تكون فريدة',
        'value.required' => 'القيمة مطلوبة',
        'value.string' => 'القيمة يجب أن تكون نص',
        'value.max' => 'القيمة لا يجب أن تتجاوز 255 حرف',
    ];

    public function mount($varibalId)
    {
        $this->varibalId = $varibalId;
    }

    public function render()
    {
        $varibal = Varibal::findOrFail($this->varibalId);
        
        $varibalValues = VaribalValue::where('varibal_id', $this->varibalId)
            ->when($this->search, function($query) {
                $query->where('value', 'like', '%' . $this->search . '%');
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('livewire.item-management.varibals.varibal-value-management', [
            'varibalValues' => $varibalValues,
            'varibal' => $varibal
        ]);
    }

    public function create()
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit($id)
    {
        $varibalValue = VaribalValue::findOrFail($id);
        $this->editingId = $id;
        $this->value = $varibalValue->value;
        $this->showForm = true;
    }

    public function save()
    {
        $this->validate();

        if ($this->editingId) {
            // Update existing
            $varibalValue = VaribalValue::findOrFail($this->editingId);
            $varibalValue->update([
                'value' => $this->value,
            ]);
            
            session()->flash('message', 'تم تحديث القيمة بنجاح');
        } else {
            // Create new
            VaribalValue::create([
                'varibal_id' => $this->varibalId,
                'value' => $this->value,
            ]);
            
            session()->flash('message', 'تم إضافة القيمة بنجاح');
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete($id)
    {
        $varibalValue = VaribalValue::findOrFail($id);
        $varibalValue->delete();
        
        session()->flash('message', 'تم حذف القيمة بنجاح');
    }

    public function cancel()
    {
        $this->resetForm();
        $this->showForm = false;
    }

    public function resetForm()
    {
        $this->value = '';
        $this->editingId = null;
        $this->resetErrorBag();
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }
}