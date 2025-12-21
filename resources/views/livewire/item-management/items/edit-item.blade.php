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
use App\Enums\ItemType;
use Livewire\WithFileUploads;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

new class extends Component {
    use WithFileUploads;

    public Item $itemModel;

    public $units;
    public $prices;
    public $notes;
    public $hasVaribals = false;
    public $creating = false;

    public $item = [
        'type' => null,
        'name' => '',
        'code' => '',
        'info' => '',
        'notes' => [],
    ];

    public $unitRows = [];

    // Image properties
    public $itemThumbnail = null;
    public $itemImages = [];
    public $existingThumbnail = null;
    public $existingImages = [];
    public $imagesToDelete = [];

    // Modal properties
    public $showModal = false;
    public $modalType = ''; // 'unit' or 'note'
    public $modalTitle = '';
    public $modalData = [
        'name' => '',
        'note_id' => null,
    ];

    public function mount(Item $itemModel)
    {
        $this->itemModel = $itemModel->load('units', 'prices', 'notes', 'barcodes');
        $this->units = Unit::all();
        $this->prices = Price::all();
        $this->notes = Note::with('noteDetails')->get();

        // Load existing images
        $this->existingThumbnail = $this->itemModel->getFirstMedia('item-thumbnail');
        $this->existingImages = $this->itemModel->getMedia('item-images');

        $this->item = [
            'name' => $this->itemModel->name,
            'type' => $this->itemModel->type->value,
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
            'item.type' => 'required|in:' . implode(',', array_column(ItemType::cases(), 'value')),
            'item.notes.*' => 'nullable',
            'unitRows.*.barcodes.*' => ['nullable', 'string', 'distinct', 'max:25', Rule::unique('barcodes', 'barcode')->where(fn($query) => $query->where('item_id', '!=', $this->itemModel->id))],
            'unitRows.*.cost' => 'required|numeric|min:0',
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
            'unitRows.*.u_val' => 'required|numeric|min:0.0001',
            'unitRows.*.unit_id' => 'required|exists:units,id|distinct',
            'unitRows.*.prices.*' => 'required|numeric|min:0',
            // Image validation
            'itemThumbnail' => 'nullable|image|max:2048',
            'itemImages.*' => 'nullable|image|max:2048',
        ];
    }

    protected $messages = [
        'item.name.required' => 'اسم الصنف مطلوب.',
        'item.name.min' => 'اسم الصنف يجب أن يكون أطول من 3 أحرف.',
        'item.name.unique' => 'اسم الصنف مستخدم بالفعل.',
        'item.type.required' => 'نوع الصنف مطلوب.',
        'item.type.in' => 'نوع الصنف غير موجود.',
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

    public function removeNewImage($index)
    {
        if (isset($this->itemImages[$index])) {
            unset($this->itemImages[$index]);
            $this->itemImages = array_values($this->itemImages);
        }
    }

    public function deleteExistingImage($mediaId, $type = 'gallery')
    {
        try {
            $media = Media::find($mediaId);
            if ($media && $media->model_id === $this->itemModel->id) {
                $media->delete();
                
                // Refresh the existing images
                if ($type === 'thumbnail') {
                    $this->existingThumbnail = null;
                } else {
                    $this->existingImages = $this->itemModel->fresh()->getMedia('item-images');
                }
                
                session()->flash('success', __('items.image_deleted_successfully'));
            }
        } catch (\Exception $e) {
            Log::error('Error deleting image: ' . $e->getMessage());
            session()->flash('error', __('common.error_occurred'));
        }
    }

    public function update()
    {
        $this->validate();

        try {
            DB::beginTransaction();

            $this->updateItem();
            $this->syncUnits();
            $this->syncBarcodes();
            $this->syncPrices();
            $this->syncNotes();
            $this->updateItemImages();

            DB::commit();
            $this->handleSuccess();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->handleError($e);
        }
    }

    private function updateItemImages()
    {
        // Save new thumbnail image
        if ($this->itemThumbnail) {
            // Delete old thumbnail if exists
            if ($this->existingThumbnail) {
                $this->existingThumbnail->delete();
            }
            
            $this->itemModel->addMedia($this->itemThumbnail->getRealPath())
                ->usingFileName($this->itemThumbnail->getClientOriginalName())
                ->toMediaCollection('item-thumbnail');
        }

        // Save new additional images
        if (!empty($this->itemImages) && is_array($this->itemImages)) {
            foreach ($this->itemImages as $image) {
                if ($image && method_exists($image, 'getRealPath')) {
                    $this->itemModel->addMedia($image->getRealPath())
                        ->usingFileName($image->getClientOriginalName())
                        ->toMediaCollection('item-images');
                }
            }
        }
    }

    private function updateItem()
    {
        // Exclude average_cost from update - it should only be modified through purchase invoices
        $itemData = $this->item;
        unset($itemData['average_cost']);
        
        $this->itemModel->update($itemData);
        Log::info('Item updated', ['item_id' => $this->itemModel->id]);
    }

    private function syncUnits()
    {
        $unitsSync = [];
        foreach ($this->unitRows as $unitRow) {
            if (empty($unitRow['unit_id'])) {
                continue;
            }

            $unitsSync[$unitRow['unit_id']] = [
                'u_val' => $unitRow['u_val'],
                'cost' => $unitRow['cost'],
            ];
        }
        $this->itemModel->units()->sync($unitsSync);
        Log::info('Units synced successfully');
    }

    private function syncBarcodes()
    {
        $barcodesToCreate = [];
        foreach ($this->unitRows as $unitRowIndex => $unitRow) {
            if (empty($unitRow['unit_id'])) {
                continue;
            }

            $hasValidBarcode = false;
            if (!empty($unitRow['barcodes'])) {
                foreach ($unitRow['barcodes'] as $barcode) {
                    if (!empty(trim($barcode))) {
                        $barcodesToCreate[] = ['unit_id' => $unitRow['unit_id'], 'barcode' => trim($barcode)];
                        $hasValidBarcode = true;
                    }
                }
            }

            if (!$hasValidBarcode) {
                $barcodesToCreate[] = ['unit_id' => $unitRow['unit_id'], 'barcode' => $this->item['code'] . ($unitRowIndex + 1)];
            }
        }

        $this->itemModel->barcodes()->delete();
        if (!empty($barcodesToCreate)) {
            $this->itemModel->barcodes()->createMany($barcodesToCreate);
        }
        Log::info('Barcodes synced successfully');
    }

    private function syncPrices()
    {
        $pricesToSync = [];
        foreach ($this->unitRows as $unitRow) {
            if (empty($unitRow['unit_id']) || empty($unitRow['prices'])) {
                continue;
            }

            foreach ($unitRow['prices'] as $price_id => $price_value) {
                $pricesToSync[] = ['price_id' => $price_id, 'unit_id' => $unitRow['unit_id'], 'price' => $price_value];
            }
        }

        $this->itemModel->prices()->detach();
        foreach ($pricesToSync as $priceData) {
            $this->itemModel->prices()->attach($priceData['price_id'], ['unit_id' => $priceData['unit_id'], 'price' => $priceData['price']]);
        }
        Log::info('Prices synced successfully');
    }

    private function syncNotes()
    {
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
    }

    private function handleSuccess()
    {
        Log::info('Transaction committed successfully');
        session()->flash('success', 'تم تحديث الصنف بنجاح!');
        $this->dispatch('$refresh');
        return redirect()->route('items.index')->with('success', 'تم تحديث الصنف بنجاح!');
    }

    private function handleError(\Exception $e)
    {
        Log::error('Error updating item', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'item' => $this->item,
            'unit_rows' => $this->unitRows,
        ]);
        session()->flash('error', 'حدث خطأ أثناء تحديث الصنف. يرجى المحاولة مرة أخرى.');
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
            $this->modalTitle = 'إضافة جديد' . ' ' . '[ ' . $note->name . ' ]';
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
            'note_id' => null,
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
        @include('livewire.item-management.items.partials.alerts')
        <div class="">
            <form wire:submit.prevent="update" wire:loading.attr="disabled" wire:target="update">
                <!-- Basic Item Information -->
                <fieldset class="shadow-sm">
                    <div class="col-md-12 p-3">
                        <div class="row">
                            <div class="col-md-1 mb-3">
                                <label for="code" class="form-label font-hold fw-bold">رقم
                                    الصنف</label>
                                <input type="text" wire:model="item.code"
                                    class="form-control font-hold fw-bold" id="code">
                                @error('item.code')
                                    <span class="text-danger font-hold fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            {{-- item type --}}
                            <div class="col-md-1 mb-3">
                                <label for="type" class="form-label font-hold fw-bold">نوع الصنف</label>
                                <select wire:model="item.type" class="form-select font-hold fw-bold"
                                    id="type">
                                    <option class="font-hold fw-bold" value="">إختر</option>
                                    @foreach (ItemType::cases() as $type)
                                        <option class="font-hold fw-bold" value="{{ $type->value }}">
                                            {{ $type->label() }}</option>
                                    @endforeach
                                </select>
                                @error('item.type')
                                    <span class="text-danger font-hold fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-3">
                                <label for="name" class="form-label font-hold fw-bold">اسم
                                    الصنف</label>
                                <input type="text" wire:model="item.name"
                                    class="form-control font-hold fw-bold" id="name" x-ref="nameInput">
                                @error('item.name')
                                    <span class="text-danger font-hold fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            @foreach ($notes as $note)
                                <div class="col-md-2 mb-3">
                                    <label for="type"
                                        class="form-label font-hold fw-bold">{{ $note->name }}</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-outline-success font-hold fw-bold"
                                            wire:click="openModal('note_detail', {{ $note->id }})"
                                            title="إضافة جديد">
                                            <i class="las la-plus"></i>
                                        </button>
                                        <select wire:model="item.notes.{{ $note->id }}"
                                            class="form-select font-hold fw-bold" id="note-{{ $note->id }}">
                                            <option class="font-hold fw-bold" value="">إختر</option>
                                            @foreach ($note->noteDetails as $noteDetail)
                                                <option class="font-hold fw-bold"
                                                    value="{{ $noteDetail->name }}">
                                                    {{ $noteDetail->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @error("item.notes.{$note->id}")
                                        <span class="text-danger font-hold fw-bold">{{ $message }}</span>
                                    @enderror
                                </div>
                            @endforeach



                            <div class="col-md-12 mb-3">
                                <label for="Details" class="form-label font-hold fw-bold">التفاصيل</label>
                                <textarea wire:model="item.info" class="form-control font-hold fw-bold" id="description" rows="2"></textarea>
                                @error('item.details')
                                    <span class="text-danger font-hold fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Image Upload Section -->
                <fieldset class="shadow-sm mt-3">
                    <legend class="p-3 mb-0">
                        <h6 class="font-hold fw-bold mb-0">{{ __('items.item_images') }}</h6>
                    </legend>
                    <div class="col-md-12 p-3">
                        @include('livewire.item-management.items.partials.image-upload')
                    </div>
                </fieldset>

                @include('livewire.item-management.items.partials.units-repeater')

                <div class="mt-3">
                    <button type="button" class="btn btn-secondary font-hold fw-bold"
                        onclick="window.history.back()">عوده / إلغاء</button>
                    <button type="submit"
                        class="btn btn-main font-hold fw-bold">{{ 'تحديث' }}</button>
                </div>
            </form>
        </div>
    </div>

    @include('livewire.item-management.items.partials.universal-modal')
    @include('livewire.item-management.items.partials.scripts')
    @include('livewire.item-management.items.partials.styles')

</div>
