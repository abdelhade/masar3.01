<?php

use Livewire\Volt\Component;
use App\Models\Unit;
use App\Models\Price;
use App\Models\Item;
use App\Models\Note;
use App\Models\NoteDetails;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

new class extends Component {
    public Item $itemModel;

    public $units;
    public $prices;
    public $notes;

    public $item = [
        'name' => '',
        'code' => '',
        'info' => '',
        'notes' => [],
    ];

    public $unitRows = [];

    // Modal properties
    public $showModal = false;
    public $modalType = ''; // 'unit' or 'note'
    public $modalTitle = '';
    public $modalData = [
        'name' => '',
        'note_id' => null
    ];

    public function mount(Item $itemModel)
    {
        $this->itemModel = $itemModel->load('units', 'prices', 'notes', 'barcodes');
        $this->units = Unit::all();
        $this->prices = Price::all();
        $this->notes = Note::with('noteDetails')->get();

        $this->item = [
            'name' => $this->itemModel->name,
            'code' => $this->itemModel->code,
            'info' => $this->itemModel->info,
            'notes' => $this->itemModel->notes->pluck('pivot.note_detail_name', 'id')->toArray(),
        ];

        $rowIndex = 0;
        foreach ($this->itemModel->units as $unit) {
            $unitPrices = $this->itemModel->prices()->wherePivot('unit_id', $unit->id)->get()->pluck('pivot.price', 'id')->toArray();

            $barcodes = $this->itemModel->barcodes()->where('unit_id', $unit->id)->pluck('barcode')->filter()->values()->toArray();
            
            // إذا لم يكن هناك باركودات، أضف باركود فارغ واحد
            if (empty($barcodes)) {
                $barcodes = [''];
            } else {
                // أضف باركود فارغ في النهاية للباركودات الإضافية
                $barcodes[] = '';
            }

            $this->unitRows[] = [
                'unit_id' => $unit->id,
                'u_val' => $unit->pivot->u_val,
                'cost' => $unit->pivot->cost,
                'barcodes' => $barcodes,
                'prices' => $unitPrices,
            ];
            $rowIndex++;
        }

        if (empty($this->unitRows)) {
            $this->addUnitRow();
        }
    }

    protected function rules()
    {
        return [
            'item.name' => ['required', 'min:3', Rule::unique('items', 'name')->ignore($this->itemModel->id)],
            'item.notes.*' => 'nullable',
            'unitRows.*.barcodes.*' => ['nullable', 'string', 'distinct', 'max:25', Rule::unique('barcodes', 'barcode')->where(fn($query) => $query->where('item_id', '!=', $this->itemModel->id))],
            'unitRows.*.cost' => 'required|numeric|min:0',
            'unitRows.0.u_val' => ['required', 'numeric', 'min:1', 'distinct', function($attribute, $value, $fail) {
                if ($value != 1) {
                    $fail('معامل التحويل للوحدة الأساسية يجب أن يكون 1.');
                }
            }],
            'unitRows.*.u_val' => 'required|numeric|min:0.0001',
            'unitRows.*.unit_id' => 'required|exists:units,id|distinct',
            'unitRows.*.prices.*' => 'required|numeric|min:0',
        ];
    }

    protected $messages = [
        'item.name.required' => 'اسم الصنف مطلوب.',
        'item.name.min' => 'اسم الصنف يجب أن يكون أطول من 3 أحرف.',
        'item.name.unique' => 'اسم الصنف مستخدم بالفعل.',
        'unitRows.*.unit_id.exists' => 'الوحدة غير موجودة.',
        'unitRows.*.unit_id.required' => 'الوحدة مطلوبة.',
        'unitRows.*.unit_id.distinct' => 'الوحدة مستخدمة بالفعل.',
        'unitRows.*.barcodes.*.string' => 'الباركود يجب أن يكون نصاً.',
        'unitRows.*.barcodes.*.distinct' => 'الباركود مستخدم بالفعل.',
        'unitRows.*.barcodes.*.unique' => 'الباركود مستخدم بالفعل.',
        'unitRows.*.cost.required' => 'التكلفة مطلوبة.',
        'unitRows.*.cost.numeric' => 'التكلفة يجب أن تكون رقماً.',
        'unitRows.*.cost.min' => 'التكلفة يجب أن تكون 0 على الأقل.',
        'unitRows.*.u_val.required' => 'معامل التحويل مطلوب.',
        'unitRows.*.u_val.numeric' => 'معامل التحويل يجب أن يكون رقماً.',
        'unitRows.*.u_val.min' => 'معامل التحويل يجب أن يكون 0.0001 على الأقل.',
        'unitRows.*.u_val.distinct' => 'معامل التحويل مستخدم بالفعل.',
        'unitRows.*.prices.*.required' => 'السعر مطلوب.',
        'unitRows.*.prices.*.numeric' => 'السعر يجب أن يكون رقماً.',
        'unitRows.*.prices.*.min' => 'السعر يجب أن يكون 0 على الأقل.',
    ];

    public function addUnitRow()
    {
        $this->unitRows[] = [
            'unit_id' => $this->units->first()?->id,
            'u_val' => 1,
            'cost' => 0,
            'barcodes' => [''],
            'prices' => [],
        ];
    }

    public function removeUnitRow($index)
    {
        unset($this->unitRows[$index]);
        $this->unitRows = array_values($this->unitRows);
    }

    public function update()
    {
        $this->validate();

        $unitsSync = [];
        $barcodesToCreate = [];
        $pricesToSync = [];

        DB::beginTransaction();

        try {
            $this->itemModel->update($this->item);
            Log::info('Item updated', ['item_id' => $this->itemModel->id]);

            foreach ($this->unitRows as $unitRowIndex => $unitRow) {
                if (empty($unitRow['unit_id'])) {
                    continue;
                }

                $unitsSync[$unitRow['unit_id']] = [
                    'u_val' => $unitRow['u_val'],
                    'cost' => $unitRow['cost'],
                ];

                $hasValidBarcode = false;
                if (!empty($unitRow['barcodes'])) {
                    foreach ($unitRow['barcodes'] as $barcodeIndex => $barcode) {
                        if (!empty(trim($barcode))) {
                            $barcodesToCreate[] = ['unit_id' => $unitRow['unit_id'], 'barcode' => trim($barcode)];
                            $hasValidBarcode = true;
                        }
                    }
                }
                
                // إذا لم يكن هناك باركودات صالحة، أنشئ باركود افتراضي
                if (!$hasValidBarcode) {
                    $barcodesToCreate[] = ['unit_id' => $unitRow['unit_id'], 'barcode' => $this->item['code'] . ($unitRowIndex + 1)];
                }

                if (!empty($unitRow['prices'])) {
                    foreach ($unitRow['prices'] as $price_id => $price_value) {
                        $pricesToSync[] = ['price_id' => $price_id, 'unit_id' => $unitRow['unit_id'], 'price' => $price_value];
                    }
                }
            }

            $this->itemModel->units()->sync($unitsSync);
            Log::info('Units synced successfully');

            $this->itemModel->barcodes()->delete();
            if (!empty($barcodesToCreate)) {
                $this->itemModel->barcodes()->createMany($barcodesToCreate);
            }
            Log::info('Barcodes synced successfully');

            $this->itemModel->prices()->detach();
            foreach ($pricesToSync as $priceData) {
                $this->itemModel->prices()->attach($priceData['price_id'], ['unit_id' => $priceData['unit_id'], 'price' => $priceData['price']]);
            }
            Log::info('Prices synced successfully');

            if (isset($this->item['notes'])) {
                $notesToSync = collect($this->item['notes'])
                    ->filter()
                    ->mapWithKeys(function ($noteDetailName, $noteId) {
                        return [$noteId => ['note_detail_name' => $noteDetailName]];
                    })
                    ->all();
                $this->itemModel->notes()->sync($notesToSync);
            } else {
                $this->itemModel->notes()->detach();
            }
            Log::info('Notes synced successfully');

            DB::commit();
            Log::info('Transaction committed successfully');
            session()->flash('success', 'تم تحديث الصنف بنجاح!');
            $this->dispatch('$refresh');
            return redirect()->route('items.index')->with('success', 'تم تحديث الصنف بنجاح!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating item', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'item' => $this->item,
                'unit_rows' => $this->unitRows,
            ]);
            session()->flash('error', 'حدث خطأ أثناء تحديث الصنف. يرجى المحاولة مرة أخرى.');
        }
    }
    public function updateUnitsCostAndPrices($index)
    {
        if ($index != 0 && isset($this->unitRows[$index]['u_val']) && $this->unitRows[$index]['u_val'] != null) {
            $this->unitRows[$index]['cost'] = $this->unitRows[$index]['u_val'] * $this->unitRows[0]['cost'];
            foreach ($this->prices as $price) {
                $basePrice = $this->unitRows[0]['prices'][$price->id] ?? 0;
                $this->unitRows[$index]['prices'][$price->id] = $this->unitRows[$index]['u_val'] * $basePrice;
            }
        } elseif ($index == 0 && isset($this->unitRows[$index]['u_val'])) {
            $this->validate([
                'unitRows.0.u_val' => [
                    'required',
                    'numeric',
                    'min:1',
                    'distinct',
                    function ($attribute, $value, $fail) {
                        if ($value != 1) {
                            $fail('معامل التحويل للوحدة الأساسية يجب أن يكون 1.');
                        }
                    },
                ],
            ]);
        }
    }

    public function updateUnitsCost($index)
    {
        // if $index == 0 update the cost of other units
        if ($index == 0 && isset($this->unitRows[$index]['cost']) && $this->unitRows[$index]['cost'] != null) {
            foreach ($this->unitRows as $unitRowIndex => $unitRow) {
                if ($unitRowIndex != $index) {
                    $baseCost = $this->unitRows[0]['cost'] ?? 0;
                    $this->unitRows[$unitRowIndex]['cost'] = $unitRow['u_val'] * $baseCost;
                }
            }
        }
    }

    // Modal functions
    public function openModal($type, $noteId = null)
    {
        $this->modalType = $type;
        $this->resetModalData();
        
        if ($type === 'unit') {
            $this->modalTitle = 'إنشاء وحدة جديدة';
        } elseif ($type === 'note_detail' && $noteId) {
            $note = Note::find($noteId);
            $this->modalTitle = 'إضافة جديد' .' '. '[ ' . $note->name . ' ]';
            $this->modalData['note_id'] = $noteId;
        }
        
        $this->showModal = true;
    }

    public function closeModal()
    {
        $this->showModal = false;
        $this->modalType = '';
        $this->modalTitle = '';
        $this->resetModalData();
        $this->resetValidation();
    }

    public function resetModalData()
    {
        $this->modalData = [
            'name' => '',
            'note_id' => null
        ];
    }

    public function saveModalData()
    {
        $rules = [
            'modalData.name' => 'required|min:1|max:255',
        ];

        if ($this->modalType === 'unit') {
            $rules['modalData.name'] .= '|unique:units,name';
        } elseif ($this->modalType === 'note_detail' && $this->modalData['note_id']) {
            $rules['modalData.name'] .= '|unique:note_details,name';
        }

        $this->validate($rules, [
            'modalData.name.required' => 'الاسم مطلوب.',
            'modalData.name.min' => 'الاسم يجب أن يكون أطول من حرف واحد.',
            'modalData.name.max' => 'الاسم يجب أن يكون أقصر من 255 حرف.',
            'modalData.name.unique' => 'الاسم مستخدم بالفعل.',
        ]);

        try {
            DB::beginTransaction();

            if ($this->modalType === 'unit') {
                // Create new unit
                $unit = Unit::create([
                    'name' => $this->modalData['name'],
                    'code' => Unit::max('code') + 1 ?? 1,
                ]);
                
                // Refresh units list
                $this->units = Unit::all();
                
                session()->flash('success', 'تم إنشاء الوحدة بنجاح!');
                
            } elseif ($this->modalType === 'note_detail' && $this->modalData['note_id']) {
                // Create new note detail
                $noteDetail = NoteDetails::create([
                    'note_id' => $this->modalData['note_id'],
                    'name' => $this->modalData['name'],
                ]);
                
                // Refresh notes list
                $this->notes = Note::with('noteDetails')->get();
                
                session()->flash('success', 'تم إضافة ' . $this->modalData['name'] . ' بنجاح!');
            }

            DB::commit();
            $this->closeModal();
            
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving modal data', [
                'error' => $e->getMessage(),
                'modal_type' => $this->modalType,
                'modal_data' => $this->modalData,
            ]);
            session()->flash('error', 'حدث خطأ أثناء الحفظ. يرجى المحاولة مرة أخرى.');
        }
    }

    // Barcode management functions
    public function addAdditionalBarcode($unitRowIndex)
    {
        $this->dispatch('open-modal', 'add-barcode-modal.' . $unitRowIndex);
    }

    public function addBarcodeField($unitRowIndex)
    {
        $this->unitRows[$unitRowIndex]['barcodes'][] = '';
        //auto focus on the last input
        $this->dispatch('auto-focus', 'unitRows.' . $unitRowIndex . '.barcodes.' . (count($this->unitRows[$unitRowIndex]['barcodes']) - 1));
    }

    public function removeBarcodeField($unitRowIndex, $barcodeIndex)
    {
        unset($this->unitRows[$unitRowIndex]['barcodes'][$barcodeIndex]);
        $this->unitRows[$unitRowIndex]['barcodes'] = array_values($this->unitRows[$unitRowIndex]['barcodes']);
        
        // إذا لم يعد هناك باركودات، أضف باركود فارغ واحد
        if (empty($this->unitRows[$unitRowIndex]['barcodes'])) {
            $this->unitRows[$unitRowIndex]['barcodes'] = [''];
        }
    }

    public function saveBarcodes($unitRowIndex)
    {
        $this->dispatch('close-modal', 'add-barcode-modal.' . $unitRowIndex);
    }

    public function cancelBarcodeUpdate($unitRowIndex)
    {
        $this->dispatch('close-modal', 'add-barcode-modal.' . $unitRowIndex);
    }

    public function showBarcodes($index)
    {
        $this->dispatch('open-modal', 'add-barcode-modal.' . $index);
    }
}; ?>

