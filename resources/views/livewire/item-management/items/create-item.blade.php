<?php

use Livewire\Volt\Component;
use App\Models\Unit;
use App\Models\Price;
use App\Models\Item;
use Modules\Accounts\Models\AccHead;
use App\Models\Note;
use App\Models\NoteDetails;
use App\Models\Varibal;
use App\Models\VaribalValue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Validate;
use App\Enums\ItemType;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    //
    public $creating = true;
    
    // Image properties
    public $itemThumbnail = null;
    public $itemImages = [];
    public $existingThumbnail = null;
    public $existingImages = [];
    public $units;
    public $prices;
    public $notes;
    public $additionalBarcodes = [];
    public $hasVaribals = false;
    public $varibals = [];
    public $varibalValues = [];
    public $selectedVaribalCombinations = [];
    public $newCombinationText = '';
    public $editingCombinationKey = null;
    public $editingCombinationText = '';
    public $selectedRowVaribal = null;
    public $selectedColVaribal = null;
    public $combinationUnitRows = [];
    public $activeCombination = null;
    public $showBarcodeModal = false;
    public $currentBarcodeUnitIndex = null;
    public $currentBarcodeCombination = null;
    public $modalBarcodeData = [];

    // Modal properties
    public $showModal = false;
    public $modalType = ''; // 'unit' or 'note'
    public $modalTitle = '';
    public $modalData = [
        'name' => '',
        'note_id' => null,
    ];

    // Basic item information
    public $item = [
        'type' => null,
        'name' => '',
        'code' => '',
        'info' => '',
        'notes' => [],
    ];
    // For managing item units
    public $unitRows = [];

    // Active tab for tabs navigation
    public $activeTab = 'basic';

    public function mount()
    {
        $this->units = Unit::all();
        $this->prices = Price::all();
        $this->notes = Note::with('noteDetails')->get();
        $this->varibals = Varibal::with('varibalValues')->get();
        $this->addUnitRow();
        $this->item['code'] = Item::max('code') + 1 ?? 1;
        $this->item['type'] = 1;
    }

    protected function rules()
    {
        return [
            'item.name' => 'required|min:3|unique:items,name',
            'item.type' => 'required|in:' . implode(',', array_column(ItemType::cases(), 'value')),
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
                        $fail(__('items.base_unit_conversion_factor_must_be_one'));
                    }
                },
            ],
            'unitRows.*.u_val' => 'required|numeric|min:0.0001|distinct',
            'unitRows.*.unit_id' => 'required|exists:units,id|distinct',
            'unitRows.*.prices.*' => 'required|numeric|min:0',
            // Image validation
            'itemThumbnail' => 'nullable|image|max:2048',
            'itemImages.*' => 'nullable|image|max:2048',
            // 'unitRows.*.barcodes.*' => 'required|string|distinct|max:25|unique:barcodes,barcode',
        ];
    }

    protected function messages(): array
    {
        return [
            'item.name.required' => __('items.item_name_required'),
            'item.name.min' => __('items.item_name_min_length'),
            'item.name.unique' => __('items.item_name_unique'),
            'item.type.required' => __('items.item_type_required'),
            'item.type.in' => __('items.item_type_invalid'),
            'item.*.notes.*.exists' => __('items.note_detail_not_exists'),
            'unitRows.*.unit_id.exists' => __('items.unit_not_exists'),
            'unitRows.*.unit_id.required' => __('items.unit_required'),
            'unitRows.*.unit_id.distinct' => __('items.unit_already_used'),
            'unitRows.*.barcodes.*.string' => __('items.barcode_must_be_string'),
            'unitRows.*.barcodes.*.distinct' => __('items.barcode_duplicate_in_list'),
            'unitRows.*.barcodes.*.unique' => __('items.barcode_already_exists'),
            'unitRows.*.barcodes.*.max' => __('items.barcode_max_length'),
            'unitRows.*.barcodes.*.required' => __('items.barcode_required'),
            'unitRows.*.cost.required' => __('items.cost_required'),
            'unitRows.*.cost.numeric' => __('items.cost_must_be_numeric'),
            'unitRows.*.cost.min' => __('items.cost_min_value'),
            'unitRows.*.cost.distinct' => __('items.cost_already_used'),
            'unitRows.*.u_val.required' => __('items.conversion_factor_required'),
            'unitRows.*.u_val.numeric' => __('items.conversion_factor_must_be_numeric'),
            'unitRows.*.u_val.min' => __('items.conversion_factor_min_value'),
            'unitRows.*.u_val.distinct' => __('items.conversion_factor_already_used'),
            'unitRows.*.prices.*.required' => __('items.price_required'),
            'unitRows.*.prices.*.numeric' => __('items.price_must_be_numeric'),
            'unitRows.*.prices.*.min' => __('items.price_min_value'),
        ];
    }

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

    public function removeNewImage($index)
    {
        if (isset($this->itemImages[$index])) {
            unset($this->itemImages[$index]);
            $this->itemImages = array_values($this->itemImages);
        }
    }

    public function resetForm()
    {
        $this->item = [
            'type' => null,
            'name' => '',
            'code' => Item::max('code') + 1 ?? 1,
            'info' => '',
            'notes' => [],
        ];
        $this->unitRows = [];
        $this->itemThumbnail = null;
        $this->itemImages = [];
        $this->activeTab = 'basic';
        $this->addUnitRow();
    }

    public function save()
    {
        $this->prepareBarcodes();
        $this->validate();

        try {
            DB::beginTransaction();
            
            if ($this->hasVaribals && count($this->selectedVaribalCombinations) > 0) {
                // Save items with variations
                $this->saveItemsWithVariations();
            } else {
                // Save single item
            $itemModel = Item::create($this->item);
            $this->attachUnits($itemModel);
            $this->setAverageCostFromBaseUnit($itemModel);
            $this->createBarcodes($itemModel);
            $this->attachPrices($itemModel);
            $this->attachNotes($itemModel);
            $this->saveItemImages($itemModel);
            }
            
            DB::commit();
            $this->handleSuccess();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->handleError($e);
            Log::error('Error saving item', [
                'error' => $e->getMessage(),
                'item' => $this->item,
                'unitRows' => $this->unitRows,
                'selectedVaribalCombinations' => $this->selectedVaribalCombinations,
                'combinationUnitRows' => $this->combinationUnitRows,
            ]);
        }
    }

    private function saveItemImages($itemModel)
    {
        // Save thumbnail image
        if ($this->itemThumbnail) {
            $itemModel->addMedia($this->itemThumbnail->getRealPath())
                ->usingFileName($this->itemThumbnail->getClientOriginalName())
                ->toMediaCollection('item-thumbnail');
        }

        // Save additional images
        if (!empty($this->itemImages) && is_array($this->itemImages)) {
            foreach ($this->itemImages as $image) {
                if ($image && method_exists($image, 'getRealPath')) {
                    $itemModel->addMedia($image->getRealPath())
                        ->usingFileName($image->getClientOriginalName())
                        ->toMediaCollection('item-images');
                }
            }
        }
    }

    private function saveItemsWithVariations()
    {
        $baseCode = $this->item['code'];
        $combinationIndex = 1;
        
        foreach ($this->selectedVaribalCombinations as $combinationKey => $combination) {
            // Create item name with variation
            $itemName = $this->getCombinationDisplayName($combinationKey);
            
            // Create unique code for each combination
            $combinationSuffix = str_pad($combinationIndex, 2, '0', STR_PAD_LEFT);
            $uniqueCode = $baseCode . $combinationSuffix;
            
            // Create item data
            $itemData = $this->item;
            $itemData['name'] = $itemName;
            $itemData['code'] = $uniqueCode;
            
            // Create the item
            $itemModel = Item::create($itemData);
            
            // Attach units, barcodes, prices, and notes for this combination
            if (isset($this->combinationUnitRows[$combinationKey])) {
                $this->attachCombinationUnits($itemModel, $combinationKey);
                $this->setAverageCostFromBaseUnit($itemModel);
                $this->createCombinationBarcodes($itemModel, $combinationKey);
                $this->attachCombinationPrices($itemModel, $combinationKey);
            }
            
            $this->attachNotes($itemModel);
            $combinationIndex++;
        }
    }

    private function attachCombinationUnits($itemModel, $combinationKey)
    {
        $unitsSync = [];
        foreach ($this->combinationUnitRows[$combinationKey] as $unitRow) {
            $unitsSync[$unitRow['unit_id']] = [
                'u_val' => $unitRow['u_val'],
                'cost' => $unitRow['cost'],
            ];
        }
        $itemModel->units()->attach($unitsSync);
    }

    private function createCombinationBarcodes($itemModel, $combinationKey)
    {
        $barcodesToCreate = [];
        $baseCode = $itemModel->code;
        
        foreach ($this->combinationUnitRows[$combinationKey] as $unitRowIndex => $unitRow) {
            if (!empty($unitRow['barcodes'])) {
                foreach ($unitRow['barcodes'] as $barcodeIndex => $barcode) {
                    if (!empty($barcode)) {
                        $barcodesToCreate[] = ['unit_id' => $unitRow['unit_id'], 'barcode' => $barcode];
                    }
                }
            } else {
                // Generate unique barcode for each unit in each combination
                $uniqueBarcode = $baseCode . ($unitRowIndex + 1);
                $barcodesToCreate[] = ['unit_id' => $unitRow['unit_id'], 'barcode' => $uniqueBarcode];
            }
        }
        $itemModel->barcodes()->createMany($barcodesToCreate);
    }

    private function getCombinationSuffix($combinationKey)
    {
        // Generate a unique suffix for each combination
        $combinationIndex = array_search($combinationKey, array_keys($this->selectedVaribalCombinations));
        if ($combinationIndex === false) {
            $combinationIndex = 0;
        }
        return str_pad($combinationIndex + 1, 2, '0', STR_PAD_LEFT);
    }

    private function attachCombinationPrices($itemModel, $combinationKey)
    {
        foreach ($this->combinationUnitRows[$combinationKey] as $unitRow) {
            $prices = collect($unitRow['prices'])->mapWithKeys(fn($price, $id) => [$id => ['unit_id' => $unitRow['unit_id'], 'price' => $price]])->all();
            foreach ($prices as $price_id => $price) {
                $itemModel->prices()->attach($price_id, ['unit_id' => $price['unit_id'], 'price' => $price['price']]);
            }
        }
    }

    private function prepareBarcodes()
    {
        foreach ($this->unitRows as $unitRowIndex => &$unitRow) {
            if (empty($unitRow['barcodes'])) {
                $this->unitRows[$unitRowIndex]['barcodes'][] = $this->item['code'] . ($unitRowIndex + 1);
            }
        }
        unset($unitRow);
        $this->unitRows = array_values($this->unitRows);
    }

    private function attachUnits($itemModel)
    {
        $unitsSync = [];
        foreach ($this->unitRows as $unitRow) {
            $unitsSync[$unitRow['unit_id']] = [
                'u_val' => $unitRow['u_val'],
                'cost' => $unitRow['cost'],
            ];
        }
        $itemModel->units()->attach($unitsSync);
    }

    /**
     * Set average_cost from base unit cost (u_val = 1) - only on creation
     */
    private function setAverageCostFromBaseUnit($itemModel): void
    {
        // Reload item with units to get the pivot data
        $itemModel->refresh();
        $itemModel->load('units');
        
        // Find the base unit (u_val = 1)
        $baseUnit = $itemModel->units->first(function ($unit) {
            return isset($unit->pivot) && $unit->pivot->u_val == 1;
        });
        
        if ($baseUnit && isset($baseUnit->pivot->cost)) {
            // Set average_cost to the cost of the base unit
            $itemModel->update(['average_cost' => $baseUnit->pivot->cost]);
        }
    }

    private function createBarcodes($itemModel)
    {
        $barcodesToCreate = [];
        foreach ($this->unitRows as $unitRowIndex => $unitRow) {
            if (!empty($unitRow['barcodes'])) {
                foreach ($unitRow['barcodes'] as $barcode) {
                    if (!empty($barcode)) {
                        $barcodesToCreate[] = ['unit_id' => $unitRow['unit_id'], 'barcode' => $barcode];
                    }
                }
            } else {
                $barcodesToCreate[] = ['unit_id' => $unitRow['unit_id'], 'barcode' => $this->item['code'] . $unitRowIndex + 1];
            }
        }
        $itemModel->barcodes()->createMany($barcodesToCreate);
    }

    private function attachPrices($itemModel)
    {
        foreach ($this->unitRows as $unitRow) {
            $prices = collect($unitRow['prices'])->mapWithKeys(fn($price, $id) => [$id => ['unit_id' => $unitRow['unit_id'], 'price' => $price]])->all();
            foreach ($prices as $price_id => $price) {
                $itemModel->prices()->attach($price_id, ['unit_id' => $price['unit_id'], 'price' => $price['price']]);
            }
        }
    }

    private function attachNotes($itemModel)
    {
        $notesData = collect($this->item['notes'] ?? [])
            ->mapWithKeys(fn($noteDetailName, $noteId) => [$noteId => ['note_detail_name' => $noteDetailName]])
            ->all();
        $itemModel->notes()->attach($notesData);
        // Notes synced successfully
    }

    private function handleSuccess()
    {
        // Transaction committed successfully
        $this->creating = false;
        session()->flash('success', __('items.item_created_successfully'));
    }

    private function handleError(\Exception $e)
    {
        // Error saving item
        Log::error('Error saving item', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
            'item' => $this->item,
            'unit_rows' => $this->unitRows,
        ]);
        session()->flash('error', __('items.error_saving_item'));
    }

    public function edit($itemId)
    {
        // 
    }

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

    /**
     * Validate base unit conversion factor (must be 1)
     * Calculations are now handled client-side via JavaScript
     */
    public function updateUnitsCostAndPrices($index)
    {
        // Only validate base unit (index 0) - calculations moved to client-side
        if ($index == 0 && isset($this->unitRows[$index]['u_val'])) {
            $this->validate([
                'unitRows.0.u_val' => [
                    'required',
                    'numeric',
                    'min:1',
                    'distinct',
                    function ($attribute, $value, $fail) {
                        if ($value != 1) {
                            $fail(__('items.base_unit_conversion_factor_must_be_one'));
                        }
                    },
                ],
            ]);
        }
    }

    /**
     * Validation only - calculations moved to client-side
     */
    public function updateUnitsCost($index)
    {
        // Calculations are now handled client-side via JavaScript
        // This function kept for backward compatibility but does nothing
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
        $this->activeTab = 'basic';
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
        $this->activeTab = 'basic';
        $this->dispatch('auto-focus', 'item-name');
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    // Modal functions
    public function openModal($type, $noteId = null)
    {
        $this->modalType = $type;
        $this->resetModalData();
        
        if ($type === 'unit') {
            $this->modalTitle = __('items.create_new_unit');
        } elseif ($type === 'note_detail' && $noteId) {
            $note = Note::find($noteId);
            $this->modalTitle = __('items.add_new_note_detail', ['note' => $note->name]);
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
            'modalData.name.required' => __('common.name') . ' ' . __('validation.required'),
            'modalData.name.min' => __('common.name') . ' ' . __('validation.min.string', ['min' => 1]),
            'modalData.name.max' => __('common.name') . ' ' . __('validation.max.string', ['max' => 255]),
            'modalData.name.unique' => __('common.name') . ' ' . __('validation.unique'),
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
                
                session()->flash('success', __('items.unit_created_successfully'));
            } elseif ($this->modalType === 'note_detail' && $this->modalData['note_id']) {
                // Create new note detail
                $noteDetail = NoteDetails::create([
                    'note_id' => $this->modalData['note_id'],
                    'name' => $this->modalData['name'],
                ]);
                
                // Refresh notes list
                $this->notes = Note::with('noteDetails')->get();
                
                session()->flash('success', __('items.note_detail_added_successfully', ['name' => $this->modalData['name']]));
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
            session()->flash('error', __('items.error_saving_modal_data'));
        }
    }

    // Varibal management methods
    public function toggleVaribalCombination($rowVaribalId, $rowValueId, $colVaribalId, $colValueId)
    {
        $key = $rowVaribalId . '_' . $rowValueId . '_' . $colVaribalId . '_' . $colValueId;
        
        if (isset($this->selectedVaribalCombinations[$key])) {
            unset($this->selectedVaribalCombinations[$key]);
        } else {
            $this->selectedVaribalCombinations[$key] = [
                'row_varibal_id' => $rowVaribalId,
                'row_value_id' => $rowValueId,
                'col_varibal_id' => $colVaribalId,
                'col_value_id' => $colValueId,
            ];
        }
    }

    public function isVaribalCombinationSelected($rowVaribalId, $rowValueId, $colVaribalId, $colValueId)
    {
        $key = $rowVaribalId . '_' . $rowValueId . '_' . $colVaribalId . '_' . $colValueId;
        return isset($this->selectedVaribalCombinations[$key]);
    }

    public function getVaribalCombinations()
    {
        $combinations = [];
        
        if (empty($this->selectedVaribalCombinations)) {
            return $combinations;
        }

        // Convert selected combinations to readable format
        foreach ($this->selectedVaribalCombinations as $key => $combination) {
            if (isset($combination['row_varibal_id'])) {
                // Grid-based combination
                $rowVaribal = $this->varibals->find($combination['row_varibal_id']);
                $colVaribal = $this->varibals->find($combination['col_varibal_id']);
                $rowValue = $rowVaribal ? $rowVaribal->varibalValues->find($combination['row_value_id']) : null;
                $colValue = $colVaribal ? $colVaribal->varibalValues->find($combination['col_value_id']) : null;
                
                if ($rowValue && $colValue) {
                    $combinations[] = [
                        'key' => $key,
                        'display' => $rowValue->value . ' - ' . $colValue->value,
                        'type' => 'grid',
                    ];
                }
            } else {
                // Text-based combination
                $combinations[] = [
                    'key' => $key,
                    'display' => $combination,
                    'type' => 'text',
                ];
            }
        }

        return $combinations;
    }

    public function addTextCombination()
    {
        if (!empty(trim($this->newCombinationText))) {
            $key = 'text_' . time() . '_' . rand(1000, 9999);
            $this->selectedVaribalCombinations[$key] = trim($this->newCombinationText);
            $this->newCombinationText = '';
            session()->flash('success', __('items.combination_added_successfully'));
        }
    }

    public function removeCombination($key)
    {
        if (isset($this->selectedVaribalCombinations[$key])) {
            unset($this->selectedVaribalCombinations[$key]);
            session()->flash('success', __('items.combination_removed_successfully'));
        }
    }

    public function startEditingCombination($key)
    {
        if (isset($this->selectedVaribalCombinations[$key]) && is_string($this->selectedVaribalCombinations[$key])) {
            $this->editingCombinationKey = $key;
            $this->editingCombinationText = $this->selectedVaribalCombinations[$key];
        }
    }

    public function saveEditingCombination()
    {
        if ($this->editingCombinationKey && !empty(trim($this->editingCombinationText))) {
            $this->selectedVaribalCombinations[$this->editingCombinationKey] = trim($this->editingCombinationText);
            $this->cancelEditingCombination();
            session()->flash('success', __('items.combination_updated_successfully'));
        }
    }

    public function cancelEditingCombination()
    {
        $this->editingCombinationKey = null;
        $this->editingCombinationText = '';
    }

    public function updatedSelectedRowVaribal()
    {
        // Clear grid combinations when row varibal changes
        $this->clearGridCombinations();
    }

    public function updatedSelectedColVaribal()
    {
        // Clear grid combinations when column varibal changes
        $this->clearGridCombinations();
    }

    public function updatedHasVaribals()
    {
        if (!$this->hasVaribals) {
            // Reset all combinations when varibals are disabled
            $this->selectedVaribalCombinations = [];
            $this->selectedRowVaribal = null;
            $this->selectedColVaribal = null;
            $this->newCombinationText = '';
            $this->editingCombinationKey = null;
            $this->editingCombinationText = '';
            $this->combinationUnitRows = [];
            $this->activeCombination = null;
        }
    }

    private function clearGridCombinations()
    {
        // Remove only grid-based combinations, keep text combinations
        $this->selectedVaribalCombinations = array_filter($this->selectedVaribalCombinations, function ($combination) {
            return is_string($combination); // Keep only text combinations
        });
    }

    public function getRowVaribalValues()
    {
        if (!$this->selectedRowVaribal) {
            return collect();
        }
        $varibal = $this->varibals->find($this->selectedRowVaribal);
        return $varibal ? $varibal->varibalValues : collect();
    }

    public function getColVaribalValues()
    {
        if (!$this->selectedColVaribal) {
            return collect();
        }
        $varibal = $this->varibals->find($this->selectedColVaribal);
        return $varibal ? $varibal->varibalValues : collect();
    }

    public function selectCombination($combinationKey)
    {
        $this->activeCombination = $combinationKey;
        
        // Initialize unit rows for this combination if not exists
        if (!isset($this->combinationUnitRows[$combinationKey])) {
            $this->combinationUnitRows[$combinationKey] = [];
            $this->addCombinationUnitRow($combinationKey);
        }
        
        // Ensure each combination has completely independent data
        $this->ensureCombinationDataIndependence($combinationKey);
    }

    private function ensureCombinationDataIndependence($combinationKey)
    {
        // Make sure each combination has its own independent data structure
        if (!isset($this->combinationUnitRows[$combinationKey])) {
            $this->combinationUnitRows[$combinationKey] = [];
        }
        
        // Initialize empty arrays for each combination if not exists
        foreach ($this->combinationUnitRows[$combinationKey] as $index => $unitRow) {
            if (!isset($this->combinationUnitRows[$combinationKey][$index]['barcodes'])) {
                $this->combinationUnitRows[$combinationKey][$index]['barcodes'] = [];
            }
            if (!isset($this->combinationUnitRows[$combinationKey][$index]['prices'])) {
                $this->combinationUnitRows[$combinationKey][$index]['prices'] = [];
            }
        }
    }

    public function updatedActiveCombination()
    {
        // Clear any shared data when switching combinations
        // Each combination should have its own independent data
    }

    public function addCombinationUnitRow($combinationKey)
    {
        if (!isset($this->combinationUnitRows[$combinationKey])) {
            $this->combinationUnitRows[$combinationKey] = [];
        }
        
        // Create completely independent data for this combination
        $newUnitRow = [
            'unit_id' => $this->units->first()->id,
            'u_val' => 1,
            'cost' => 0,
            'barcodes' => [],
            'prices' => [],
        ];
        
        // Initialize prices array for this combination only
        foreach ($this->prices as $price) {
            $newUnitRow['prices'][$price->id] = 0;
        }
        
        // Add initial barcode for the new unit
        $unitIndex = count($this->combinationUnitRows[$combinationKey]);
        $combinationSuffix = $this->getCombinationSuffix($combinationKey);
        $uniqueBarcode = $this->item['code'] . $combinationSuffix . ($unitIndex + 1);
        $newUnitRow['barcodes'][] = $uniqueBarcode;
        
        // Ensure barcodes array is properly initialized
        if (!isset($newUnitRow['barcodes'])) {
            $newUnitRow['barcodes'] = [];
        }
        
        $this->combinationUnitRows[$combinationKey][] = $newUnitRow;
    }

    /**
     * Validate base unit conversion factor for combinations (must be 1)
     * Calculations are now handled client-side via JavaScript
     */
    public function updateCombinationUnitsCostAndPrices($combinationKey, $index)
    {
        // Only validate base unit (index 0) - calculations moved to client-side
        if ($index == 0 && isset($this->combinationUnitRows[$combinationKey][$index]['u_val'])) {
            $this->validate([
                'combinationUnitRows.' . $combinationKey . '.0.u_val' => [
                    'required',
                    'numeric',
                    'min:1',
                    'distinct',
                    function ($attribute, $value, $fail) {
                        if ($value != 1) {
                            $fail(__('items.base_unit_conversion_factor_must_be_one'));
                        }
                    },
                ],
            ]);
        }
    }

    /**
     * Validation only - calculations moved to client-side
     */
    public function updateCombinationUnitsCost($combinationKey, $index)
    {
        // Calculations are now handled client-side via JavaScript
        // This function kept for backward compatibility but does nothing
    }

    /**
     * Validation only - calculations moved to client-side
     */
    public function updateCombinationPrices($combinationKey, $index, $priceId)
    {
        // Calculations are now handled client-side via JavaScript
        // This function kept for backward compatibility but does nothing
    }

    public function addCombinationBarcode($combinationKey, $unitRowIndex)
    {
        if (!isset($this->combinationUnitRows[$combinationKey][$unitRowIndex])) {
            return;
        }

        $barcodeIndex = count($this->combinationUnitRows[$combinationKey][$unitRowIndex]['barcodes']);
        $uniqueBarcode = $this->item['code'] . ($unitRowIndex + 1) . ($barcodeIndex + 1);
        $this->combinationUnitRows[$combinationKey][$unitRowIndex]['barcodes'][] = $uniqueBarcode;
    }

    public function removeCombinationBarcode($combinationKey, $unitRowIndex, $barcodeIndex)
    {
        if (isset($this->combinationUnitRows[$combinationKey][$unitRowIndex]['barcodes'][$barcodeIndex])) {
            unset($this->combinationUnitRows[$combinationKey][$unitRowIndex]['barcodes'][$barcodeIndex]);
            $this->combinationUnitRows[$combinationKey][$unitRowIndex]['barcodes'] = array_values($this->combinationUnitRows[$combinationKey][$unitRowIndex]['barcodes']);
        }
    }

    public function openBarcodeModal($combinationKey, $unitRowIndex)
    {
        $this->currentBarcodeCombination = $combinationKey;
        $this->currentBarcodeUnitIndex = $unitRowIndex;
        $existingBarcodes = $this->combinationUnitRows[$combinationKey][$unitRowIndex]['barcodes'] ?? [];
        $this->modalBarcodeData = array_values(array_slice($existingBarcodes, 1));
        $this->showBarcodeModal = true;
        $this->dispatch('open-modal', 'barcodeModal');

        // Opening barcode modal
    }

    public function closeBarcodeModal()
    {
        // closeBarcodeModal invoked
        
        $this->showBarcodeModal = false;
        $this->dispatch('close-modal', 'barcodeModal');
        $this->currentBarcodeCombination = null;
        $this->currentBarcodeUnitIndex = null;
        $this->modalBarcodeData = [];
    }

    public function addModalBarcode()
    {
        // addModalBarcode invoked
        
        if (!is_array($this->modalBarcodeData)) {
            $this->modalBarcodeData = [];
        }
        // Reassign to trigger Livewire re-render reliably
        $this->modalBarcodeData = array_values(array_merge($this->modalBarcodeData, ['']));
        
        // Added modal barcode
        
        // Optional: focus last input
        // $this->dispatch('auto-focus', 'modalBarcodeInput.' . (count($this->modalBarcodeData) - 1));
    }

    public function removeModalBarcode($index)
    {
        // removeModalBarcode invoked
        
        if (isset($this->modalBarcodeData[$index])) {
            unset($this->modalBarcodeData[$index]);
            $this->modalBarcodeData = array_values($this->modalBarcodeData);
            
            // Removed modal barcode

            // Component will re-render via state change
        }
    }

    public function saveAdditionalBarcodes()
    {
        // saveAdditionalBarcodes invoked
        
        if ($this->currentBarcodeCombination && $this->currentBarcodeUnitIndex !== null) {
            $unit =& $this->combinationUnitRows[$this->currentBarcodeCombination][$this->currentBarcodeUnitIndex];
            $existing = $unit['barcodes'] ?? [];
            $base = $existing[0] ?? null;
            $additional = [];
            foreach ($this->modalBarcodeData as $barcode) {
                $barcode = trim((string) $barcode);
                if ($barcode !== '') {
                    $additional[] = $barcode;
                }
            }
            $additional = array_values(array_unique($additional));
            $unit['barcodes'] = array_values(array_filter(array_merge([$base], $additional), fn($b) => $b !== null && $b !== ''));
        }
        
        $this->closeBarcodeModal();
    }

    public function removeCombinationUnitRow($combinationKey, $index)
    {
        if (isset($this->combinationUnitRows[$combinationKey][$index])) {
            unset($this->combinationUnitRows[$combinationKey][$index]);
            $this->combinationUnitRows[$combinationKey] = array_values($this->combinationUnitRows[$combinationKey]);
        }
    }

    public function getActiveCombinationUnitRows()
    {
        if ($this->activeCombination && isset($this->combinationUnitRows[$this->activeCombination])) {
            return $this->combinationUnitRows[$this->activeCombination];
        }
        return [];
    }

    public function getCombinationDisplayName($combinationKey)
    {
        if (isset($this->selectedVaribalCombinations[$combinationKey])) {
            $combination = $this->selectedVaribalCombinations[$combinationKey];
            if (is_string($combination)) {
                return $this->item['name'] . ' - ' . $combination;
            } elseif (isset($combination['row_varibal_id'])) {
                $rowVaribal = $this->varibals->find($combination['row_varibal_id']);
                $colVaribal = $this->varibals->find($combination['col_varibal_id']);
                $rowValue = $rowVaribal ? $rowVaribal->varibalValues->find($combination['row_value_id']) : null;
                $colValue = $colVaribal ? $colVaribal->varibalValues->find($combination['col_value_id']) : null;
                
                if ($rowValue && $colValue) {
                    return $this->item['name'] . ' - ' . $rowValue->value . ' - ' . $colValue->value;
                }
            }
        }
        return $this->item['name'];
    }
}; ?>

