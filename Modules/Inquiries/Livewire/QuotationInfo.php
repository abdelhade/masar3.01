<?php

namespace Modules\Inquiries\Livewire;

use Livewire\Component;
use Modules\Inquiries\Models\QuotationType;
use Modules\Inquiries\Models\QuotationUnit;

class QuotationInfo extends Component
{
    public $types = [];
    public $units = [];
    public $type_id = null;
    public $type_name = '';
    public $unit_id = null;
    public $unit_name = '';
    public $selected_type_id_for_unit = null; // لاختيار النوع عند إضافة وحدة
    public $mode = 'create';

    protected $rules = [
        'type_name' => 'required|string|min:2|max:255',
        'unit_name' => 'required|string|min:2|max:255',
        'selected_type_id_for_unit' => 'required|exists:quotation_types,id',
    ];

    public function mount()
    {
        $this->loadData();
    }

    private function loadData()
    {
        $this->types = QuotationType::with('units')->latest()->get(); // حمّل الوحدات التابعة لكل نوع
        $this->units = QuotationUnit::latest()->get(); // للعرض العام إذا لزم، لكن الرئيسي مع types
    }

    public function resetTypeForm()
    {
        $this->type_id = null;
        $this->type_name = '';
        $this->mode = 'create';
        $this->resetValidation();
    }

    public function resetUnitForm()
    {
        $this->unit_id = null;
        $this->unit_name = '';
        $this->selected_type_id_for_unit = null;
        $this->mode = 'create';
        $this->resetValidation();
    }

    public function cancel()
    {
        $this->resetTypeForm();
        $this->resetUnitForm();
    }

    // CRUD for Quotation Type
    public function storeType()
    {
        $this->validate(['type_name' => 'required|string|min:2|max:255|unique:quotation_types,name']);
        QuotationType::create(['name' => $this->type_name]);
        $this->loadData();
        $this->resetTypeForm();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => __('Type Added Success')]);
    }

    public function editType($id)
    {
        $this->resetUnitForm();
        $type = QuotationType::findOrFail($id);
        $this->type_id = $id;
        $this->type_name = $type->name;
        $this->mode = 'edit';
    }

    public function updateType()
    {
        $this->validate([
            'type_name' => 'required|string|min:2|max:255|unique:quotation_types,name,' . $this->type_id,
        ]);

        $type = QuotationType::findOrFail($this->type_id);
        $type->update(['name' => $this->type_name]);
        $this->loadData();
        $this->resetTypeForm();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => __('Type Updated Success')]);
    }

    public function destroyType($id)
    {
        $type = QuotationType::findOrFail($id);
        $type->units()->delete(); // احذف الوحدات التابعة أولاً (cascade في migration)
        $type->delete();
        $this->loadData();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => __('Type And Units Deleted Success')]);
    }

    // CRUD for Quotation Unit
    public function storeUnit()
    {

        $this->validate([
            'unit_name' => 'required|string|min:2|max:255',
            'selected_type_id_for_unit' => 'required|exists:quotation_types,id',
        ]);
        try {
            QuotationUnit::create([
                'name' => $this->unit_name,
                'quotation_type_id' => $this->selected_type_id_for_unit,
            ]);

            $this->loadData();
            $this->resetUnitForm();
            $this->dispatch('swal:toast', [
                'type' => 'success',
                'message' => __('Unit Added Success'),
            ]);
        } catch (\Exception) {
            $this->dispatch('swal:toast', [
                'type' => 'error',
                'message' => __('Error Occurred While Adding Unit'),
            ]);
        }
    }


    public function editUnit($id)
    {
        $this->resetTypeForm();
        $unit = QuotationUnit::findOrFail($id);
        $this->unit_id = $id;
        $this->unit_name = $unit->name;
        $this->selected_type_id_for_unit = $unit->quotation_type_id;
        $this->mode = 'edit';
    }

    public function updateUnit()
    {
        $this->validate([
            'unit_name' => 'required|string|min:2|max:255',
            'selected_type_id_for_unit' => 'required|exists:quotation_types,id',
        ]);

        $unit = QuotationUnit::findOrFail($this->unit_id);
        $unit->update([
            'name' => $this->unit_name,
            'quotation_type_id' => $this->selected_type_id_for_unit
        ]);
        $this->loadData();
        $this->resetUnitForm();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => __('Unit Updated Success')]);
    }

    public function destroyUnit($id)
    {
        QuotationUnit::findOrFail($id)->delete();
        $this->loadData();
        $this->dispatch('swal:toast', ['type' => 'success', 'message' => __('Unit Deleted Success')]);
    }

    public function render()
    {
        return view('inquiries::livewire.quotation-info');
    }
}
