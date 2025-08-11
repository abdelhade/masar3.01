<?php

use Livewire\Volt\Component;
use App\Models\Unit;
use App\Models\Price;
use App\Models\Item;
use App\Models\AccHead;
use App\Models\Note;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;

new class extends Component {
    //
    public $creating = true;
    public $units;
    public $prices;
    public $notes;
    public $additionalBarcodes = [];
    // public $editingBarcodeIndex = null;

    // Basic item information
    public $item = [
        'name' => '',
        'code' => '',
        'info' => '',
        'notes' => [],
    ];
    // For managing item units
    public $unitRows = [];

    public function mount()
    {
        $this->units = Unit::all();
        $this->prices = Price::all();
        $this->notes = Note::with('noteDetails')->get();
        $this->addUnitRow();
        $this->item['code'] = Item::max('code') + 1 ?? 1;
    }

    protected function rules()
    {
        return [
            'item.name' => 'required|min:3|unique:items,name',
            'item.*.notes.*' => 'nullable|exists:note_details,id',
            'unitRows.*.barcodes.*' => 'nullable|unique:barcodes,barcode|string|distinct|max:25',
            'unitRows.*.cost' => 'required|numeric|min:0|distinct',
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
            'unitRows.*.u_val' => 'required|numeric|min:0.0001|distinct',
            'unitRows.*.unit_id' => 'required|exists:units,id|distinct',
            'unitRows.*.prices.*' => 'required|numeric|min:0',
            'unitRows.*.barcodes.*' => 'required|string|distinct|max:25|unique:barcodes,barcode',
        ];
    }

    protected $messages = [
        'item.name.required' => 'اسم الصنف مطلوب.',
        'item.name.min' => 'اسم الصنف يجب أن يكون أطول من 3 أحرف.',
        'item.name.unique' => 'اسم الصنف مستخدم بالفعل.',
        'item.*.notes.*.exists' => 'الملاحظة غير موجودة.',
        'unitRows.*.unit_id.exists' => 'الوحدة غير موجودة.',
        'unitRows.*.unit_id.required' => 'الوحدة مطلوبة.',
        'unitRows.*.unit_id.distinct' => 'الوحدة مستخدمة بالفعل.',
        'unitRows.*.barcodes.*.string' => 'الباركود يجب أن يكون نصاً.',
        'unitRows.*.barcodes.*.distinct' => 'باركود إضافى مكرر راجع قائمة الباركود الإضافى',
        'unitRows.*.barcodes.*.unique' => 'الباركود مستخدم بالفعل.',
        'unitRows.*.barcodes.*.max' => 'الباركود يجب أن يكون أقصر من 25 حرف.',
        'unitRows.*.barcodes.*.required' => 'الباركود مطلوب.',
        'unitRows.*.cost.required' => 'التكلفة مطلوبة.',
        'unitRows.*.cost.numeric' => 'التكلفة يجب أن تكون رقماً.',
        'unitRows.*.cost.min' => 'التكلفة يجب أن تكون 0 على الأقل.',
        'unitRows.*.cost.distinct' => 'التكلفة مستخدمة بالفعل.',
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
        // $this->validate([
        //     'unitRows.*.unit_id' => 'required|exists:units,id|distinct',
        //     'unitRows.*.u_val' => 'required|numeric|min:1|distinct',
        //     'unitRows.*.cost' => 'required|numeric|min:0|distinct',
        //     'unitRows.*.prices.*' => 'required|distinct|numeric|min:0',
        //     'unitRows.*.barcodes.*' => 'nullable|unique:barcodes,barcode|string|distinct',
        // ]);
        $this->unitRows[] = [
            'unit_id' => $this->units->first()->id,
            'u_val' => 1,
            'cost' => 0,
            'barcodes' => [],
            'prices' => [],
        ];
        if (count($this->unitRows) > 1) {
            $this->unitRows[count($this->unitRows) - 2]['barcodes'][] = $this->item['code'] . (count($this->unitRows) - 1);
        }
    }

    public function removeUnitRow($index)
    {
        unset($this->unitRows[$index]);
        $this->unitRows = array_values($this->unitRows);
    }

    public function resetForm()
    {
        $this->item = [
            'name' => '',
            'code' => Item::max('code') + 1 ?? 1,
            'info' => '',
            'notes' => [],
        ];
        $this->unitRows = [];
        $this->addUnitRow();
    }

    public function save()
    {
        $unitsSync = [];
        $barcodesToCreate = [];
        $pricesSync = [];

        foreach ($this->unitRows as $unitRowIndex => &$unitRow) {
            if (empty($unitRow['barcodes'])) {
                $this->unitRows[$unitRowIndex]['barcodes'][] = $this->item['code'] . ($unitRowIndex + 1);
            }
        }
        unset($unitRow); // Important: unset the reference
        $this->unitRows = array_values($this->unitRows);

        $this->validate();

        try {
            DB::beginTransaction();

            foreach ($this->unitRows as $unitRowIndex => $unitRow) {
                $unitsSync[$unitRow['unit_id']] = [
                    'u_val' => $unitRow['u_val'],
                    'cost' => $unitRow['cost'],
                ];
                if (!empty($unitRow['barcodes'])) {
                    foreach ($unitRow['barcodes'] as $barcode) {
                        if (!empty($barcode)) {
                            $barcodesToCreate[] = ['unit_id' => $unitRow['unit_id'], 'barcode' => $barcode];
                        }
                    }
                } else {
                    $barcodesToCreate[] = ['unit_id' => $unitRow['unit_id'], 'barcode' => $this->item['code'] . $unitRowIndex + 1];
                }

                $pricesSync[] = collect($unitRow['prices'])->mapWithKeys(fn($price, $id) => [$id => ['unit_id' => $unitRow['unit_id'], 'price' => $price]])->all();
            }

            $itemModel = Item::create($this->item);

            // Process units
            $itemModel->units()->attach($unitsSync);
            // Process barcodes
            $itemModel->barcodes()->createMany($barcodesToCreate);
            // Process prices
            foreach ($pricesSync as $index => $prices) {
                foreach ($prices as $price_id => $price) {
                    $itemModel->prices()->attach($price_id, ['unit_id' => $price['unit_id'], 'price' => $price['price']]);
                }
            }
            // Process notes
            $itemModel->notes()->attach(collect($this->item['notes'])->mapWithKeys(fn($noteDetailName, $noteId) => [$noteId => ['note_detail_name' => $noteDetailName]])->all());
            Log::info('Notes synced successfully');
            $itemModel->save();
            $itemModel->refresh();

            DB::commit();
            Log::info('Transaction committed successfully');
            $this->creating = false;
            session()->flash('success', 'تم إنشاء الصنف بنجاح!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error saving item', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'item' => $this->item,
                'unit_rows' => $this->unitRows,
            ]);
            session()->flash('error', 'حدث خطأ أثناء حفظ الصنف. يرجى المحاولة مرة أخرى.');
        }
    }

    public function edit($itemId)
    {
        // $itemModel = MyItem::with(['units'])->find($itemId);
        // $this->item = [
        //     'id' => $itemModel->id,
        //     'name' => $itemModel->name,
        //     'code' => $itemModel->code,
        //     'details' => $itemModel->details,
        //     'type' => $itemModel->type,
        //     'group_id' => $itemModel->group_id,
        //     'category_id' => $itemModel->category_id,
        //     'supplier_id' => $itemModel->supplier_id,
        //     'location_id' => $itemModel->location_id,
        // ];

        // // Set unit rows
        // $this->unitRows = [];
        // foreach ($itemModel->units as $unit) {
        //     $this->unitRows[] = [
        //         'u_val' => $unit->pivot->u_val,
        //         'last_cost' => $unit->pivot->last_cost,
        //         'barcode' => $unit->pivot->barcode,
        //         'unit_id' => $unit->id,
        //     ];
        // }

        // if (empty($this->unitRows)) {
        //     $this->addUnitRow();
        // }

        // $this->isEditing = true;
        // $this->openModal();
        // dd($this->showModal,$this->isEditing,$itemId);
    }

    public function addAdditionalBarcode($unitRowIndex)
    {
        // if (count($this->unitRows[$unitRowIndex]['barcodes']) < 2) {
        //     $this->addBarcodeField($unitRowIndex);
        // }
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
    }

    public function saveBarcodes($unitRowIndex)
    {
        // $this->validate([
        //     'unitRows.*.barcodes.*' => 'required|string|distinct',
        // ]);
        $this->dispatch('close-modal', 'add-barcode-modal.' . $unitRowIndex);
    }

    public function cancelBarcodeUpdate($unitRowIndex)
    {
        // $this->reset(
        //     'additionalBarcodes',
        //     // , 'editingBarcodeIndex'
        // );
        $this->dispatch('close-modal', 'add-barcode-modal.' . $unitRowIndex);
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

    public function showBarcodes($index)
    {
        $this->dispatch('open-modal', 'add-barcode-modal.' . $index);
    }

    public function createNew()
    {
        $this->resetForm();
        $this->resetValidation();
        $this->item['code'] = Item::max('code') + 1 ?? 1;
        $this->creating = true;
        $this->dispatch('auto-focus', 'item-name');
    }

    public function createNewFromCurrent()
    {
        $this->resetValidation();
        $this->item['code'] = Item::max('code') + 1 ?? 1;
        foreach ($this->unitRows as $unitRowIndex => $unitRow) {
            $this->unitRows[$unitRowIndex]['barcodes'] = [];
        }
        $this->creating = true;
        $this->dispatch('auto-focus', 'item-name');
    }
}; ?>