<div>
    {{-- form --}}
    <div class="">
        <div class="">
            <h5 class="">
                {{ 'تعديل الصنف' }}</h5>
        </div>
        @if (session()->has('success'))
            <div class="alert alert-success font-family-cairo fw-bold font-12 mt-2" x-data="{ show: true }" x-show="show"
                x-init="setTimeout(() => show = false, 3000)">
                {{ session('success') }}
            </div>
        @endif
        @if (session()->has('error'))
            <div class="alert alert-danger font-family-cairo fw-bold font-12 mt-2" x-data="{ show: true }" x-show="show"
                x-init="setTimeout(() => show = false, 3000)">
                {{ session('error') }}
            </div>
        @endif
        <div class="">
            <form wire:submit.prevent="update" wire:loading.attr="disabled" wire:target="update">
                <!-- Basic Item Information -->
                <fieldset class="shadow-sm">
                    <div class="col-md-12 p-3">
                        <div class="row">
                            <div class="col-md-1 mb-3">
                                <label for="code" class="form-label font-family-cairo fw-bold">رقم
                                    الصنف</label>
                                <input type="text" wire:model="item.code"
                                    class="form-control font-family-cairo fw-bold" id="code">
                                @error('item.code')
                                    <span class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="name" class="form-label font-family-cairo fw-bold">اسم
                                    الصنف</label>
                                <input type="text" wire:model="item.name"
                                    class="form-control font-family-cairo fw-bold" id="name" x-ref="nameInput">
                                @error('item.name')
                                    <span class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            @foreach ($notes as $note)
                                <div class="col-md-2 mb-3">
                                    <label for="type"
                                        class="form-label font-family-cairo fw-bold">{{ $note->name }}</label>
                                    <div class="input-group">
                                        <button type="button"
                                            class="btn btn-outline-success font-family-cairo fw-bold"
                                            wire:click="openModal('note_detail', {{ $note->id }})"
                                            title="إضافة جديد">
                                            <i class="las la-plus"></i>
                                        </button>
                                        <select wire:model="item.notes.{{ $note->id }}"
                                            class="form-select font-family-cairo fw-bold" id="note-{{ $note->id }}">
                                            <option class="font-family-cairo fw-bold" value="">إختر</option>
                                            @foreach ($note->noteDetails as $noteDetail)
                                                <option class="font-family-cairo fw-bold" value="{{ $noteDetail->name }}">
                                                    {{ $noteDetail->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error("item.notes.{$note->id}")
                                        <span class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endforeach



                            <div class="col-md-12 mb-3">
                                <label for="Details" class="form-label font-family-cairo fw-bold">التفاصيل</label>
                                <textarea wire:model="item.info" class="form-control font-family-cairo fw-bold" id="description" rows="2"></textarea>
                                @error('item.details')
                                    <span class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>
                <!-- Units Repeater Section -->
                <fieldset class="shadow-sm mt-2">
                    <div class="col-md-12 p-2">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="font-family-cairo fw-bold mb-0">وحدات الصنف</h6>
                                <button type="button"
                                    class="btn btn-outline-success btn-sm font-family-cairo fw-bold mt-3"
                                    wire:click="openModal('unit')"
                                    title="إنشاء وحدة جديدة">
                                    <i class="las la-plus"></i> إنشاء وحدة جديدة
                                </button>
                            </div>
                            <button type="button" class="btn btn-primary btn-sm font-family-cairo fw-bold"
                                wire:click="addUnitRow">
                                <i class="las la-plus"></i> إضافة وحدة للصنف
                            </button>
                        </div>
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="table table-striped mb-0" style="min-width: 1200px;">
                                <thead class="table-light text-center align-middle">

                                    <tr>
                                        <th class="font-family-cairo fw-bold">الوحدة</th>
                                        <th class="font-family-cairo fw-bold">معامل التحويل</th>
                                        <th class="font-family-cairo fw-bold">التكلفة</th>
                                        @foreach ($prices as $price)
                                            <th class="font-family-cairo fw-bold">{{ $price->name }}</th>
                                        @endforeach
                                        <th class="font-family-cairo fw-bold">باركود</th>
                                        <th class="font-family-cairo fw-bold">XX</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($unitRows as $index => $unitRow)
                                        <tr wire:key="{{ $index }}">
                                            <td>
                                                <select wire:model.live="unitRows.{{ $index }}.unit_id"
                                                    class="form-select font-family-cairo fw-bold"
                                                    style="min-width: 100px;">
                                                    <option class="font-family-cairo fw-bold" value="">
                                                        إختر</option>
                                                    @foreach ($units as $unit)
                                                        <option class="font-family-cairo fw-bold"
                                                            value="{{ $unit->id }}">
                                                            {{ $unit->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                @error("unitRows.{$index}.unit_id")
                                                    <span
                                                        class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="number" onclick="this.select()"
                                                    wire:model="unitRows.{{ $index }}.u_val"
                                                    wire:keyup.debounce.300ms="updateUnitsCostAndPrices({{ $index }})"
                                                    class="form-control font-family-cairo fw-bold" min="1"
                                                    step="0.0001"
                                                    style="min-width: 150px;">
                                                @error("unitRows.{$index}.u_val")
                                                    <span
                                                        class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="number" onclick="this.select()"
                                                    wire:model="unitRows.{{ $index }}.cost"
                                                    wire:keyup.debounce.300ms="updateUnitsCost({{ $index }})"
                                                    class="form-control font-family-cairo fw-bold"
                                                    step="0.0001"
                                                    style="min-width: 150px;">
                                                @error("unitRows.{$index}.cost")
                                                    <span
                                                        class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            @foreach ($prices as $price)
                                                <td>
                                                    <input type="number" onclick="this.select()"
                                                        wire:model="unitRows.{{ $index }}.prices.{{ $price->id }}"
                                                        class="form-control font-family-cairo fw-bold"
                                                        step="0.0001"
                                                        style="min-width: 150px;">
                                                    @error("unitRows.{$index}.prices.{$price->id}")
                                                        <span
                                                            class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                            @endforeach
                                            <td class="d-flex text-center flex-column gap-1 mt-4">
                                                <input type="text" onclick="this.select()"
                                                    wire:model="unitRows.{{ $index }}.barcodes.0"
                                                    class="form-control font-family-cairo fw-bold"
                                                    maxlength="25" style="min-width: 150px;"
                                                    placeholder="الباركود الأساسي">
                                                {{-- add button to add more barcodes --}}
                                                <button type="button"
                                                    class="btn btn-primary btn-sm font-family-cairo fw-bold"
                                                    wire:click="addAdditionalBarcode({{ $index }})">
                                                    <i class="las la-plus"></i> باركود إضافى
                                                </button>
                                                @error("unitRows.{$index}.barcodes.0")
                                                    <span
                                                        class="text-danger font-family-cairo fw-bold font-12">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <button type="button"  class="btn btn-danger btn-icon-square-sm float-end"
                                                    wire:click="removeUnitRow({{ $index }})">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <!-- Additional Barcode Modal -->
                                        <div wire:ignore.self class="modal fade"
                                            id="add-barcode-modal.{{ $index }}" tabindex="-1"
                                            aria-labelledby="addBarcodeModalLabel" aria-hidden="true"
                                            data-bs-backdrop="static" data-bs-keyboard="false">
                                            <div class="modal-dialog modal-dialog-centered">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="font-family-cairo fw-bold text-white"
                                                            id="addBarcodeModalLabel">
                                                            إضافة وتعديل الباركود
                                                        </h5>
                                                        <button type="button" class="btn-close"
                                                            data-bs-dismiss="modal"
                                                            wire:click="cancelBarcodeUpdate({{ $index }})"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="d-flex justify-content-end mb-2">
                                                            <button type="button"
                                                                class="btn btn-primary btn-sm font-family-cairo fw-bold"
                                                                wire:click="addBarcodeField({{ $index }})">
                                                                <i class="las la-plus"></i> إضافة باركود
                                                            </button>
                                                        </div>

                                                        @foreach ($unitRow['barcodes'] as $barcodeIndex => $barcode)
                                                            @if($barcodeIndex > 0) {{-- عرض الباركودات الإضافية فقط (تخطي الباركود الأول) --}}
                                                                <div class="d-flex align-items-center mb-2"
                                                                    wire:key="{{ $index }}-barcode-{{ $barcodeIndex }}">
                                                                    <input type="text"
                                                                        class="form-control font-family-cairo fw-bold"
                                                                        wire:model.live="unitRows.{{ $index }}.barcodes.{{ $barcodeIndex }}"
                                                                        id="unitRows.{{ $index }}.barcodes.{{ $barcodeIndex }}"
                                                                        placeholder="أدخل الباركود الإضافي">
                                                                    <button type="button"
                                                                        class="btn btn-danger btn-sm ms-2"
                                                                        wire:click="removeBarcodeField({{ $index }}, {{ $barcodeIndex }})">
                                                                        <i class="far fa-trash-alt"></i>
                                                                    </button>
                                                                </div>
                                                                @error("unitRows.{{ $index }}.barcodes.{{ $barcodeIndex }}")
                                                                    <span
                                                                        class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                                                @enderror
                                                            @endif
                                                        @endforeach
                                                        
                                                        {{-- إذا لم يكن هناك باركودات إضافية، أظهر رسالة --}}
                                                        @if(count($unitRow['barcodes']) <= 1)
                                                            <div class="text-center text-muted font-family-cairo fw-bold py-3">
                                                                <i class="las la-info-circle"></i> لا توجد باركودات إضافية
                                                            </div>
                                                        @endif
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button"
                                                            class="btn btn-secondary font-family-cairo fw-bold"
                                                            data-bs-dismiss="modal"
                                                            wire:click="cancelBarcodeUpdate({{ $index }})">إلغاء</button>
                                                        <button type="button"
                                                            class="btn btn-primary font-family-cairo fw-bold"
                                                            wire:click="saveBarcodes({{ $index }})">حفظ</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                </fieldset>

                <div class="mt-3">
                    <button type="button" class="btn btn-secondary font-family-cairo fw-bold"
                        onclick="window.history.back()">عوده / إلغاء</button>
                    <button type="submit"
                        class="btn btn-primary font-family-cairo fw-bold">{{ 'تحديث' }}</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Universal Modal for creating units and notes --}}
    @if($showModal)
    <div class="modal fade show" style="display: block;" tabindex="-1" role="dialog" aria-modal="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title font-family-cairo fw-bold text-white" id="universalModalLabel">
                        {{ $modalTitle }}
                    </h5>
                    <button type="button" class="btn-close btn-close-white" wire:click="closeModal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    @if (session()->has('success'))
                        <div class="alert alert-success font-family-cairo fw-bold font-12 mb-3" x-data="{ show: true }" x-show="show"
                            x-init="setTimeout(() => show = false, 3000)">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if (session()->has('error'))
                        <div class="alert alert-danger font-family-cairo fw-bold font-12 mb-3" x-data="{ show: true }" x-show="show"
                            x-init="setTimeout(() => show = false, 5000)">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <form wire:submit.prevent="saveModalData">
                        <div class="mb-3">
                            <label for="modalName" class="form-label font-family-cairo fw-bold">الاسم</label>
                            <input type="text" 
                                   wire:model="modalData.name" 
                                   class="form-control font-family-cairo fw-bold" 
                                   id="modalName" 
                                   placeholder="أدخل الاسم"
                                   autofocus>
                            @error('modalData.name')
                                <span class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                            @enderror
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" 
                            class="btn btn-secondary font-family-cairo fw-bold" 
                            wire:click="closeModal">
                        إلغاء
                    </button>
                    <button type="button" 
                            class="btn btn-primary font-family-cairo fw-bold" 
                            wire:click="saveModalData"
                            wire:loading.attr="disabled"
                            wire:target="saveModalData">
                        <span wire:loading.remove wire:target="saveModalData">حفظ</span>
                        <span wire:loading wire:target="saveModalData">جاري الحفظ...</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal-backdrop fade show"></div>
    @endif
</div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.addEventListener('livewire:init', () => {
            window.addEventListener('open-modal', event => {
                let modal = new bootstrap.Modal(document.getElementById(event.detail[0]));
                modal.show();
            });

            window.addEventListener('close-modal', event => {
                let modal = bootstrap.Modal.getInstance(document.getElementById(event.detail[0]));
                if (modal) {
                    modal.hide();
                }
                const backdrop = document.querySelector('.modal-backdrop');
                if (backdrop) {
                    backdrop.remove();
                }
            });

            // Auto-focus functionality
            Livewire.on('auto-focus', function(inputId) {
                // Add a small delay to ensure DOM is updated
                setTimeout(() => {
                    const element = document.getElementById(inputId);
                    if (element) {
                        element.focus();
                    }
                }, 100);
            });

            // منع زر الإدخال (Enter) من حفظ النموذج
            document.querySelectorAll('form').forEach(function(form) {
                form.addEventListener('keydown', function(e) {
                    // إذا كان الزر Enter وتم التركيز على input وليس textarea أو زر
                    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.type !== 'submit' && e.target.type !== 'button') {
                        e.preventDefault();
                    }
                });
            });

            // إغلاق المودال عند الضغط على Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    Livewire.dispatch('closeModal');
                }
            });

            // إغلاق المودال عند النقر خارج المودال
            document.addEventListener('click', function(e) {
                if (e.target.classList.contains('modal-backdrop')) {
                    Livewire.dispatch('closeModal');
                }
            });

            // حفظ البيانات عند الضغط على Enter في المودال
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && document.querySelector('.modal.show')) {
                    const modalInput = document.querySelector('.modal.show input[type="text"]');
                    if (modalInput && modalInput === document.activeElement) {
                        e.preventDefault();
                        Livewire.dispatch('saveModalData');
                    }
                }
            });
        });
    });
</script>
{{-- finshed --}}