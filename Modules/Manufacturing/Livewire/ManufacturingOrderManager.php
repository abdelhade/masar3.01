<?php

namespace Modules\Manufacturing\Livewire;

use App\Models\Item;
use Livewire\Component;
use Livewire\WithPagination;
use Illuminate\Support\Facades\DB;
use Modules\Branches\Models\Branch;
use Modules\Manufacturing\Models\{ManufacturingOrder, ManufacturingStage};
use Modules\Manufacturing\Enums\ManufacturingStageStatus;

class ManufacturingOrderManager extends Component
{
    use WithPagination;

    public $order_id;
    public $order_number;
    public $branch_id;
    public $item_id;
    public $description;
    public $status = 'draft';
    public $is_template = false;
    public $template_name;
    public $selected_stages = [];
    public $templates = [];
    public $view_mode = 'form';
    public $viewing_order_id;

    protected $paginationTheme = 'bootstrap';

    protected $listeners = ['itemSelected'];

    public function rules()
    {
        return [
            'order_number' => 'required|string',
            'branch_id' => 'required|exists:branches,id',
            'item_id' => 'required|exists:items,id',
            'description' => 'nullable|string|max:1000',
            'status' => 'required|in:draft,in_progress,completed,cancelled',
            'template_name' => 'nullable|string|required_if:is_template,true|max:255',
            'selected_stages' => 'required|array|min:1',
            'selected_stages.*.id' => 'required|exists:manufacturing_stages,id',
            'selected_stages.*.quantity' => 'required|integer|min:0',
            'selected_stages.*.estimated_duration' => 'required|numeric|min:0',
            'selected_stages.*.status' => 'required|in:' . implode(',', array_column(ManufacturingStageStatus::cases(), 'value')),
            'selected_stages.*.notes' => 'nullable|string|max:500',
        ];
    }

    public function messages()
    {
        return [
            'order_number.required' => 'رقم الأمر مطلوب',
            'branch_id.required' => 'يجب اختيار الفرع',
            'branch_id.exists' => 'الفرع المختار غير موجود',
            'item_id.required' => 'يجب اختيار الصنف/المنتج',
            'item_id.exists' => 'الصنف المختار غير موجود',
            'selected_stages.required' => 'يجب إضافة مرحلة واحدة على الأقل',
            'selected_stages.min' => 'يجب إضافة مرحلة واحدة على الأقل',
            'selected_stages.*.quantity.required' => 'الكمية مطلوبة',
            'selected_stages.*.quantity.integer' => 'الكمية يجب أن تكون رقم صحيح',
            'selected_stages.*.quantity.min' => 'الكمية يجب أن تكون صفر أو أكثر',
            'selected_stages.*.estimated_duration.required' => 'المدة المقدرة مطلوبة',
            'selected_stages.*.estimated_duration.numeric' => 'المدة المقدرة يجب أن تكون رقم',
            'selected_stages.*.estimated_duration.min' => 'المدة المقدرة يجب أن تكون صفر أو أكثر',
        ];
    }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function mount()
    {
        $this->templates = ManufacturingOrder::templates()->get();
        if (!$this->order_id) {
            $this->order_number = ManufacturingOrder::generateOrderNumber();
        }
    }

    public function updatedIsTemplate($value)
    {
        if (!$value) {
            $this->template_name = null;
        }
    }

    public function createOrder()
    {
        $this->validate();

        if (empty($this->selected_stages)) {
            session()->flash('error', 'يجب إضافة مرحلة واحدة على الأقل!');
            return;
        }

        DB::transaction(function () {
            $order = ManufacturingOrder::create([
                'order_number' => $this->order_number,
                'branch_id' => $this->branch_id,
                'item_id' => $this->item_id,
                'description' => $this->description,
                'status' => $this->status,
                'is_template' => $this->is_template,
                'template_name' => $this->is_template ? $this->template_name : null,
                'estimated_duration' => 0,
            ]);

            foreach ($this->selected_stages as $index => $stageData) {
                $order->stages()->attach($stageData['id'], [
                    'order' => $index + 1,
                    'quantity' => $stageData['quantity'],
                    'estimated_duration' => $stageData['estimated_duration'],
                    'is_active' => true,
                    'status' => $stageData['status'] ?? ManufacturingStageStatus::PENDING->value,
                    'notes' => $stageData['notes'] ?? null,
                ]);
            }

            $order->calculateTotals();
            $this->resetForm();
            session()->flash('message', 'تم إنشاء أمر التصنيع بنجاح!');
            $this->dispatch('orderCreated');
        });
    }

    public function generateItemCode()
    {
        $newCode = Item::max('code') + 1 ?? 1;
        return $newCode;
    }

    public function itemSelected($data)
    {
        $this->item_id = $data['value'];
    }

    public function updateStageStatus($orderId, $stageId, $newStatus)
    {
        $order = ManufacturingOrder::findOrFail($orderId);

        $pivotData = ['status' => $newStatus];

        if ($newStatus === ManufacturingStageStatus::IN_PROGRESS->value) {
            $pivotData['started_at'] = now();
        } elseif ($newStatus === ManufacturingStageStatus::COMPLETED->value) {
            $pivotData['completed_at'] = now();
        }

        $order->stages()->updateExistingPivot($stageId, $pivotData);

        $order->calculateTotals();

        session()->flash('message', 'تم تحديث حالة المرحلة بنجاح!');
        $this->dispatch('stageStatusUpdated');
    }