<div>
    {{-- form --}}
    <div class="card">
        <div class="text-center py-3" style="background: linear-gradient(135deg, #34d3a3 0%, #1aa1c4 100%); color: white; border-radius: 0.5rem 0.5rem 0 0;">
            <h5 class="card-title font-hold fw-bold font-20 text-white mb-0">
                {{ __('items.add_new_item') }}
            </h5>
        </div>
        <div class="card-body">
            @include('livewire.item-management.items.partials.alerts')
            <form wire:submit.prevent="save" wire:loading.attr="disabled" wire:target="save"
                wire:loading.class="opacity-50">
                
                <!-- Basic Information Section -->
                <fieldset class="shadow-sm mb-2" style="border: 2px solid #80e6cb; border-radius: 0.5rem;">
                    <div class="col-md-12 p-2">
                        <div class="row">
                            <div class="col-md-1 mb-2">
                                <label for="code" class="form-label font-hold fw-bold">{{ __('items.item_code') }}</label>
                                <input type="text" wire:model.live="item.code"
                                    class="form-control font-hold fw-bold" id="code"
                                    value="{{ $item['code'] }}" readonly disabled>
                                @error('item.code')
                                    <span class="text-danger font-hold fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-1 mb-2">
                                <label for="type" class="form-label font-hold fw-bold">{{ __('items.item_type') }}</label>
                                <select wire:model="item.type" class="form-select font-hold fw-bold"
                                    id="type">
                                    <option class="font-hold fw-bold" value="">{{ __('common.select') }}</option>
                                    @foreach (ItemType::cases() as $type)
                                        <option class="font-hold fw-bold" value="{{ $type->value }}">
                                            {{ $type->label() }}</option>
                                    @endforeach
                                </select>
                                @error('item.type')
                                    <span class="text-danger font-hold fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            <div class="col-md-3 mb-2">
                                <label for="name" class="form-label font-hold fw-bold">{{ __('items.item_name') }}</label>
                                <input type="text" wire:model="item.name"
                                    class="form-control font-hold fw-bold frst" id="item-name" x-ref="nameInput"
                                    @if (!$creating) disabled readonly @endif>
                                @error('item.name')
                                    <span class="text-danger font-hold fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            @foreach ($notes as $note)
                                <div class="col-md-2 mb-2">
                                    <label for="type"
                                        class="form-label font-hold fw-bold">{{ $note->name }}</label>
                                    <div class="input-group">
                                        <button type="button" class="btn btn-outline-success font-hold fw-bold"
                                            wire:click="openModal('note_detail', {{ $note->id }})"
                                            @if (!$creating) disabled @endif title="{{ __('items.add_new') }}">
                                            <i class="las la-plus"></i>
                                        </button>
                                        <select wire:model="item.notes.{{ $note->id }}"
                                            @if (!$creating) disabled readonly @endif
                                            class="form-select font-hold fw-bold"
                                            id="note-{{ $note->id }}">
                                            <option class="font-hold fw-bold" value="">{{ __('common.select') }}</option>
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
                            <div class="col-md-12 mb-2">
                                <label for="Details" class="form-label font-hold fw-bold">{{ __('items.item_description') }}</label>
                                <textarea wire:model="item.info" class="form-control font-hold fw-bold" id="description" rows="2"
                                    @if (!$creating) disabled readonly @endif></textarea>
                                @error('item.details')
                                    <span class="text-danger font-hold fw-bold">{{ $message }}</span>
                                @enderror
                            </div>
                            {{-- check box for decision if item will have varibals --}}
                            <div class="col-md-1 mb-2">
                                <input type="checkbox" wire:model.live="hasVaribals" class="form-check-input"
                                    id="hasVaribals">
                                <label for="hasVaribals" class="form-label font-hold fw-bold">{{ __('items.has_variations') }}</label>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- Units & Prices Section -->
                <fieldset class="shadow-sm mb-2" style="border: 2px solid #80e6cb; border-radius: 0.5rem;">
                    <div class="col-md-12 p-2">
                        @include('livewire.item-management.items.partials.units-repeater')
                    </div>
                </fieldset>

                <!-- Variations Section -->
                <fieldset class="shadow-sm mb-2" style="border: 2px solid #80e6cb; border-radius: 0.5rem;">
                    <div class="col-md-12 p-2">
                        @include('livewire.item-management.items.partials.varibals-grid')
                        @include('livewire.item-management.items.partials.combination-units')
                    </div>
                </fieldset>

                <!-- Images Section -->
                <fieldset class="shadow-sm mb-2" style="border: 2px solid #80e6cb; border-radius: 0.5rem;">
                    <div class="col-md-12 p-2">
                        @include('livewire.item-management.items.partials.image-upload')
                    </div>
                </fieldset>

                <div class="container-fluid mt-2">
                    <div class="d-flex justify-content-center gap-2 flex-wrap">
                        @if ($creating)
                            <button type="button" class="btn btn-lg btn-outline-secondary font-hold fw-bold"
                                onclick="window.location.href='{{ route('items.index') }}'">
                                {{ __('common.back') }} ( {{ __('common.cancel') }} )
                            </button>
                            <button type="submit" class="btn btn-lg btn-main font-hold fw-bold"
                                wire:loading.attr="disabled" wire:target="save">{{ __('common.save') }}</button>
                        @else
                            <button type="button" class="btn btn-lg btn-outline-secondary font-hold fw-bold"
                                onclick="window.location.href='{{ route('items.index') }}'">
                                {{ __('common.back') }}
                            </button>
                            <button type="button" class="btn btn-lg btn-main font-hold fw-bold"
                                wire:click="createNew">{{ __('common.new') }}</button>
                            <button type="button" class="btn btn-lg btn-main font-hold fw-bold"
                                wire:click="createNewFromCurrent">{{ __('items.new_from_current_item') }}</button>
                        @endif
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Universal Modal for creating units and notes --}}
    @include('livewire.item-management.items.partials.universal-modal')

    {{-- Barcode Modal inside root to keep single root element --}}
    @include('livewire.item-management.items.partials.barcode-modal')


    @include('livewire.item-management.items.partials.scripts')
    @include('livewire.item-management.items.partials.styles')

                        </div>

