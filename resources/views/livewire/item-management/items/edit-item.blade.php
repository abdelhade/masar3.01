<?php

use Livewire\Volt\Component;
use App\Models\Unit;
use App\Models\Price;
use App\Models\Item;
use App\Models\Note;
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

            $barcodeValue = $this->itemModel->barcodes()->where('unit_id', $unit->id)->value('barcode');

            $this->unitRows[] = [
                'unit_id' => $unit->id,
                'u_val' => $unit->pivot->u_val,
                'cost' => $unit->pivot->cost,
                'barcodes' => $barcodeValue ? [$rowIndex => $barcodeValue] : [],
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
            'barcodes' => [],
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

                if (!empty($unitRow['barcodes'])) {
                    foreach ($unitRow['barcodes'] as $barcodeIndex => $barcode) {
                        $barcodesToCreate[$barcodeIndex] = ['unit_id' => $unitRow['unit_id'], 'barcode' => $barcode];
                    }
                } else {
                    $barcodesToCreate[$unitRowIndex] = ['unit_id' => $unitRow['unit_id'], 'barcode' => $this->item['code'] . $unitRowIndex + 1];
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
                                            <td>
                                                <input type="text" onclick="this.select()"
                                                    wire:model="unitRows.{{ $index }}.barcodes.{{ $index }}"
                                                    class="form-control font-family-cairo fw-bold"
                                                    maxlength="25" style="min-width: 150px;">
                                                @error("unitRows.{$index}.barcodes.{$index}")
                                                    <span
                                                        class="text-danger font-family-cairo fw-bold">{{ $message }}</span>
                                                @enderror
                                            </td>
                                            <td>
                                                <button type="button"  class="btn btn-danger btn-icon-square-sm float-end"
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
                        onclick="window.history.back()">عوده / إلغاء</button>
                    <button type="submit"
                        class="btn btn-primary font-family-cairo fw-bold">{{ 'تحديث' }}</button>
                </div>
            </form>
        </div>
    </div>
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

        // منع زر الإدخال (Enter) من حفظ النموذج
        document.querySelectorAll('form').forEach(function(form) {
            form.addEventListener('keydown', function(e) {
                // إذا كان الزر Enter وتم التركيز على input وليس textarea أو زر
                if (e.key === 'Enter' && e.target.tagName !== 'TEXTAREA' && e.target.type !== 'submit' && e.target.type !== 'button') {
                    e.preventDefault();
                }
            });
        });
        });
    });
</script>
{{-- finshed --}}