    public function viewOrderStages($orderId)
    {
        $this->viewing_order_id = $orderId;
        $this->view_mode = 'stages';
    }

    public function backToList()
    {
        $this->view_mode = 'form';
        $this->viewing_order_id = null;
    }

    public function loadTemplate($template_id)
    {
        if (empty($template_id)) return;

        $template = ManufacturingOrder::with('stages')->findOrFail($template_id);

        $this->order_id = null;
        $this->order_number = ManufacturingOrder::generateOrderNumber();
        $this->branch_id = $template->branch_id;
        $this->item_id = $template->item_id;
        $this->description = $template->description;
        $this->status = 'draft';

        $this->selected_stages = $template->stages->map(function ($stage) {
            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'quantity' => $stage->pivot->quantity ?? 0,
                'estimated_duration' => $stage->pivot->estimated_duration ?? 0,
                'status' => ManufacturingStageStatus::PENDING->value,
                'notes' => $stage->pivot->notes ?? null,
            ];
        })->toArray();

        $this->dispatch('templateLoaded');
    }

    public function editOrder($id)
    {
        $order = ManufacturingOrder::with('stages')->findOrFail($id);

        $this->order_id = $order->id;
        $this->order_number = $order->order_number;
        $this->branch_id = $order->branch_id;
        $this->item_id = $order->item_id;
        $this->description = $order->description;
        $this->status = $order->status;
        $this->is_template = $order->is_template;
        $this->template_name = $order->template_name;

        $this->selected_stages = $order->stages->map(function ($stage) {
            return [
                'id' => $stage->id,
                'name' => $stage->name,
                'quantity' => $stage->pivot->quantity ?? 0,
                'estimated_duration' => $stage->pivot->estimated_duration ?? 0,
                'status' => $stage->pivot->status ?? ManufacturingStageStatus::PENDING->value,
                'notes' => $stage->pivot->notes ?? null,
            ];
        })->toArray();

        $this->view_mode = 'form';
    }

    public function updateOrder()
    {
        $this->validate();

        DB::transaction(function () {
            $order = ManufacturingOrder::findOrFail($this->order_id);
            $order->update([
                'order_number' => $this->order_number,
                'branch_id' => $this->branch_id,
                'item_id' => $this->item_id,
                'description' => $this->description,
                'status' => $this->status,
                'is_template' => $this->is_template,
                'template_name' => $this->is_template ? $this->template_name : null,
            ]);

            $order->stages()->detach();

            foreach ($this->selected_stages as $index => $stageData) {
                $order->stages()->attach($stageData['id'], [
                    'order' => $index + 1,
                    'quantity' => $stageData['quantity'],
                    'estimated_duration' => $stageData['estimated_duration'],
                    'is_active' => true,
                    'status' => $stageData['status'] ?? ManufacturingStageStatus::PENDING->value,
                    'notes' => $stageData['notes'] ?? null,
                ]);
            }

            $order->calculateTotals();
            $this->resetForm();
            session()->flash('message', 'تم تحديث أمر التصنيع بنجاح!');
            $this->dispatch('orderUpdated');
        });
    }

    public function deleteOrder($id)
    {
        $order = ManufacturingOrder::findOrFail($id);
        $order->stages()->detach();
        $order->delete();
        session()->flash('message', 'تم حذف أمر التصنيع بنجاح!');
    }

    public function resetForm()
    {
        $this->resetExcept(['view_mode']);
        $this->status = 'draft';
        $this->selected_stages = [];
        $this->order_id = null;
        $this->order_number = ManufacturingOrder::generateOrderNumber();
        $this->templates = ManufacturingOrder::templates()->get();
        $this->view_mode = 'form';
    }

    public function addStage($stage_id)
    {
        if (empty($stage_id)) return;

        $stage = ManufacturingStage::findOrFail($stage_id);

        $exists = collect($this->selected_stages)->contains('id', $stage->id);
        if ($exists) {
            session()->flash('error', 'هذه المرحلة موجودة بالفعل!');
            return;
        }

        $this->selected_stages[] = [
            'id' => $stage->id,
            'name' => $stage->name,
            'quantity' => 0,
            'estimated_duration' => 0,
            'status' => ManufacturingStageStatus::PENDING->value,
            'notes' => null,
        ];

        $this->dispatch('stageAdded');
    }

    public function removeStage($index)
    {
        unset($this->selected_stages[$index]);
        $this->selected_stages = array_values($this->selected_stages);
    }

    public function render()
    {
        $data = [
            'orders' => ManufacturingOrder::with('branch', 'stages')->latest()->paginate(10),
            'branches' => Branch::all(),
            'available_stages' => ManufacturingStage::all(),
            'templates' => $this->templates,
            'stage_statuses' => ManufacturingStageStatus::cases(),
        ];

        if ($this->view_mode === 'stages' && $this->viewing_order_id) {
            $data['viewing_order'] = ManufacturingOrder::with(['stages' => function ($query) {
                $query->orderBy('manufacturing_order_stage.order');
            }])->findOrFail($this->viewing_order_id);
        }

        return view('manufacturing::livewire.manufacturing-order-manager', $data);
    }
}
