<?php

namespace Modules\Manufacturing\Livewire;

use Livewire\Component;
use App\Models\Item;
use Modules\Manufacturing\Services\ManufacturingCostService;
use App\Models\OperHead;
use App\Models\OperationItems;
use Illuminate\Support\Facades\DB;

class ManufacturingCostModal extends Component
{
    public $isOpen = false;
    public $items = []; // Items passed from the parent view (id, name)
    public $selectedItemId = null;
    public $manufacturingQuantity = 1;
    public $costData = null;
    public $isLoading = false;

    protected $listeners = ['openManufacturingModal'];

    public $activeTab = 'single';
    public $totalCostData = [];
    public $totalRawMaterials = [];
    public $grandTotal = 0;

    public function openManufacturingModal($items)
    {
        $this->items = $items;
        $this->isOpen = true;
        $this->reset(['selectedItemId', 'costData', 'activeTab', 'totalCostData', 'totalRawMaterials', 'grandTotal']);
        $this->manufacturingQuantity = 1;
        
        // Calculate totals immediately when opening
        $this->calculateAllCosts();
    }

    public function close()
    {
        $this->isOpen = false;
        $this->reset(['items', 'selectedItemId', 'costData', 'activeTab', 'totalCostData', 'totalRawMaterials', 'grandTotal']);
    }

    public function updatedSelectedItemId($value)
    {
        if ($value) {
            // Find the item in the list to get its quantity
            $selectedItem = collect($this->items)->firstWhere('id', $value);
            $this->manufacturingQuantity = $selectedItem['qty'] ?? 1;
            
            $this->calculateCost();
        } else {
            $this->costData = null;
        }
    }

    public function updatedManufacturingQuantity()
    {
        if ($this->selectedItemId) {
            $this->calculateCost();
        }
    }

    public function setTab($tab)
    {
        $this->activeTab = $tab;
    }

    public function calculateCost()
    {
        if (!$this->selectedItemId) return;

        $this->isLoading = true;
        
        try {
            $service = new ManufacturingCostService();
            $this->costData = $service->calculateItemCost(
                $this->selectedItemId, 
                (float) $this->manufacturingQuantity
            );
        } catch (\Exception $e) {
            // Handle error
            $this->costData = null;
        }

        $this->isLoading = false;
    }

    public function calculateAllCosts()
    {
        $this->isLoading = true;
        $service = new ManufacturingCostService();
        
        $this->totalCostData = [];
        $this->totalRawMaterials = [];
        $this->grandTotal = 0;

        foreach ($this->items as $item) {
            $qty = $item['qty'] ?? 1;
            $costData = $service->calculateItemCost($item['id'], $qty);
            
            if ($costData) {
                $this->totalCostData[] = $costData;
                $this->grandTotal += $costData['total_cost'];

                // Aggregate raw materials
                $this->aggregateRawMaterials($costData['components']);
            }
        }
        
        $this->isLoading = false;
    }

    private function aggregateRawMaterials($components)
    {
        foreach ($components as $component) {
            if (!empty($component['components'])) {
                // Recursive aggregation
                $this->aggregateRawMaterials($component['components']);
            } else {
                // It's a raw material (leaf node)
                $id = $component['item_id'];
                if (!isset($this->totalRawMaterials[$id])) {
                    $this->totalRawMaterials[$id] = [
                        'name' => $component['name'],
                        'quantity' => 0,
                        'total_cost' => 0,
                        'unit_cost' => $component['unit_cost']
                    ];
                }
                $this->totalRawMaterials[$id]['quantity'] += $component['quantity_needed'];
                $this->totalRawMaterials[$id]['total_cost'] += $component['total_cost'];
            }
        }
    }

    public function confirmCreatePurchaseOrder()
    {
        if (empty($this->totalRawMaterials)) {
            return;
        }
        
        $this->dispatch('confirm-create-po');
    }

    public function proceedCreatePurchaseOrder()
    {
        if (empty($this->totalRawMaterials)) {
            return;
        }

        // Prepare items data for the session
        $itemsData = [];
        foreach ($this->totalRawMaterials as $itemId => $material) {
            $item = \App\Models\Item::with('units')->find($itemId);
            if (!$item) continue;

            $unitId = $item->units->first()->id ?? null;
            
            $itemsData[] = [
                'item_id' => $itemId,
                'unit_id' => $unitId,
                'name' => $material['name'],
                'quantity' => $material['quantity'],
                'price' => $material['unit_cost'] > 0 ? $material['unit_cost'] : 0,
                'sub_value' => $material['total_cost'],
                'available_units' => $item->units->map(fn($unit) => (object)[
                    'id' => $unit->id,
                    'name' => $unit->name
                ]),
                'notes' => '',
            ];
        }

        // Prepare invoice data
        $invoiceData = [
            'invoice_data' => [
                'notes' => __('Generated from Manufacturing Requisition'),
                'invoice_date' => now()->format('Y-m-d'),
                'accural_date' => now()->format('Y-m-d'),
                'branch_id' => auth()->user()->branch_id ?? 1,
            ],
            'items_data' => $itemsData,
            'subtotal' => $this->grandTotal,
            'total_after_additional' => $this->grandTotal,
        ];

        // Store in session
        session(['convert_invoice_data' => $invoiceData]);

        // Redirect to Create Invoice page (Type 15: Purchase Order)
        $type = 15;
        $hash = md5($type);
        return redirect()->to(url('/invoices/create?type=' . $type . '&q=' . $hash));
    }

    public function render()
    {
        return view('manufacturing::livewire.manufacturing-cost-modal');
    }
}
