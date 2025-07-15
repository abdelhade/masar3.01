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
        'unitRows.*.barcodes.*.distinct' => 'الباركود مستخدم بالفعل.',
        'unitRows.*.barcodes.*.unique' => 'الباركود مستخدم بالفعل.',
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

        $this->validate();
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
        // dd($barcodesToCreate);
        // dd($this->unitRows,$pricesSync);
        try {
            Log::info('Starting item save process', ['item' => $this->item]);
            Log::info('Creating new item');
            $itemModel = Item::create($this->item);
            Log::info('New item created', ['item_id' => $itemModel->id]);

            // Process units
            Log::info('Processing units', ['unit_rows' => $this->unitRows]);
            $itemModel->units()->attach($unitsSync);
            Log::info('Units synced successfully');
            // Process barcodes
            $itemModel->barcodes()->createMany($barcodesToCreate);
            Log::info('Barcodes synced successfully');
            // Process prices
            foreach ($pricesSync as $index => $prices) {
                foreach ($prices as $price_id => $price) {
                    $itemModel->prices()->attach($price_id, ['unit_id' => $price['unit_id'], 'price' => $price['price']]);
                }
            }
            Log::info('Prices synced successfully');
            // Process notes
            $itemModel->notes()->attach(collect($this->item['notes'])->mapWithKeys(fn($noteDetailName, $noteId) => [$noteId => ['note_detail_name' => $noteDetailName]])->all());
            Log::info('Notes synced successfully');
            $itemModel->save();
            $itemModel->refresh();
            $this->resetForm();
            DB::commit();
            Log::info('Transaction committed successfully');
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

    public function addAdditionalBarcode($index)
    {
        if (empty($this->additionalBarcodes)) {
            $this->addBarcodeField();
        }
        // $this->editingBarcodeIndex = $index;
        // $this->additionalBarcodes = array_values($this->unitRows[$index]['barcodes'] ?? []);
        // remove the first barcode, as it's the main one
        // if (count($this->additionalBarcodes) > 0) {
        //     array_shift($this->additionalBarcodes);
        // }
        $this->dispatch('open-modal', 'add-barcode-modal');
    }

    public function addBarcodeField()
    {
        $this->additionalBarcodes[] = '';
    }

    public function removeBarcodeField($barcodeIndex)
    {
        unset($this->additionalBarcodes[$barcodeIndex]);
        $this->additionalBarcodes = array_values($this->additionalBarcodes);
    }

    public function saveBarcodes()
    {
        // if ($this->editingBarcodeIndex !== null) {
        //     $mainBarcode = $this->unitRows[$this->editingBarcodeIndex]['barcodes'][$this->editingBarcodeIndex] ?? null;
        //     $this->unitRows[$this->editingBarcodeIndex]['barcodes'] = [];
        //     if ($mainBarcode) {
        //         $this->unitRows[$this->editingBarcodeIndex]['barcodes'][$this->editingBarcodeIndex] = $mainBarcode;
        //     }
        //     foreach ($this->additionalBarcodes as $barcode) {
        //         if (!empty($barcode)) {
        //             $this->unitRows[$this->editingBarcodeIndex]['barcodes'][] = $barcode;
        //         }
        //     }
        // }
        $this->dispatch('close-modal', 'add-barcode-modal');
    }

    public function cancelBarcodeUpdate()
    {
        $this->reset(
            'additionalBarcodes',
            // , 'editingBarcodeIndex'
        );
        $this->dispatch('close-modal', 'add-barcode-modal');
    }

    public function updateUnitsCostAndPrices($index)
    {
        if ($index != 0 && isset($this->unitRows[$index]['u_val']) && $this->unitRows[$index]['u_val'] != null) {
            $this->unitRows[$index]['cost'] = $this->unitRows[$index]['u_val'] * $this->unitRows[0]['cost'];
            foreach ($this->prices as $price) {
                $this->unitRows[$index]['prices'][$price->id] = $this->unitRows[$index]['u_val'] * $this->unitRows[0]['prices'][$price->id];
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
                    $this->unitRows[$unitRowIndex]['cost'] = $unitRow['u_val'] * $this->unitRows[0]['cost'];
                }
            }
        }
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
            <form wire:submit.prevent="save" wire:loading.attr="disabled" wire:target="save">
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
                                    class="form-control font-family-cairo fw-bold frst" id="name"
                                    x-ref="nameInput">
                                @error('item.name')
                                    <span class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            @foreach ($notes as $note)
                                <div class="col-md-2 mb-3">
                                    <label for="type"
                                        class="form-label font-family-cairo fw-bold">{{ $note->name }}</label>
                                    <select wire:model="item.notes.{{ $note->id }}"
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
                            <h6 class="font-family-cairo fw-bold mb-0">وحدات الصنف</h6>
                            <button type="button" class="btn btn-primary btn-sm font-family-cairo fw-bold"
                                wire:click="addUnitRow">
                                <i class="las la-plus"></i> إضافة وحدة جديدة
                            </button>
                        </div>
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
                                            <td  class="font-family-cairo fw-bold font-14 text-center">
                                                <select wire:model.live="unitRows.{{ $index }}.unit_id"
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
                                                    wire:model="unitRows.{{ $index }}.cost"
                                                    wire:keyup.debounce.300ms="updateUnitsCost({{ $index }})"
                                                    class="form-control font-family-cairo fw-bold"
                                                    step="0.0001" style="min-width: 150px;">
                                                @error("unitRows.{$index}.cost")
                                                    <span
                                                        class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            @foreach ($prices as $price)
                                                <td class="text-center">
                                                    <input type="number" onclick="this.select()"
                                                        wire:model="unitRows.{{ $index }}.prices.{{ $price->id }}"
                                                        class="form-control font-family-cairo fw-bold"
                                                        step="0.0001" style="min-width: 150px;">
                                                    @error("unitRows.{$index}.prices.{$price->id}")
                                                        <span
                                                            class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                                    @enderror
                                                </td>
                                            @endforeach
                                            <td class="d-flex text-center flex-column gap-1 mt-4">
                                                <input type="text" onclick="this.select()"
                                                    wire:model="unitRows.{{ $index }}.barcodes.{{ $index }}"
                                                    class="form-control font-family-cairo fw-bold"
                                                    maxlength="25" style="min-width: 150px;">
                                                {{-- add button to add more barcodes --}}
                                                <button type="button"
                                                    class="btn btn-primary btn-sm font-family-cairo fw-bold"
                                                    wire:click="addAdditionalBarcode({{ $index }})">
                                                    <i class="las la-plus"></i> باركود إضافى
                                                </button>
                                                @error("unitRows.{$index}.barcodes.{$index}")
                                                    <span
                                                        class="text-danger font-family-cairo fw-bold font-12">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td  class="font-family-cairo fw-bold font-14 text-center">
                                                <button type="button" class="btn btn-danger btn-icon-square-sm float-end"
                                                    wire:click="removeUnitRow({{ $index }})">
                                                    <i class="far fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                </fieldset>

                <div class="mt-3">
                    <button type="button" class="btn btn-secondary font-family-cairo fw-bold"
                        onclick="window.location.href='{{ route('items.index') }}'">عوده / إلغاء</button>
                    <button type="submit"
                        class="btn btn-primary font-family-cairo fw-bold">{{ 'إنشاء' }}</button>
                </div>
            </form>
        </div>
    </div>
    <!-- Additional Barcode Modal -->
    <div wire:ignore.self class="modal fade" id="add-barcode-modal" tabindex="-1"
        aria-labelledby="addBarcodeModalLabel" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-family-cairo fw-bold" id="addBarcodeModalLabel">إضافة وتعديل الباركود
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        wire:click="cancelBarcodeUpdate" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-primary btn-sm font-family-cairo fw-bold"
                            wire:click="addBarcodeField">
                            <i class="las la-plus"></i> إضافة حقل
                        </button>
                    </div>

                    @foreach ($additionalBarcodes as $barcodeIndex => $barcode)
                        <div class="d-flex align-items-center mb-2" wire:key="barcode-{{ $barcodeIndex }}">
                            <input type="text" class="form-control font-family-cairo fw-bold"
                                wire:model="additionalBarcodes.{{ $barcodeIndex }}" placeholder="أدخل الباركود">

                            <button type="button" class="btn btn-danger btn-sm ms-2"
                                wire:click="removeBarcodeField({{ $barcodeIndex }})">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </div>
                        @error("additionalBarcodes.{$barcodeIndex}")
                            <span class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                        @enderror
                    @endforeach
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary font-family-cairo fw-bold"
                        data-bs-dismiss="modal" wire:click="cancelBarcodeUpdate">إلغاء</button>
                    <button type="button" class="btn btn-primary font-family-cairo fw-bold"
                        wire:click="saveBarcodes">حفظ</button>
                </div>
            </div>
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
