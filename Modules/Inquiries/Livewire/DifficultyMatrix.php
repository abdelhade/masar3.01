<?php

namespace Modules\Inquiries\Livewire;

use Livewire\Component;
use RealRashid\SweetAlert\Facades\Alert;
use Modules\Inquiries\Models\WorkCondition;
use Modules\Inquiries\Models\SubmittalChecklist;

class DifficultyMatrix extends Component
{
    public $submittals = [];
    public $conditions = [];
    public $submittal_id = null;
    public $submittal_name = '';
    public $submittal_score = 0;
    public $condition_id = null;
    public $condition_name = '';
    public $condition_score = 0;
    public $options = [['name' => '', 'score' => 0]];
    public $mode = 'create'; // create or edit

    // لا داعي لتغيير قواعد التحقق
    protected $rules = [
        'submittal_name' => 'required|string|min:3|max:255',
        'submittal_score' => 'required|integer|min:0',
        'condition_name' => 'required|string|min:3|max:255',
        'condition_score' => 'required|integer|min:0',
        'options.*.name' => 'nullable|string|min:3|max:255',
        'options.*.score' => 'nullable|integer|min:0',
    ];

    public function mount()
    {
        $this->loadData();
    }

    // دالة مساعدة لتحديث البيانات
    private function loadData()
    {
        $this->submittals = SubmittalChecklist::latest()->get();
        $this->conditions = WorkCondition::latest()->get();
    }

    public function resetSubmittalForm()
    {
        $this->submittal_id = null;
        $this->submittal_name = '';
        $this->submittal_score = 0;
        $this->mode = 'create';
        $this->resetValidation(); // لإزالة رسائل الخطأ القديمة
    }

    public function resetConditionForm()
    {
        $this->condition_id = null;
        $this->condition_name = '';
        $this->condition_score = 0;
        $this->options = [['name' => '', 'score' => 0]];
        $this->mode = 'create';
        $this->resetValidation(); // لإزالة رسائل الخطأ القديمة
    }

    // دالة جديدة لإلغاء أي عملية تعديل حالية
    public function cancel()
    {
        $this->resetSubmittalForm();
        $this->resetConditionForm();
    }


    public function addOption()
    {
        $this->options[] = ['name' => '', 'score' => 0];
    }

    public function removeOption($index)
    {
        unset($this->options[$index]);
        $this->options = array_values($this->options);
    }

    // --- Submittal Checklist CRUD ---

    public function storeSubmittal()
    {
        $this->validate([
            'submittal_name' => 'required|string|min:3|max:255|unique:submittal_checklists,name',
            'submittal_score' => 'required|integer|min:0',
        ]);

        SubmittalChecklist::create([
            'name' => $this->submittal_name,
            'score' => $this->submittal_score,
        ]);

        $this->loadData();
        $this->resetSubmittalForm();
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'تم إضافة التقديم بنجاح',
        ]);
    }

    public function editSubmittal($id)
    {
        // قم بإلغاء أي تعديل في الفورم الآخر أولاً
        $this->resetConditionForm();

        $submittal = SubmittalChecklist::findOrFail($id);
        $this->submittal_id = $id;
        $this->submittal_name = $submittal->name;
        $this->submittal_score = $submittal->score;
        $this->mode = 'edit';
    }

    public function updateSubmittal()
    {
        $this->validate([
            'submittal_name' => 'required|string|min:3|max:255|unique:submittal_checklists,name,' . $this->submittal_id,
            'submittal_score' => 'required|integer|min:0',
        ]);

        $submittal = SubmittalChecklist::findOrFail($this->submittal_id);
        $submittal->update([
            'name' => $this->submittal_name,
            'score' => $this->submittal_score,
        ]);

        $this->loadData();
        $this->resetSubmittalForm();
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'تم تعديل التقديم بنجاح',
        ]);
    }

    public function destroySubmittal($id)
    {
        SubmittalChecklist::findOrFail($id)->delete();
        $this->loadData();
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'تم حذف التقديم بنجاح',
        ]);
    }

    // --- Work Condition CRUD ---

    public function storeCondition()
    {
        $this->validate([
            'condition_name' => 'required|string|min:3|max:255|unique:work_conditions,name',
            'condition_score' => 'required|integer|min:0',
            'options.*.name' => 'nullable|string|min:3|max:255',
            'options.*.score' => 'nullable|integer|min:0',
        ]);

        $options = array_filter($this->options, fn($option) => !empty($option['name']));
        $options = $options ? array_combine(
            array_column($options, 'name'),
            array_column($options, 'score')
        ) : null;

        WorkCondition::create([
            'name' => $this->condition_name,
            'score' => $this->condition_score,
            'options' => $options, // الموديل يجب أن يحتوي على كاست للـ JSON
        ]);

        $this->loadData();
        $this->resetConditionForm();
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'تم إضافة شرط العمل بنجاح',
        ]);
    }

    public function editCondition($id)
    {
        // قم بإلغاء أي تعديل في الفورم الآخر أولاً
        $this->resetSubmittalForm();

        $condition = WorkCondition::findOrFail($id);
        $this->condition_id = $id;
        $this->condition_name = $condition->name;
        $this->condition_score = $condition->score;

        // تأكد من أن الموديل يقوم بتحويل options إلى array
        $optionsArray = $condition->options ?? [];
        $this->options = !empty($optionsArray) ? array_map(fn($name, $score) => ['name' => $name, 'score' => $score], array_keys($optionsArray), $optionsArray) : [['name' => '', 'score' => 0]];

        $this->mode = 'edit';
    }

    public function updateCondition()
    {
        $this->validate([
            'condition_name' => 'required|string|min:3|max:255|unique:work_conditions,name,' . $this->condition_id,
            'condition_score' => 'required|integer|min:0',
            'options.*.name' => 'nullable|string|min:3|max:255',
            'options.*.score' => 'nullable|integer|min:0',
        ]);

        $options = array_filter($this->options, fn($option) => !empty($option['name']));
        $options = $options ? array_combine(
            array_column($options, 'name'),
            array_column($options, 'score')
        ) : null;

        $condition = WorkCondition::findOrFail($this->condition_id);
        $condition->update([
            'name' => $this->condition_name,
            'score' => $this->condition_score,
            'options' => $options,
        ]);

        $this->loadData();
        $this->resetConditionForm();
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'تم تعديل شرط العمل بنجاح',
        ]);
    }

    public function destroyCondition($id)
    {
        WorkCondition::findOrFail($id)->delete();
        $this->loadData();
        $this->dispatch('swal:toast', [
            'type' => 'success',
            'message' => 'تم حذف شرط العمل بنجاح',
        ]);
    }

    public function render()
    {
        return view('inquiries::livewire.difficulty-matrix');
    }
}
