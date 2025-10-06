<?php

namespace Modules\App\Livewire;

use Livewire\Component;
use Illuminate\Database\Eloquent\Model;

class SearchableSelect extends Component
{
    public $search = '';
    public $selectedId = null;
    public $selectedText = '';
    public $showDropdown = false;
    public $items = [];
    public $filteredItems = [];

    // الخصائص المطلوبة
    public $model; // اسم الموديل مثل: App\Models\Client
    public $labelField; // اسم الحقل المعروض مثل: cname
    public $valueField = 'id'; // اسم حقل القيمة
    public $placeholder = 'ابحث أو أضف جديد...';
    public $label; // العنوان مثل: العميل
    public $wireModel; // اسم المتغير في الكومبوننت الأب
    public $additionalData = []; // بيانات إضافية عند الإنشاء
    public $where = []; // شروط إضافية للفلترة مثل: ['type' => 'client']

    protected $listeners = ['refreshItems'];

    public function mount()
    {
        $this->loadItems();

        if ($this->selectedId) {
            $this->loadSelectedItem();
            $this->search = $this->selectedText; // عرض الاسم المختار
        }
    }

    public function loadItems()
    {
        $query = app($this->model)::query();

        // تطبيق الشروط الإضافية
        foreach ($this->where as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } else {
                $query->where($field, $value);
            }
        }

        $this->items = $query->get()->map(function ($item) {
            return [
                'id' => $item->{$this->valueField},
                'text' => $item->{$this->labelField}
            ];
        })->toArray();

        $this->filteredItems = $this->items;
    }

    public function loadSelectedItem()
    {
        $item = app($this->model)::find($this->selectedId);
        if ($item) {
            $this->selectedText = $item->{$this->labelField};
        }
    }

    public function updatedSearch($value)
    {
        $this->showDropdown = !empty($value);

        if (empty($value)) {
            $this->filteredItems = $this->items;
            return;
        }

        $this->filteredItems = array_filter($this->items, function ($item) use ($value) {
            return stripos($item['text'], $value) !== false;
        });
    }

    public function selectItem($id, $text)
    {
        $this->selectedId = $id;
        $this->selectedText = $text;
        $this->search = $text; // عرض الاسم المختار في حقل البحث
        $this->showDropdown = false;

        // إرسال القيمة للكومبوننت الأب
        $this->dispatch('itemSelected', [
            'wireModel' => $this->wireModel,
            'value' => $id
        ]);
    }

    public function updatedSelectedId($value)
    {
        if ($value) {
            $this->loadSelectedItem();
            $this->search = $this->selectedText; // عرض الاسم في حقل البحث
        } else {
            $this->search = '';
            $this->selectedText = '';
        }
    }

    public function refreshItems()
    {
        $this->loadItems();

        // إذا كان هناك عنصر محدد، تحديث النص المعروض
        if ($this->selectedId) {
            $this->loadSelectedItem();
            $this->search = $this->selectedText;
        }
    }

    public function createNew()
    {
        // التحقق من عدم وجود العنصر
        $exists = collect($this->items)->contains('text', $this->search);

        if ($exists) {
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'هذا العنصر موجود بالفعل'
            ]);
            return;
        }

        try {
            // دمج البيانات الأساسية مع الإضافية
            $data = array_merge(
                [$this->labelField => $this->search],
                $this->additionalData
            );

            // إنشاء العنصر الجديد
            $newItem = app($this->model)::create($data);

            // تحديث القائمة
            $this->loadItems();

            // اختيار العنصر الجديد
            $this->selectItem($newItem->{$this->valueField}, $newItem->{$this->labelField});

            $this->dispatch('notify', [
                'type' => 'success',
                'message' => 'تم إنشاء العنصر بنجاح'
            ]);
        } catch (\Exception $e) {
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'حدث خطأ أثناء الإنشاء: ' . $e->getMessage()
            ]);
        }
    }

    public function clearSelection()
    {
        $this->selectedId = null;
        $this->selectedText = '';
        $this->search = '';
        $this->showDropdown = false;

        $this->dispatch('itemSelected', [
            'wireModel' => $this->wireModel,
            'value' => null
        ]);
    }

    public function render()
    {
        return view('app::livewire.searchable-select');
    }
}