<div>
    {{-- form --}}
    <div class="">
        <div class="">
            <h5 class="">
                {{ 'إضافة صنف جديد' }}</h5>
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
            <form wire:submit.prevent="save" wire:loading.attr="disabled" wire:target="save"
                wire:loading.class="opacity-50">
                <!-- Basic Item Information -->
                <fieldset class="shadow-sm">
                    <div class="col-md-12 p-3">
                        <div class="row">
                            <div class="col-md-1 mb-3">
                                <label for="code" class="form-label font-family-cairo fw-bold">رقم
                                    الصنف</label>
                                <input type="text" wire:model.live="item.code"
                                    class="form-control font-family-cairo fw-bold" id="code"
                                    value="{{ $item['code'] }}" readonly disabled>
                                @error('item.code')
                                    <span class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="name" class="form-label font-family-cairo fw-bold">اسم
                                    الصنف</label>
                                <input type="text" wire:model="item.name"
                                    class="form-control font-family-cairo fw-bold frst" id="item-name" x-ref="nameInput"
                                    @if (!$creating) disabled readonly @endif>
                                @error('item.name')
                                    <span class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            @foreach ($notes as $note)
                                <div class="col-md-2 mb-3">
                                    <label for="type"
                                        class="form-label font-family-cairo fw-bold">{{ $note->name }}</label>
                                    <select wire:model="item.notes.{{ $note->id }}"
                                        @if (!$creating) disabled readonly @endif
                                        class="form-select font-family-cairo fw-bold" id="note-{{ $note->id }}">
                                        <option class="font-family-cairo fw-bold" value="">إختر</option>
                                        @foreach ($note->noteDetails as $noteDetail)
                                            <option class="font-family-cairo fw-bold" value="{{ $noteDetail->name }}">
                                                {{ $noteDetail->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error("item.notes.{$note->id}")
                                        <span class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endforeach
                            <div class="col-md-12 mb-3">
                                <label for="Details" class="form-label font-family-cairo fw-bold">التفاصيل</label>
                                <textarea wire:model="item.info" class="form-control font-family-cairo fw-bold" id="description" rows="2"
                                    @if (!$creating) disabled readonly @endif></textarea>
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
                        @if ($creating)
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="font-family-cairo fw-bold mb-0">وحدات الصنف</h6>
                                <button type="button" class="btn btn-primary btn-sm font-family-cairo fw-bold"
                                    wire:click="addUnitRow">
                                    <i class="las la-plus"></i> إضافة وحدة جديدة
                                </button>
                            </div>
                        @endif
                        <div class="table-responsive" style="overflow-x: auto;">
                            <table class="table table-striped mb-0" style="min-width: 1200px;">
                                <thead class="table-light text-center align-middle">

                                    <tr>
                                        <th class="font-family-cairo text-center fw-bold">الوحدة</th>
                                        <th class="font-family-cairo text-center fw-bold">معامل التحويل</th>
                                        <th class="font-family-cairo text-center fw-bold">التكلفة</th>
                                        @foreach ($prices as $price)
                                            <th class="font-family-cairo fw-bold">{{ $price->name }}</th>
                                        @endforeach
                                        <th class="font-family-cairo text-center fw-bold">باركود</th>
                                        <th class="font-family-cairo text-center fw-bold">XX</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($unitRows as $index => $unitRow)
                                        <tr>
                                            <td class="font-family-cairo fw-bold font-14 text-center">
                                                <select wire:model.live="unitRows.{{ $index }}.unit_id"
                                                    @if (!$creating) disabled readonly @endif
                                                    class="form-select font-family-cairo fw-bold font-14"
                                                    style="min-width: 100px; height: 50px;">
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
                                            <td class="text-center">
                                                <input type="number" onclick="this.select()"
                                                    @if (!$creating) disabled readonly @endif
                                                    wire:model="unitRows.{{ $index }}.u_val"
                                                    wire:keyup.debounce.300ms="updateUnitsCostAndPrices({{ $index }})"
                                                    class="form-control font-family-cairo fw-bold" min="1"
                                                    step="0.0001" style="min-width: 150px;">
                                                @error("unitRows.{$index}.u_val")
                                                    <span
                                                        class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <input type="number" onclick="this.select()"
                                                    @if (!$creating) disabled readonly @endif
                                                    wire:model="unitRows.{{ $index }}.cost"
                                                    wire:keyup.debounce.300ms="updateUnitsCost({{ $index }})"
                                                    class="form-control font-family-cairo fw-bold" step="0.0001"
                                                    style="min-width: 150px;">
                                                @error("unitRows.{$index}.cost")
                                                    <span
                                                        class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            @foreach ($prices as $price)
                                                <td class="text-center">
                                                    <input type="number" onclick="this.select()"
                                                        @if (!$creating) disabled readonly @endif
                                                        wire:model="unitRows.{{ $index }}.prices.{{ $price->id }}"
                                                        class="form-control font-family-cairo fw-bold" step="0.0001"
                                                        style="min-width: 150px;">
                                                    @error("unitRows.{$index}.prices.{$price->id}")
                                                        <span
                                                            class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                            @endforeach
                                            <td class="d-flex text-center flex-column gap-1 mt-4">
                                                <input type="text" onclick="this.select()"
                                                    @if (!$creating) disabled readonly @endif
                                                    wire:model.live="unitRows.{{ $index }}.barcodes.0"
                                                    class="form-control font-family-cairo fw-bold" maxlength="25"
                                                    style="min-width: 150px;">
                                                {{-- add button to add more barcodes --}}
                                                @if ($creating)
                                                    <button type="button"
                                                        class="btn btn-primary btn-sm font-family-cairo fw-bold"
                                                        wire:click="addAdditionalBarcode({{ $index }})">
                                                        <i class="las la-plus"></i> باركود إضافى
                                                    </button>
                                                @endif
                                                @if (!$creating)
                                                    <button type="button"
                                                        class="btn btn-primary btn-sm font-family-cairo fw-bold"
                                                        wire:click="showBarcodes({{ $index }})">
                                                        <i class="las la-plus"></i> عرض الباركود
                                                    </button>
                                                @endif
                                                @error("unitRows.{$index}.barcodes.{$index}")
                                                    <span
                                                        class="text-danger font-family-cairo fw-bold font-12">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td class="font-family-cairo fw-bold font-14 text-center">
                                                @if ($creating)
                                                    <button type="button"
                                                        class="btn btn-danger btn-icon-square-sm float-end"
                                                        wire:click="removeUnitRow({{ $index }})">
                                                        <i class="far fa-trash-alt"></i>
                                                    </button>
                                                @endif
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
                                                            @if ($creating)
                                                                <button type="button"
                                                                    class="btn btn-primary btn-sm font-family-cairo fw-bold"
                                                                    wire:click="addBarcodeField({{ $index }})">
                                                                    <i class="las la-plus"></i> إضافة باركود
                                                                </button>
                                                            @endif
                                                        </div>

                                                        @foreach ($unitRow['barcodes'] as $barcodeIndex => $barcode)
                                                            <div class="d-flex align-items-center mb-2"
                                                                wire:key="{{ $index }}-barcode-{{ $barcodeIndex }}">
                                                                <input type="text"
                                                                    @if (!$creating) disabled readonly @endif
                                                                    class="form-control font-family-cairo fw-bold"
                                                                    wire:model.live="unitRows.{{ $index }}.barcodes.{{ $barcodeIndex }}"
                                                                    id="unitRows.{{ $index }}.barcodes.{{ $barcodeIndex }}"
                                                                    placeholder="أدخل الباركود">
                                                                @if ($creating)
                                                                    <button type="button"
                                                                        class="btn btn-danger btn-sm ms-2"
                                                                        wire:click="removeBarcodeField({{ $index }}, {{ $barcodeIndex }})">
                                                                        <i class="far fa-trash-alt"></i>
                                                                    </button>
                                                                @endif
                                                            </div>
                                                            @error("unitRows.{{ $index }}.barcodes.{{ $barcodeIndex }}")
                                                                <span
                                                                    class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                                            @enderror
                                                        @endforeach
                                                    </div>
                                                    <div class="modal-footer">
                                                        @if ($creating)
                                                            <button type="button"
                                                                class="btn btn-secondary font-family-cairo fw-bold"
                                                                data-bs-dismiss="modal"
                                                                wire:click="cancelBarcodeUpdate({{ $index }})">إلغاء</button>
                                                            <button type="button"
                                                                class="btn btn-primary font-family-cairo fw-bold"
                                                                wire:click="saveBarcodes({{ $index }})">حفظ</button>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                </fieldset>

                <div class="container-fluid mt-3">
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        @if ($creating)
                            <button type="button" class="btn btn-lg btn-secondary font-family-cairo fw-bold"
                                onclick="window.location.href='{{ route('items.index') }}'">
                                عوده ( إلغاء )
                            </button>
                            <button type="submit" class="btn btn-lg btn-primary font-family-cairo fw-bold"
                                wire:loading.attr="disabled" wire:target="save">{{ 'حفظ' }}</button>
                        @else
                            <button type="button" class="btn btn-lg btn-secondary font-family-cairo fw-bold"
                                onclick="window.location.href='{{ route('items.index') }}'">
                                عوده
                            </button>
                            <button type="button" class="btn btn-lg btn-info font-family-cairo fw-bold"
                                wire:click="createNew">{{ 'جديد' }}</button>
                            <button type="button" class="btn btn-lg btn-warning font-family-cairo fw-bold"
                                wire:click="createNewFromCurrent">{{ 'جديد من الصنف الحالى' }}</button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('livewire:init', () => {
            window.addEventListener('open-modal', event => {
                let modal = new bootstrap.Modal(document.getElementById(event.detail[0]));
                modal.show();
            });

            window.addEventListener('close-modal', event => {
                let modal = bootstrap.Modal.getInstance(document.getElementById(event.detail[
                    0]));
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
                    if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target
                        .type !== 'submit' && e.target.type !== 'button') {
                        e.preventDefault();
                    }
                });
            });
        });


    });
</script>
