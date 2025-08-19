<?php

namespace App\Livewire\Reports;

use App\Models\OperHead;
use App\Models\OperationItems;
use App\Models\Item;
use App\Models\Price;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseInvoiceItemsPricing extends Component
{
    use WithPagination;

    public $operationId;
    public $operation;
    public $searchTerm = '';
    public $bulkIncrease = '';
    public $increaseType = 'fixed'; // fixed or percent
    public $purchasePriceType = 'last'; // last or average
    public $targetPriceTypes = []; // Array for multiple price type IDs
    public $selectedItems = [];
    public $showOnlyWithStock = false;

    // Statistics
    public $totalItems = 0;
    public $totalPurchaseValue = 0;
    public $totalSaleValue = 0;
    public $totalProfit = 0;
    public $avgProfitMargin = 0;

    protected $listeners = ['itemPriceUpdated' => 'updateItemPrice'];

    public function mount($operationId)
    {
        $this->operationId = $operationId;
        $this->operation = OperHead::with(['operationItems.item', 'type'])->findOrFail($operationId);
        $this->calculateStatistics();
    }

    public function getFilteredItems()
    {
        $query = OperationItems::with(['item', 'unit'])
            ->where('pro_id', $this->operationId)
            ->where('isdeleted', 0);

        if ($this->searchTerm) {
            $query->whereHas('item', function ($q) {
                $q->where('name', 'like', '%' . $this->searchTerm . '%')
                    ->orWhere('code', 'like', '%' . $this->searchTerm . '%');
            });
        }

        if ($this->showOnlyWithStock) {
            $query->where('current_stock_value', '>', 0);
        }

        return $query->paginate(20);
    }

    public function getPurchasePrice($itemId)
    {
        $operationItem = OperationItems::where('item_id', $itemId)
            ->where('isdeleted', 0)
            ->where('qty_in', '>', 0);

        if ($this->purchasePriceType === 'last') {
            return $operationItem->orderBy('created_at', 'desc')->first()->cost_price ?? 0;
        } else {
            // Calculate weighted average purchase price
            $items = $operationItem->get();
            $totalCost = $items->sum(function ($item) {
                return $item->cost_price * $item->qty_in;
            });
            $totalQuantity = $items->sum('qty_in');
            return $totalQuantity > 0 ? $totalCost / $totalQuantity : 0;
        }
    }

    public function updateItemPrice($itemId, $newIncrease)
    {
        $operationItem = OperationItems::find($itemId);
        if ($operationItem) {
            $item = Item::find($operationItem->item_id);
            if ($item) {
                $this->updateItemModelPrice($item, $operationItem->unit_id, $newIncrease);
            }

            $this->calculateStatistics();
        }
    }

    public function applyBulkIncrease()
    {
        if (empty($this->bulkIncrease) || $this->bulkIncrease <= 0) {
            session()->flash('error', 'يرجى إدخال قيمة صحيحة للزيادة');
            return;
        }

        if (empty($this->targetPriceTypes)) {
            session()->flash('error', 'يرجى اختيار نوع سعر واحد على الأقل');
            return;
        }

        $itemsToUpdate = $this->selectedItems ?: $this->getAllItemIds();

        foreach ($itemsToUpdate as $itemId) {
            $operationItem = OperationItems::find($itemId);
            if ($operationItem) {
                $basePrice = $this->getPurchasePrice($operationItem->item_id);
                $increase = $this->increaseType === 'fixed'
                    ? $this->bulkIncrease
                    : $basePrice * ($this->bulkIncrease / 100);

                $item = Item::find($operationItem->item_id);
                if ($item) {
                    $this->updateItemModelPrice($item, $operationItem->unit_id, $increase);
                }
            }
        }

        $this->calculateStatistics();
        session()->flash('message', 'تم تطبيق الزيادة الجماعية على ' . count($itemsToUpdate) . ' صنف');
        $this->bulkIncrease = '';
        $this->selectedItems = [];
        $this->targetPriceTypes = [];
    }

    public function updateItemModelPrice($item, $unitId, $increase)
    {
        // Get selected price type IDs
        $priceIds = $this->targetPriceTypes;

        // Loop through all prices for the item and unit
        $existingPrices = $item->prices()->wherePivot('unit_id', $unitId)->get();

        foreach ($priceIds as $priceId) {
            $price = $existingPrices->where('id', $priceId)->first();

            if ($price) {
                // Add increase to existing price
                $currentPrice = $price->pivot->price ?? 0;
                $newPrice = $currentPrice + $increase;

                $item->prices()->updateExistingPivot($price->id, [
                    'price' => $newPrice,
                    'discount' => 0, // Adjust as needed
                    'tax_rate' => 0, // Adjust as needed
                ]);
            } else {
                // Create new price with the increase as the price
                $item->prices()->attach($priceId, [
                    'unit_id' => $unitId,
                    'price' => $increase, // If no existing price, use increase as base price
                    'discount' => 0,
                    'tax_rate' => 0,
                ]);
            }
        }
    }

    public function calculateStatistics()
    {
        $items = OperationItems::where('pro_id', $this->operationId)
            ->where('isdeleted', 0)
            ->with('item')
            ->get();

        $this->totalItems = $items->count();
        $this->totalPurchaseValue = $items->sum(function ($item) {
            return $item->cost_price * $item->fat_quantity;
        });

        $this->totalSaleValue = $items->sum(function ($item) {
            // Use the first price type for statistics
            $price = $item->item->prices()
                ->wherePivot('unit_id', $item->unit_id)
                ->first()
                ?->pivot
                ?->price ?? $item->fat_price;

            return $price * $item->fat_quantity;
        });

        $this->totalProfit = $this->totalSaleValue - $this->totalPurchaseValue;

        if ($this->totalPurchaseValue > 0) {
            $this->avgProfitMargin = ($this->totalProfit / $this->totalPurchaseValue) * 100;
        } else {
            $this->avgProfitMargin = 0;
        }
    }

    public function saveAllPrices()
    {
        $this->operation->update([
            'fat_total' => $this->totalSaleValue,
            'fat_net' => $this->totalSaleValue - $this->operation->fat_disc,
            'profit' => $this->totalProfit,
        ]);

        session()->flash('message', 'تم حفظ جميع الأسعار بنجاح!');
        $this->dispatch('allPricesSaved');
    }

    public function getAllItemIds()
    {
        return OperationItems::where('pro_id', $this->operationId)
            ->where('isdeleted', 0)
            ->pluck('id')
            ->toArray();
    }

    public function toggleItemSelection($itemId)
    {
        if (in_array($itemId, $this->selectedItems)) {
            $this->selectedItems = array_diff($this->selectedItems, [$itemId]);
        } else {
            $this->selectedItems[] = $itemId;
        }
    }

    public function selectAllItems()
    {
        $this->selectedItems = $this->getAllItemIds();
    }

    public function deselectAllItems()
    {
        $this->selectedItems = [];
    }

    public function updatedSearchTerm()
    {
        $this->resetPage();
    }

    public function updatedShowOnlyWithStock()
    {
        $this->resetPage();
    }

    public function render()
    {
        $items = $this->getFilteredItems();
        $this->calculateStatistics();
        $priceTypes = Price::all(); // Fetch all price types

        return view('livewire.reports.purchase-invoice-items-pricing', [
            'items' => $items,
            'operation' => $this->operation,
            'priceTypes' => $priceTypes,
        ]);
    }
}
